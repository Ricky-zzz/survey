<?php

namespace App\Models;

use Medoo\Medoo;

class Section
{
    private $db;

    public function __construct(Medoo $database)
    {
        $this->db = $database;
    }

    /**
     * Get section by ID
     * @return array|null
     */
    public function getById($id)
    {
        return $this->db->get('sections', [
            'id',
            'survey_id',
            'title',
            'description',
            'is_respondent_info',
            'order_sequence',
            'created_at'
        ], ['id' => $id]);
    }

    /**
     * Get all sections for a survey (ordered by sequence)
     * @return array
     */
    public function getBySurvey($surveyId)
    {
        return $this->db->select('sections', [
            'id',
            'survey_id',
            'title',
            'description',
            'is_respondent_info',
            'order_sequence',
            'created_at'
        ], [
            'survey_id' => $surveyId,
            'ORDER' => ['order_sequence' => 'ASC']
        ]);
    }

    /**
     * Get section with all its questions
     */
    public function getWithQuestions($sectionId)
    {
        $section = $this->getById($sectionId);
        if (!$section) {
            return null;
        }

        $questionModel = new Question($this->db);
        $section['questions'] = $questionModel->getBySection($sectionId);

        return $section;
    }

    /**
     * Get survey's sections with all their questions (nested structure)
     */
    public function getBySurveyWithQuestions($surveyId)
    {
        $sections = $this->getBySurvey($surveyId);
        
        $questionModel = new Question($this->db);
        foreach ($sections as &$section) {
            $section['questions'] = $questionModel->getBySection($section['id']);
        }

        return $sections;
    }

    /**
     * Get respondent info section for a survey
     */
    public function getRespondentInfoSection($surveyId)
    {
        return $this->db->get('sections', [
            'id',
            'survey_id',
            'title',
            'description',
            'order_sequence',
            'created_at'
        ], [
            'survey_id' => $surveyId,
            'is_respondent_info' => true
        ]);
    }

    /**
     * Create a new section
     */
    public function create($data)
    {
        return $this->db->insert('sections', [
            'survey_id' => $data['survey_id'],
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'is_respondent_info' => $data['is_respondent_info'] ?? false,
            'order_sequence' => $data['order_sequence'] ?? 0
        ]);
    }

    /**
     * Update section
     */
    public function update($id, $data)
    {
        $updateData = [];

        if (isset($data['title'])) {
            $updateData['title'] = $data['title'];
        }
        if (isset($data['description'])) {
            $updateData['description'] = $data['description'];
        }
        if (isset($data['order_sequence'])) {
            $updateData['order_sequence'] = $data['order_sequence'];
        }

        return $this->db->update('sections', $updateData, ['id' => $id]);
    }

    /**
     * Delete section (cascades to questions, responses, files)
     */
    public function delete($id)
    {
        return $this->db->delete('sections', ['id' => $id]);
    }

    /**
     * Get max order_sequence for a survey (for ordering new sections)
     * @return int
     */
    public function getMaxOrderSequence($surveyId)
    {
        $result = $this->db->select('sections', [
            ['MAX(order_sequence)' => 'max_order']
        ], [
            'survey_id' => $surveyId
        ]);
        
        return (int)(($result[0]['max_order'] ?? -1) ?: -1);
    }
}
