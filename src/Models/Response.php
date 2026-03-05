<?php

namespace App\Models;

use Medoo\Medoo;

class Response
{
    private $db;

    public function __construct(Medoo $database)
    {
        $this->db = $database;
    }

    /**
     * Get response by ID
     * @return array|null
     */
    public function getById($id)
    {
        return $this->db->get('responses', [
            'id',
            'respondent_id',
            'question_id',
            'answer_value',
            'created_at',
            'updated_at'
        ], ['id' => $id]);
    }

    /**
     * Get all responses for a respondent
     * @return array
     */
    public function getByRespondent($respondentId)
    {
        return $this->db->select('responses', [
            '[>]questions' => ['question_id' => 'id']
        ], [
            'responses.id',
            'responses.respondent_id',
            'responses.question_id',
            'responses.answer_value',
            'questions.question_text',
            'questions.type',
            'responses.created_at'
        ], [
            'responses.respondent_id' => $respondentId,
            'ORDER' => ['questions.order_sequence' => 'ASC']
        ]);
    }

    /**
     * Get responses for a specific question
     * @return array
     */
    public function getByQuestion($questionId)
    {
        return $this->db->select('responses', [
            '[>]respondents' => ['respondent_id' => 'id']
        ], [
            'responses.id',
            'responses.respondent_id',
            'responses.question_id',
            'responses.answer_value',
            'respondents.survey_id',
            'respondents.created_at'
        ], [
            'responses.question_id' => $questionId,
            'ORDER' => ['respondents.created_at' => 'DESC']
        ]);
    }

    /**
     * Get response for a specific respondent and question
     */
    public function getByRespondentAndQuestion($respondentId, $questionId)
    {
        return $this->db->get('responses', [
            '[>]questions' => ['question_id' => 'id']
        ], [
            'responses.id',
            'responses.respondent_id',
            'responses.question_id',
            'responses.answer_value',
            'questions.question_text',
            'questions.type'
        ], [
            'responses.respondent_id' => $respondentId,
            'responses.question_id' => $questionId
        ]);
    }

    /**
     * Get all responses for a survey (for analysis/results)
     */
    public function getBySurvey($surveyId)
    {
        return $this->db->select('responses', [
            '[>]respondents' => ['respondent_id' => 'id'],
            '[>]questions' => ['question_id' => 'id']
        ], [
            'responses.id',
            'responses.respondent_id',
            'responses.question_id',
            'responses.answer_value',
            'questions.question_text',
            'questions.type',
            'respondents.created_at(submitted_at)'
        ], [
            'respondents.survey_id' => $surveyId,
            'ORDER' => ['respondents.created_at' => 'DESC']
        ]);
    }

    /**
     * Create a new response
     */
    public function create($data)
    {
        return $this->db->insert('responses', [
            'respondent_id' => $data['respondent_id'],
            'question_id' => $data['question_id'],
            'answer_value' => $data['answer_value']
        ]);
    }

    /**
     * Create multiple responses (bulk insert for all questions)
     */
    public function createMultiple($respondentId, $responses)
    {
        $data = [];
        foreach ($responses as $questionId => $answerValue) {
            $data[] = [
                'respondent_id' => $respondentId,
                'question_id' => $questionId,
                'answer_value' => $answerValue
            ];
        }

        return $this->db->insert('responses', $data);
    }

    /**
     * Update response
     */
    public function update($respondentId, $questionId, $answerValue)
    {
        return $this->db->update('responses', [
            'answer_value' => $answerValue
        ], [
            'respondent_id' => $respondentId,
            'question_id' => $questionId
        ]);
    }

    /**
     * Delete response
     */
    public function delete($responsId)
    {
        return $this->db->delete('responses', ['id' => $responsId]);
    }

    /**
     * Delete all responses for a respondent
     */
    public function deleteByRespondent($respondentId)
    {
        return $this->db->delete('responses', ['respondent_id' => $respondentId]);
    }

    /**
     * Get average response value for a question (for scale/numeric questions)
     * @return float
     */
    public function getAverageForQuestion($questionId)
    {
        $result = $this->db->select('responses', [
            ['AVG(answer_value)' => 'average']
        ], [
            'question_id' => $questionId
        ]);
        return $result && isset($result[0]['average']) ? round((float)$result[0]['average'], 2) : 0.0;
    }

    /**
     * Get response statistics for a question (count by value)
     * @return array
     */
    public function getStatisticsForQuestion($questionId)
    {
        return $this->db->select('responses', [
            'answer_value',
            ['COUNT(*)' => 'count']
        ], [
            'question_id' => $questionId,
            'GROUP' => 'answer_value'
        ]);
    }

    /**
     * Check if respondent has answered a question
     */
    public function hasAnswered($respondentId, $questionId)
    {
        return $this->db->get('responses', ['id'], [
            'respondent_id' => $respondentId,
            'question_id' => $questionId
        ]) !== false;
    }
}
