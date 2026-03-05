<?php

namespace App\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use App\Models\Survey;
use App\Models\Section;
use App\Models\Admin;

class SurveyController
{
    private $surveyModel;
    private $sectionModel;
    private $adminModel;
    private $config;

    public function __construct(Survey $surveyModel, Section $sectionModel, Admin $adminModel, $config)
    {
        $this->surveyModel = $surveyModel;
        $this->sectionModel = $sectionModel;
        $this->adminModel = $adminModel;
        $this->config = $config;
    }

    /**
     * GET /admin/surveys
     * List all surveys
     */
    public function index(Request $request, Response $response)
    {
        $surveys = $this->surveyModel->getAll();
        
        return $this->render($response, 'admin/dashboard', [
            'surveys' => $surveys
        ]);
    }

    /**
     * GET /admin/surveys/create
     * Show survey creation form
     */
    public function createForm(Request $request, Response $response)
    {
        return $this->render($response, 'admin/survey-form', [
            'sections' => []
        ]);
    }

    /**
     * POST /admin/surveys
     * Store new survey
     */
    public function store(Request $request, Response $response)
    {
        $data = $request->getParsedBody();
        
        // Validate required fields
        if (!isset($data['name']) || empty($data['name'])) {
            return $this->renderWithError($response, 'admin/survey-form', 'Survey name is required');
        }

        try {
            // Get current admin ID (from session - you'll need to implement authentication)
            $adminId = $_SESSION['admin_id'] ?? 1;

            $surveyData = [
                'name' => $data['name'],
                'description' => $data['description'] ?? '',
                'is_public' => isset($data['is_public']) ? (bool)$data['is_public'] : true,
                'passkey' => $data['is_public'] ? null : ($data['passkey'] ?? null),
                'created_by' => $adminId
            ];

            $surveyId = $this->surveyModel->create($surveyData);

            // Create default respondent info section
            $this->sectionModel->create([
                'survey_id' => $surveyId,
                'title' => 'Your Information',
                'description' => 'Please provide your details',
                'is_respondent_info' => true,
                'order_sequence' => 0
            ]);

            return $response
                ->withHeader('Location', "/admin/surveys/{$surveyId}/edit")
                ->withStatus(302);
        } catch (\Exception $e) {
            return $this->renderWithError($response, 'admin/survey-form', $e->getMessage());
        }
    }

    /**
     * GET /admin/surveys/{id}
     * Show survey details with sections and questions
     */
    public function show(Request $request, Response $response, $args)
    {
        $surveyId = $args['id'];
        $survey = $this->surveyModel->getWithSections($surveyId);

        if (!$survey) {
            return $this->renderNotFound($response);
        }

        $responseCount = $this->surveyModel->getResponseCount($surveyId);

        // Redirect to edit form for survey details
        return $response
            ->withHeader('Location', "/admin/surveys/{$surveyId}/edit")
            ->withStatus(302);
    }

    /**
     * GET /admin/surveys/{id}/edit
     * Show survey edit form
     */
    public function editForm(Request $request, Response $response, $args)
    {
        $surveyId = $args['id'];
        $survey = $this->surveyModel->getById($surveyId);

        if (!$survey) {
            return $this->renderNotFound($response);
        }

        $sections = $this->sectionModel->getBySurveyWithQuestions($surveyId);

        return $this->render($response, 'admin/survey-form', [
            'survey' => $survey,
            'sections' => $sections
        ]);
    }

    /**
     * PUT /admin/surveys/{id}
     * Update survey
     */
    public function update(Request $request, Response $response, $args)
    {
        $surveyId = $args['id'];
        $data = $request->getParsedBody();

        $survey = $this->surveyModel->getById($surveyId);
        if (!$survey) {
            return $this->renderNotFound($response);
        }

        try {
            $updateData = [
                'name' => $data['name'] ?? $survey['name'],
                'description' => $data['description'] ?? $survey['description'],
                'is_public' => isset($data['is_public']) ? (bool)$data['is_public'] : $survey['is_public']
            ];

            // Only update passkey if survey is private
            if (!$updateData['is_public']) {
                $updateData['passkey'] = $data['passkey'] ?? $survey['passkey'];
            } else {
                $updateData['passkey'] = null;
            }

            $this->surveyModel->update($surveyId, $updateData);

            return $response
                ->withHeader('Location', "/admin/surveys/{$surveyId}")
                ->withStatus(302);
        } catch (\Exception $e) {
            return $this->renderWithError($response, 'admin/survey-form', $e->getMessage());
        }
    }

    /**
     * DELETE /admin/surveys/{id}
     * Delete survey
     */
    public function delete(Request $request, Response $response, $args)
    {
        $surveyId = $args['id'];
        $survey = $this->surveyModel->getById($surveyId);

        if (!$survey) {
            return $this->renderNotFound($response);
        }

        try {
            $this->surveyModel->delete($surveyId);

            return $response
                ->withHeader('Location', '/admin/surveys')
                ->withStatus(302);
        } catch (\Exception $e) {
            return $this->renderWithError($response, 'admin/survey-form', $e->getMessage());
        }
    }

    /**
     * GET /
     * Public surveys index (for respondents)
     */
    public function publicIndex(Request $request, Response $response)
    {
        $surveys = $this->surveyModel->getAllPublic();

        return $this->render($response, 'public/index', [
            'surveys' => $surveys
        ]);
    }

    /**
     * GET /admin/surveys/{id}/share
     * Get shareable link for a survey
     */
    public function shareLink(Request $request, Response $response, $args)
    {
        $surveyId = $args['id'];
        
        $shareInfo = $this->surveyModel->getShareableInfo($surveyId, $this->config);
        
        if (!$shareInfo) {
            return $this->renderNotFound($response);
        }

        // Return JSON response for AJAX calls
        if ($request->hasHeader('X-Requested-With') && 
            $request->getHeaderLine('X-Requested-With') === 'XMLHttpRequest') {
            $response->getBody()->write(json_encode([
                'status' => 'success',
                'data' => $shareInfo
            ]));
            return $response->withHeader('Content-Type', 'application/json');
        }

        // For non-AJAX calls, return a simple share page
        return $this->render($response, 'admin/share-link', [
            'shareInfo' => $shareInfo
        ]);
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

    private function renderWithError(Response $response, $template, $error)
    {
        ob_start();
        extract(['error' => $error]);
        include __DIR__ . "/../Views/{$template}.php";
        $html = ob_get_clean();

        $response->getBody()->write($html);
        return $response->withStatus(400);
    }

    private function renderNotFound(Response $response)
    {
        $response->getBody()->write('Survey not found');
        return $response->withStatus(404);
    }
}
