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
     * Redirect to first section of survey
     */
    public function takeSurvey(Request $request, Response $response, $args)
    {
        $surveyId = $args['id'];
        $survey = $this->surveyModel->getById($surveyId);

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

        // Render multi-step survey form
        return $this->render($response, 'respondent/survey-multistep', [
            'survey' => $survey,
            'sections' => $sections,
            'showPasskeyForm' => $showPasskeyForm,
            'error' => $showPasskeyForm && (isset($postData['passkey']) || isset($queryParams['key'])) 
                ? 'Invalid passkey' : null
        ]);
    }

    /**
     * POST /surveys/{id}/submit
     * Process survey submission (Alpine.js multi-step form)
     */
    public function submitSurvey(Request $request, Response $response, $args)
    {
        $surveyId = $args['id'];
        $data = $request->getParsedBody();
        $files = $request->getUploadedFiles();

        $survey = $this->surveyModel->getById($surveyId);
        if (!$survey) {
            return $this->renderNotFound($response);
        }

        // Extract respondent info
        $respondentData = [
            'survey_id' => $surveyId,
            'first_name' => $data['first_name'] ?? '',
            'last_name' => $data['last_name'] ?? '',
            'middle_name' => $data['middle_name'] ?? null,
            'email' => $data['email'] ?? '',
            'phone' => $data['phone'] ?? null,
            'age' => !empty($data['age']) ? (int)$data['age'] : null,
            'sex' => $data['sex'] ?? null,
            'submitted_at' => date('Y-m-d H:i:s')
        ];

        $respondentId = $this->respondentModel->create($respondentData);

        if ($respondentId) {
            // Save responses
            if (isset($data['responses'])) {
                foreach ($data['responses'] as $questionId => $answer) {
                    $responseData = [
                        'respondent_id' => $respondentId,
                        'question_id' => $questionId,
                        'answer_text' => is_array($answer) ? implode(', ', $answer) : $answer,
                        'created_at' => date('Y-m-d H:i:s')
                    ];
                    $this->responseModel->create($responseData);
                }
            }

            // Handle file uploads
            if (!empty($files['files'])) {
                $this->handleFileUploads($respondentId, $surveyId, $files['files']);
            }

            // Redirect to thank you page
            return $response
                ->withHeader('Location', "/surveys/{$surveyId}/thank-you")
                ->withStatus(302);
        }

        // Error - redirect back to survey
        return $response
            ->withHeader('Location', "/surveys/{$surveyId}/take")
            ->withStatus(302);
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

    private function renderNotFound(Response $response)
    {
        $response->getBody()->write('Survey not found');
        return $response->withStatus(404);
    }
}

