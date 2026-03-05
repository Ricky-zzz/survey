<?php

namespace App\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use App\Models\Survey;
use App\Models\Respondent;
use App\Models\Response as ResponseModel;
use App\Models\File;
use App\Services\FileUploader;

class RespondentController
{
    private $surveyModel;
    private $respondentModel;
    private $responseModel;
    private $fileModel;
    private $fileUploader;

    public function __construct(
        Survey $surveyModel,
        Respondent $respondentModel,
        ResponseModel $responseModel,
        File $fileModel,
        FileUploader $fileUploader
    ) {
        $this->surveyModel = $surveyModel;
        $this->respondentModel = $respondentModel;
        $this->responseModel = $responseModel;
        $this->fileModel = $fileModel;
        $this->fileUploader = $fileUploader;
    }

    /**
     * GET /surveys/{id}/take
     * Display survey form for respondents
     */
    public function takeSurvey(Request $request, Response $response, $args)
    {
        $surveyId = $args['id'];
        $survey = $this->surveyModel->getWithSections($surveyId);

        if (!$survey) {
            return $this->renderNotFound($response);
        }

        // Check if survey is private and validate passkey
        $showPasskeyForm = false;
        if (!$survey['is_public']) {
            // Check passkey from URL params first, then POST data
            $queryParams = $request->getQueryParams();
            $postData = $request->getParsedBody() ?? [];
            $passkey = $queryParams['key'] ?? $postData['passkey'] ?? null;
            
            if (!$this->surveyModel->verifyPasskey($surveyId, $passkey)) {
                $showPasskeyForm = true;
            }
        }

        // Get sections with questions
        $sections = $survey['sections'] ?? [];

        return $this->render($response, 'respondent/survey', [
            'survey' => $survey,
            'sections' => $sections,
            'showPasskeyForm' => $showPasskeyForm,
            'error' => $showPasskeyForm && (isset($postData['passkey']) || isset($queryParams['key'])) 
                ? 'Invalid passkey' : null
        ]);
    }

    /**
     * POST /surveys/{id}/submit
     * Submit survey responses
     */
    public function submitSurvey(Request $request, Response $response, $args)
    {
        $surveyId = $args['id'];
        $data = $request->getParsedBody();
        $files = $request->getUploadedFiles();

        $survey = $this->surveyModel->getById($surveyId);
        if (!$survey) {
            return $this->jsonResponse($response, [
                'success' => false,
                'message' => 'Survey not found'
            ], 404);
        }

        try {
            // Create respondent record
            $respondentId = $this->respondentModel->create($surveyId);

            // Store responses (from respondent data and answers)
            $responses = $data['responses'] ?? [];
            
            if (!empty($responses)) {
                $this->responseModel->createMultiple($respondentId, $responses);
            }

            // Handle file uploads
            if (!empty($files)) {
                $this->handleFileUploads($respondentId, $surveyId, $files);
            }

            // Mark respondent as submitted
            $this->respondentModel->markSubmitted($respondentId);

            return $this->jsonResponse($response, [
                'success' => true,
                'message' => 'Survey submitted successfully',
                'respondentId' => $respondentId
            ]);
        } catch (\Exception $e) {
            return $this->jsonResponse($response, [
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * GET /surveys/{id}/thank-you
     * Thank you page after submission
     */
    public function thankYou(Request $request, Response $response, $args)
    {
        $surveyId = $args['id'];
        $survey = $this->surveyModel->getById($surveyId);

        if (!$survey) {
            return $this->renderNotFound($response);
        }

        return $this->render($response, 'respondent/thank-you', [
            'survey' => $survey
        ]);
    }

    /**
     * Handle file uploads for file_upload questions
     */
    private function handleFileUploads($respondentId, $surveyId, $uploadedFiles)
    {
        foreach ($uploadedFiles as $questionId => $files) {
            // Ensure files is an array
            if (!is_array($files)) {
                $files = [$files];
            }

            foreach ($files as $file) {
                if ($file->getError() === UPLOAD_ERR_OK) {
                    try {
                        $filePath = $this->fileUploader->upload($file, $surveyId, $respondentId);
                        
                        // Store file metadata in database
                        $this->fileModel->create([
                            'respondent_id' => $respondentId,
                            'question_id' => $questionId,
                            'file_path' => $filePath,
                            'original_filename' => $file->getClientFilename(),
                            'file_size' => $file->getSize(),
                            'file_type' => 'pdf'
                        ]);
                    } catch (\Exception $e) {
                        // Log error but don't fail entire submission
                        error_log($e->getMessage());
                    }
                }
            }
        }
    }

    /**
     * Format section data for Alpine.js form
     */
    private function formatSectionForForm($section)
    {
        return [
            'id' => $section['id'],
            'title' => $section['title'],
            'description' => $section['description'],
            'is_respondent_info' => $section['is_respondent_info'],
            'questions' => array_map(fn($q) => $this->formatQuestionForForm($q), $section['questions'] ?? [])
        ];
    }

    /**
     * Format question data for Alpine.js form
     */
    private function formatQuestionForForm($question)
    {
        $formatted = [
            'id' => $question['id'],
            'text' => $question['question_text'],
            'type' => $question['type'],
            'required' => $question['required']
        ];

        // Add options for scale and multiple_choice
        if (in_array($question['type'], ['scale', 'multiple_choice']) && isset($question['options'])) {
            $formatted['options'] = array_map(fn($opt) => [
                'id' => $opt['id'],
                'text' => $opt['option_text'],
                'value' => $opt['value']
            ], $question['options']);
        }

        return $formatted;
    }

    // Helper methods
    private function render(Response $response, $template, $data = [])
    {
        ob_start();
        extract($data);
        include __DIR__ . "/../Views/{$template}.php";
        $html = ob_get_clean();

        $response->getBody()->write($html);
        return $response;
    }

    private function renderWithError(Response $response, $template, $data = [])
    {
        ob_start();
        extract($data);
        include __DIR__ . "/../Views/{$template}.php";
        $html = ob_get_clean();

        $response->getBody()->write($html);
        return $response->withStatus(403);
    }

    private function renderNotFound(Response $response)
    {
        $response->getBody()->write('Survey not found');
        return $response->withStatus(404);
    }

    private function jsonResponse(Response $response, $data, $statusCode = 200)
    {
        $response->getBody()->write(json_encode($data));
        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus($statusCode);
    }
}
