<?php

namespace App\Models;

use Medoo\Medoo;

class Survey
{
    private $db;

    public function __construct(Medoo $database)
    {
        $this->db = $database;
    }

    /**
     * Get all surveys
     * @return array
     */
    public function getAll()
    {
        return $this->db->select('surveys', [
            '[>]admins' => ['created_by' => 'id']
        ], [
            'surveys.id',
            'surveys.name',
            'surveys.description',
            'surveys.is_public',
            'surveys.passkey',
            'surveys.created_at',
            'surveys.updated_at',
            'admins.id(admin_id)',
            'admins.username(admin_name)'
        ], [
            'ORDER' => ['surveys.id' => 'DESC']
        ]);
    }

    /**
     * Get survey by ID
     * @return array|null
     */
    public function getById($id)
    {
        return $this->db->get('surveys', [
            '[>]admins' => ['created_by' => 'id']
        ], [
            'surveys.id',
            'surveys.name',
            'surveys.description',
            'surveys.is_public',
            'surveys.passkey',
            'surveys.created_at',
            'surveys.updated_at',
            'admins.id(admin_id)',
            'admins.username(admin_name)'
        ], ['surveys.id' => $id]);
    }

    /**
     * Get survey with all sections and questions (for respondent view and admin edit)
     */
    public function getWithSections($surveyId)
    {
        $survey = $this->getById($surveyId);
        if (!$survey) {
            return null;
        }

        $sectionModel = new Section($this->db);
        $survey['sections'] = $sectionModel->getBySurveyWithQuestions($surveyId);

        return $survey;
    }

    /**
     * Get all public surveys (for public index)
     * @return array|null
     */
    public function getAllPublic()
    {
        return $this->db->select('surveys', [
            '[>]admins' => ['created_by' => 'id']
        ], [
            'surveys.id',
            'surveys.name',
            'surveys.description',
            'surveys.is_public',
            'surveys.created_at',
            'admins.username(admin_name)'
        ], [
            'surveys.is_public' => true,
            'ORDER' => ['surveys.id' => 'DESC']
        ]);
    }

    /**
     * Create a new survey
     */
    public function create($data)
    {
        return $this->db->insert('surveys', [
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'is_public' => $data['is_public'] ?? true,
            'passkey' => $data['passkey'] ?? null,
            'created_by' => $data['created_by']
        ]);
    }

    /**
     * Update survey
     */
    public function update($id, $data)
    {
        $updateData = [];

        if (isset($data['name'])) {
            $updateData['name'] = $data['name'];
        }
        if (isset($data['description'])) {
            $updateData['description'] = $data['description'];
        }
        if (isset($data['is_public'])) {
            $updateData['is_public'] = $data['is_public'];
        }
        if (isset($data['passkey'])) {
            $updateData['passkey'] = $data['passkey'];
        }

        return $this->db->update('surveys', $updateData, ['id' => $id]);
    }

    /**
     * Delete survey (cascades to sections, questions, responses, files)
     */
    public function delete($id)
    {
        return $this->db->delete('surveys', ['id' => $id]);
    }

    /**
     * Verify passkey for private survey
     */
    public function verifyPasskey($surveyId, $passkey)
    {
        $survey = $this->getById($surveyId);
        if (!$survey) {
            return false;
        }

        // If survey is public, no passkey needed
        if ($survey['is_public']) {
            return true;
        }

        // For private surveys, compare passkey
        return $survey['passkey'] === $passkey;
    }

    /**
     * Get response count for a survey
     * @return int
     */
    public function getResponseCount($surveyId)
    {
        return $this->db->count('respondents', ['survey_id' => $surveyId, 'submitted_at[!]' => null]);
    }
}
