<?php

namespace App\Models;

use Medoo\Medoo;

class Question
{
    private $db;

    public function __construct(Medoo $database)
    {
        $this->db = $database;
    }

    /**
     * Get question by ID
     * @return array|null
     */
    public function getById($id)
    {
        return $this->db->get('questions', [
            'id',
            'section_id',
            'question_text',
            'type',
            'required',
            'allow_multiple_files',
            'order_sequence',
            'created_at',
            'updated_at'
        ], ['id' => $id]);
    }

    /**
     * Get all questions for a section (ordered by sequence)
     * @return array
     */
    public function getBySection($sectionId)
    {
        return $this->db->select('questions', [
            'id',
            'section_id',
            'question_text',
            'type',
            'required',
            'allow_multiple_files',
            'order_sequence',
            'created_at'
        ], [
            'section_id' => $sectionId,
            'ORDER' => ['order_sequence' => 'ASC']
        ]);
    }

    /**
     * Get question with all its options
     */
    public function getWithOptions($questionId)
    {
        $question = $this->getById($questionId);
        if (!$question) {
            return null;
        }

        // Only fetch options for questions that have them
        if (in_array($question['type'], ['scale', 'multiple_choice'])) {
            $optionModel = new QuestionOption($this->db);
            $question['options'] = $optionModel->getByQuestion($questionId);
        }

        return $question;
    }

    /**
     * Get all questions for a section with their options
     */
    public function getBySectionWithOptions($sectionId)
    {
        $questions = $this->getBySection($sectionId);
        $optionModel = new QuestionOption($this->db);

        foreach ($questions as &$question) {
            if (in_array($question['type'], ['scale', 'multiple_choice'])) {
                $question['options'] = $optionModel->getByQuestion($question['id']);
            } else {
                $question['options'] = [];
            }
        }

        return $questions;
    }

    /**
     * Create a new question
     */
    public function create($data)
    {
        return $this->db->insert('questions', [
            'section_id' => $data['section_id'],
            'question_text' => $data['question_text'],
            'type' => $data['type'],
            'required' => $data['required'] ?? true,
            'allow_multiple_files' => $data['allow_multiple_files'] ?? false,
            'order_sequence' => $data['order_sequence'] ?? 0
        ]);
    }

    /**
     * Update question
     */
    public function update($id, $data)
    {
        $updateData = [];

        if (isset($data['question_text'])) {
            $updateData['question_text'] = $data['question_text'];
        }
        if (isset($data['type'])) {
            $updateData['type'] = $data['type'];
        }
        if (isset($data['required'])) {
            $updateData['required'] = $data['required'];
        }
        if (isset($data['allow_multiple_files'])) {
            $updateData['allow_multiple_files'] = $data['allow_multiple_files'];
        }
        if (isset($data['order_sequence'])) {
            $updateData['order_sequence'] = $data['order_sequence'];
        }

        return $this->db->update('questions', $updateData, ['id' => $id]);
    }

    /**
     * Delete question (cascades to options, responses, files)
     */
    public function delete($id)
    {
        return $this->db->delete('questions', ['id' => $id]);
    }

    /**
     * Get questions by type for a section (useful for filtering)
     */
    public function getBySectionAndType($sectionId, $type)
    {
        return $this->db->select('questions', [
            'id',
            'section_id',
            'question_text',
            'type',
            'required',
            'order_sequence',
            'created_at'
        ], [
            'section_id' => $sectionId,
            'type' => $type,
            'ORDER' => ['order_sequence' => 'ASC']
        ]);
    }

    /**
     * Get questions requiring files for a survey
     */
    public function getFileUploadQuestions($surveyId)
    {
        return $this->db->select('questions', [
            '[>]sections' => ['section_id' => 'id']
        ], [
            'questions.id',
            'questions.section_id',
            'questions.question_text',
            'questions.allow_multiple_files',
            'sections.survey_id'
        ], [
            'sections.survey_id' => $surveyId,
            'questions.type' => 'file_upload',
            'ORDER' => ['questions.order_sequence' => 'ASC']
        ]);
    }

    /**
     * Get max order_sequence for a section
     * @return int
     */
    public function getMaxOrderSequence($sectionId)
    {
        $result = $this->db->select('questions', [
            ['MAX(order_sequence)' => 'max_order']
        ], [
            'section_id' => $sectionId
        ]);
        
        return (int)(($result[0]['max_order'] ?? -1) ?: -1);
    }
}
