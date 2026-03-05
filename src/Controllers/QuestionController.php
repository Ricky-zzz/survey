<?php

namespace App\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use App\Models\Question;
use App\Models\QuestionOption;
use App\Models\Section;

class QuestionController
{
    private $questionModel;
    private $questionOptionModel;
    private $sectionModel;

    public function __construct(Question $questionModel, QuestionOption $questionOptionModel, Section $sectionModel)
    {
        $this->questionModel = $questionModel;
        $this->questionOptionModel = $questionOptionModel;
        $this->sectionModel = $sectionModel;
    }

    /**
     * POST /admin/sections/{sectionId}/questions
     * Create new question
     */
    public function store(Request $request, Response $response, $args)
    {
        $sectionId = $args['sectionId'];
        $data = $request->getParsedBody();

        // Verify section exists
        $section = $this->sectionModel->getById($sectionId);
        if (!$section) {
            return $this->renderNotFound($response);
        }

        // Validate required fields
        if (!isset($data['question_text']) || empty($data['question_text'])) {
            return $this->renderWithError($response, 'Question text is required', 400);
        }

        if (!isset($data['type']) || empty($data['type'])) {
            return $this->renderWithError($response, 'Question type is required', 400);
        }

        try {
            $maxOrder = $this->questionModel->getMaxOrderSequence($sectionId);

            $questionData = [
                'section_id' => $sectionId,
                'question_text' => $data['question_text'],
                'type' => $data['type'],
                'required' => isset($data['required']) ? (bool)$data['required'] : true,
                'allow_multiple_files' => isset($data['allow_multiple_files']) ? (bool)$data['allow_multiple_files'] : false,
                'order_sequence' => $maxOrder + 1
            ];

            $questionId = $this->questionModel->create($questionData);

            // Create options if provided (for scale and multiple_choice types)
            if (in_array($data['type'], ['scale', 'multiple_choice']) && isset($data['options'])) {
                $this->questionOptionModel->createMultiple($questionId, $data['options']);
            }

            return $this->jsonResponse($response, [
                'success' => true,
                'questionId' => $questionId,
                'message' => 'Question created successfully'
            ]);
        } catch (\Exception $e) {
            return $this->renderWithError($response, $e->getMessage(), 500);
        }
    }

    /**
     * PUT /admin/questions/{id}
     * Update question
     */
    public function update(Request $request, Response $response, $args)
    {
        $questionId = $args['id'];
        $data = $request->getParsedBody();

        $question = $this->questionModel->getById($questionId);
        if (!$question) {
            return $this->renderNotFound($response);
        }

        try {
            $updateData = [
                'question_text' => $data['question_text'] ?? $question['question_text'],
                'required' => isset($data['required']) ? (bool)$data['required'] : $question['required'],
                'allow_multiple_files' => isset($data['allow_multiple_files']) ? (bool)$data['allow_multiple_files'] : $question['allow_multiple_files']
            ];

            // Type cannot be changed after creation
            // if (isset($data['type'])) {
            //     $updateData['type'] = $data['type'];
            // }

            $this->questionModel->update($questionId, $updateData);

            // Update options if provided
            if (isset($data['options'])) {
                $this->questionOptionModel->deleteByQuestion($questionId);
                
                $this->questionOptionModel->createMultiple($questionId, $data['options']);
            }

            return $this->jsonResponse($response, [
                'success' => true,
                'message' => 'Question updated successfully'
            ]);
        } catch (\Exception $e) {
            return $this->renderWithError($response, $e->getMessage(), 500);
        }
    }

    /**
     * DELETE /admin/questions/{id}
     * Delete question
     */
    public function delete(Request $request, Response $response, $args)
    {
        $questionId = $args['id'];

        $question = $this->questionModel->getById($questionId);
        if (!$question) {
            return $this->renderNotFound($response);
        }

        try {
            // Delete question options first (cascade)
            $this->questionOptionModel->deleteByQuestion($questionId);
            
            // Delete the question
            $this->questionModel->delete($questionId);

            return $this->jsonResponse($response, [
                'success' => true,
                'message' => 'Question deleted successfully'
            ]);
        } catch (\Exception $e) {
            return $this->renderWithError($response, $e->getMessage(), 500);
        }
    }

    /**
     * POST /admin/questions/{questionId}/options
     * Add option to question
     */
    public function storeOption(Request $request, Response $response, $args)
    {
        $questionId = $args['questionId'];
        $data = $request->getParsedBody();

        $question = $this->questionModel->getById($questionId);
        if (!$question) {
            return $this->renderNotFound($response);
        }

        // Only scale and multiple_choice questions can have options
        if (!in_array($question['type'], ['scale', 'multiple_choice'])) {
            return $this->renderWithError($response, 'This question type cannot have options', 400);
        }

        if (!isset($data['option_text']) || empty($data['option_text'])) {
            return $this->renderWithError($response, 'Option text is required', 400);
        }

        try {
            $maxOrder = $this->questionOptionModel->getMaxOrderSequence($questionId);

            $optionData = [
                'question_id' => $questionId,
                'option_text' => $data['option_text'],
                'value' => $data['value'] ?? ($maxOrder + 2), 
                'order_sequence' => $maxOrder + 1
            ];

            $optionId = $this->questionOptionModel->create($optionData);

            return $this->jsonResponse($response, [
                'success' => true,
                'optionId' => $optionId,
                'message' => 'Option added successfully'
            ]);
        } catch (\Exception $e) {
            return $this->renderWithError($response, $e->getMessage(), 500);
        }
    }

    /**
     * DELETE /admin/options/{id}
     * Delete question option
     */
    public function deleteOption(Request $request, Response $response, $args)
    {
        $optionId = $args['id'];

        $option = $this->questionOptionModel->getById($optionId);
        if (!$option) {
            return $this->renderNotFound($response);
        }

        try {
            $this->questionOptionModel->delete($optionId);

            return $this->jsonResponse($response, [
                'success' => true,
                'message' => 'Option deleted successfully'
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
