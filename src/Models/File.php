<?php

namespace App\Models;

use Medoo\Medoo;

class File
{
    private $db;

    public function __construct(Medoo $database)
    {
        $this->db = $database;
    }

    /**
     * Get file by ID
     * @return array|null
     */
    public function getById($id)
    {
        return $this->db->get('files', [
            'id',
            'respondent_id',
            'question_id',
            'file_path',
            'original_filename',
            'file_size',
            'file_type',
            'uploaded_at'
        ], ['id' => $id]);
    }

    /**
     * Get all files for a respondent
     * @return array
     */
    public function getByRespondent($respondentId)
    {
        return $this->db->select('files', [
            '[>]questions' => ['question_id' => 'id']
        ], [
            'files.id',
            'files.respondent_id',
            'files.question_id',
            'files.file_path',
            'files.original_filename',
            'files.file_size',
            'questions.question_text',
            'files.uploaded_at'
        ], [
            'files.respondent_id' => $respondentId,
            'ORDER' => ['files.uploaded_at' => 'DESC']
        ]);
    }

    /**
     * Get files for a respondent and specific question
     */
    public function getByRespondentAndQuestion($respondentId, $questionId)
    {
        return $this->db->select('files', [
            'id',
            'respondent_id',
            'question_id',
            'file_path',
            'original_filename',
            'file_size',
            'uploaded_at'
        ], [
            'respondent_id' => $respondentId,
            'question_id' => $questionId,
            'ORDER' => ['uploaded_at' => 'DESC']
        ]);
    }

    /**
     * Get all files for a question (across all respondents)
     */
    public function getByQuestion($questionId)
    {
        return $this->db->select('files', [
            '[>]respondents' => ['respondent_id' => 'id']
        ], [
            'files.id',
            'files.respondent_id',
            'files.question_id',
            'files.file_path',
            'files.original_filename',
            'files.file_size',
            'respondents.created_at(respondent_date)',
            'files.uploaded_at'
        ], [
            'files.question_id' => $questionId,
            'ORDER' => ['files.uploaded_at' => 'DESC']
        ]);
    }

    /**
     * Get all files for a survey
     */
    public function getBySurvey($surveyId)
    {
        return $this->db->select('files', [
            '[>]respondents' => ['respondent_id' => 'id'],
            '[>]questions' => ['question_id' => 'id']
        ], [
            'files.id',
            'files.respondent_id',
            'files.question_id',
            'files.file_path',
            'files.original_filename',
            'files.file_size',
            'questions.question_text',
            'respondents.created_at(respondent_date)',
            'files.uploaded_at'
        ], [
            'respondents.survey_id' => $surveyId,
            'ORDER' => ['files.uploaded_at' => 'DESC']
        ]);
    }

    /**
     * Create a new file record
     */
    public function create($data)
    {
        return $this->db->insert('files', [
            'respondent_id' => $data['respondent_id'],
            'question_id' => $data['question_id'],
            'file_path' => $data['file_path'],
            'original_filename' => $data['original_filename'],
            'file_size' => $data['file_size'],
            'file_type' => $data['file_type'] ?? 'pdf'
        ]);
    }

    /**
     * Create multiple file records (bulk insert)
     */
    public function createMultiple($respondentId, $questionId, $files)
    {
        $data = [];
        foreach ($files as $file) {
            $data[] = [
                'respondent_id' => $respondentId,
                'question_id' => $questionId,
                'file_path' => $file['file_path'],
                'original_filename' => $file['original_filename'],
                'file_size' => $file['file_size'],
                'file_type' => $file['file_type'] ?? 'pdf'
            ];
        }

        return $this->db->insert('files', $data);
    }

    /**
     * Delete file record and cleanup storage
     */
    public function delete($fileId, $uploadBasePath = null)
    {
        $file = $this->getById($fileId);
        if (!$file && $uploadBasePath) {
            // Try to delete file from storage
            $filePath = $uploadBasePath . DIRECTORY_SEPARATOR . $file['file_path'];
            if (file_exists($filePath)) {
                @unlink($filePath);
            }
        }

        return $this->db->delete('files', ['id' => $fileId]);
    }

    /**
     * Delete all files for a respondent
     */
    public function deleteByRespondent($respondentId)
    {
        return $this->db->delete('files', ['respondent_id' => $respondentId]);
    }

    /**
     * Delete all files for a respondent and question
     */
    public function deleteByRespondentAndQuestion($respondentId, $questionId)
    {
        return $this->db->delete('files', [
            'respondent_id' => $respondentId,
            'question_id' => $questionId
        ]);
    }

    /**
     * Get total file size for a respondent
     * @return int
     */
    public function getTotalSizeByRespondent($respondentId)
    {
        $result = $this->db->select('files', [
            ['SUM(file_size)' => 'total']
        ], [
            'respondent_id' => $respondentId
        ]);
        
        return (int)(($result[0]['total'] ?? 0) ?: 0);
    }

    /**
     * Get total file size for a survey
     * @return int
     */
    public function getTotalSizeBySurvey($surveyId)
    {
        $result = $this->db->select('files', [
            '[>]respondents' => ['respondent_id' => 'id']
        ], [
            ['SUM(file_size)' => 'total']
        ], [
            'respondents.survey_id' => $surveyId
        ]);
        
        return (int)(($result[0]['total'] ?? 0) ?: 0);
    }

    /**
     * Get file count for a survey
     * @return int
     */
    public function getCountBySurvey($surveyId)
    {
        $result = $this->db->select('files', [
            '[>]respondents' => ['respondent_id' => 'id']
        ], [
            ['COUNT(*)' => 'total']
        ], [
            'respondents.survey_id' => $surveyId
        ]);
        
        return (int)(($result[0]['total'] ?? 0) ?: 0);
    }
}
