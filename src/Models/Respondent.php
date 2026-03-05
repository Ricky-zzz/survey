<?php

namespace App\Models;

use Medoo\Medoo;

class Respondent
{
    private $db;

    public function __construct(Medoo $database)
    {
        $this->db = $database;
    }

    /**
     * Get respondent by ID
     * @return array|null
     */
    public function getById($id)
    {
        return $this->db->get('respondents', [
            'id',
            'survey_id',
            'submitted_at',
            'created_at',
            'updated_at'
        ], ['id' => $id]);
    }

    /**
     * Get all respondents for a survey
     * @return array
     */
    public function getBySurvey($surveyId, $limit = null, $offset = 0)
    {
        $options = [
            'survey_id' => $surveyId,
            'ORDER' => ['submitted_at' => 'DESC']
        ];

        if ($limit) {
            $options['LIMIT'] = [$offset, $limit];
        }

        return $this->db->select('respondents', [
            'id',
            'survey_id',
            'submitted_at',
            'created_at'
        ], $options);
    }

    /**
     * Get respondent with all their responses
     */
    public function getWithResponses($respondentId)
    {
        $respondent = $this->getById($respondentId);
        if (!$respondent) {
            return null;
        }

        $responseModel = new Response($this->db);
        $respondent['responses'] = $responseModel->getByRespondent($respondentId);

        return $respondent;
    }

    /**
     * Get respondent with responses and files
     */
    public function getWithResponsesAndFiles($respondentId)
    {
        $respondent = $this->getWithResponses($respondentId);
        if (!$respondent) {
            return null;
        }

        $fileModel = new File($this->db);
        $respondent['files'] = $fileModel->getByRespondent($respondentId);

        return $respondent;
    }

    /**
     * Create a new respondent record (for survey submission start)
     */
    public function create($surveyId)
    {
        return $this->db->insert('respondents', [
            'survey_id' => $surveyId
        ]);
    }

    /**
     * Mark respondent as submitted
     */
    public function markSubmitted($respondentId)
    {
        return $this->db->update('respondents', [
            'submitted_at' => $this->db->raw('NOW()')
        ], ['id' => $respondentId]);
    }

    /**
     * Delete respondent and cascade delete responses and files
     */
    public function delete($respondentId)
    {
        return $this->db->delete('respondents', ['id' => $respondentId]);
    }

    /**
     * Get count of submitted responses for a survey
     * @return int
     */
    public function getSubmittedCount($surveyId)
    {
        return $this->db->count('respondents', [
            'survey_id' => $surveyId,
            'submitted_at[!]' => null
        ]);
    }

    /**
     * Get email from respondent's responses (from respondent info section)
     */
    public function getEmail($respondentId)
    {
        // This fetches the answer to the email question from the respondent info section
        // We'll need to join with questions and responses
        $result = $this->db->get('responses', [
            '[>]questions' => ['question_id' => 'id'],
            '[>]sections' => ['questions.section_id' => 'id']
        ], [
            'responses.answer_value(email)'
        ], [
            'responses.respondent_id' => $respondentId,
            'sections.is_respondent_info' => true,
            'questions.question_text' => 'Email'
        ]);

        return $result ? $result['email'] : null;
    }

    /**
     * Get respondent info data (responses from respondent info section)
     */
    public function getRespondentInfo($respondentId)
    {
        $responses = $this->db->select('responses', [
            '[>]questions' => ['question_id' => 'id'],
            '[>]sections' => ['questions.section_id' => 'id']
        ], [
            'questions.id(question_id)',
            'questions.question_text',
            'responses.answer_value'
        ], [
            'responses.respondent_id' => $respondentId,
            'sections.is_respondent_info' => true,
            'ORDER' => ['questions.order_sequence' => 'ASC']
        ]);

        return $responses;
    }

    /**
     * Get latest submission (last respondent created)
     */
    public function getLatestForSurvey($surveyId)
    {
        return $this->db->get('respondents', [
            'id',
            'survey_id',
            'submitted_at',
            'created_at'
        ], [
            'survey_id' => $surveyId,
            'ORDER' => ['id' => 'DESC']
        ]);
    }
}
