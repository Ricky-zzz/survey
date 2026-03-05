<?php

namespace App\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use App\Models\Section;
use App\Models\Survey;

class SectionController
{
    private $sectionModel;
    private $surveyModel;

    public function __construct(Section $sectionModel, Survey $surveyModel)
    {
        $this->sectionModel = $sectionModel;
        $this->surveyModel = $surveyModel;
    }

    /**
     * POST /admin/surveys/{surveyId}/sections
     * Create new section
     */
    public function store(Request $request, Response $response, $args)
    {
        $surveyId = $args['surveyId'];
        $data = $request->getParsedBody();

        // Verify survey exists
        $survey = $this->surveyModel->getById($surveyId);
        if (!$survey) {
            return $this->renderNotFound($response);
        }

        // Validate required fields
        if (!isset($data['title']) || empty($data['title'])) {
            return $this->renderWithError($response, 'Section title is required', 400);
        }

        try {
            $maxOrder = $this->sectionModel->getMaxOrderSequence($surveyId);

            $sectionData = [
                'survey_id' => $surveyId,
                'title' => $data['title'],
                'description' => $data['description'] ?? '',
                'is_respondent_info' => false,
                'order_sequence' => $maxOrder + 1
            ];

            $sectionId = $this->sectionModel->create($sectionData);

            // Return JSON for AJAX request
            return $this->jsonResponse($response, [
                'success' => true,
                'sectionId' => $sectionId,
                'message' => 'Section created successfully'
            ]);
        } catch (\Exception $e) {
            return $this->renderWithError($response, $e->getMessage(), 500);
        }
    }

    /**
     * PUT /admin/surveys/{surveyId}/sections/{id}
     * Update section
     */
    public function update(Request $request, Response $response, $args)
    {
        $surveyId = $args['surveyId'];
        $sectionId = $args['id'];
        $data = $request->getParsedBody();

        // Verify survey and section exist
        $survey = $this->surveyModel->getById($surveyId);
        if (!$survey) {
            return $this->renderNotFound($response);
        }

        $section = $this->sectionModel->getById($sectionId);
        if (!$section || $section['survey_id'] != $surveyId) {
            return $this->renderNotFound($response);
        }

        // Cannot edit respondent info section's properties
        if ($section['is_respondent_info']) {
            return $this->renderWithError($response, 'Cannot edit respondent info section', 403);
        }

        try {
            $updateData = [
                'title' => $data['title'] ?? $section['title'],
                'description' => $data['description'] ?? $section['description']
            ];

            if (isset($data['order_sequence'])) {
                $updateData['order_sequence'] = $data['order_sequence'];
            }

            $this->sectionModel->update($sectionId, $updateData);

            return $this->jsonResponse($response, [
                'success' => true,
                'message' => 'Section updated successfully'
            ]);
        } catch (\Exception $e) {
            return $this->renderWithError($response, $e->getMessage(), 500);
        }
    }

    /**
     * DELETE /admin/surveys/{surveyId}/sections/{id}
     * Delete section
     */
    public function delete(Request $request, Response $response, $args)
    {
        $surveyId = $args['surveyId'];
        $sectionId = $args['id'];

        // Verify survey and section exist
        $survey = $this->surveyModel->getById($surveyId);
        if (!$survey) {
            return $this->renderNotFound($response);
        }

        $section = $this->sectionModel->getById($sectionId);
        if (!$section || $section['survey_id'] != $surveyId) {
            return $this->renderNotFound($response);
        }

        // Cannot delete respondent info section
        if ($section['is_respondent_info']) {
            return $this->renderWithError($response, 'Cannot delete respondent info section', 403);
        }

        try {
            $this->sectionModel->delete($sectionId);

            return $this->jsonResponse($response, [
                'success' => true,
                'message' => 'Section deleted successfully'
            ]);
        } catch (\Exception $e) {
            return $this->renderWithError($response, $e->getMessage(), 500);
        }
    }

    // Helper methods
    private function jsonResponse(Response $response, $data, $statusCode = 200)
    {
        $response->getBody()->write(json_encode($data));
        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus($statusCode);
    }

    private function renderWithError(Response $response, $message, $statusCode = 400)
    {
        return $this->jsonResponse($response, [
            'success' => false,
            'message' => $message
        ], $statusCode);
    }

    private function renderNotFound(Response $response)
    {
        return $this->jsonResponse($response, [
            'success' => false,
            'message' => 'Resource not found'
        ], 404);
    }
}
