<?php

namespace App\Models;

use Medoo\Medoo;

class QuestionOption
{
    private $db;

    public function __construct(Medoo $database)
    {
        $this->db = $database;
    }

    /**
     * Get option by ID
     * @return array|null
     */
    public function getById($id)
    {
        return $this->db->get('question_options', [
            'id',
            'question_id',
            'option_text',
            'value',
            'order_sequence',
            'created_at'
        ], ['id' => $id]);
    }

    /**
     * Get all options for a question (ordered by sequence)
     * @return array
     */
    public function getByQuestion($questionId)
    {
        return $this->db->select('question_options', [
            'id',
            'question_id',
            'option_text',
            'value',
            'order_sequence',
            'created_at'
        ], [
            'question_id' => $questionId,
            'ORDER' => ['order_sequence' => 'ASC']
        ]);
    }

    /**
     * Create a new option
     */
    public function create($data)
    {
        return $this->db->insert('question_options', [
            'question_id' => $data['question_id'],
            'option_text' => $data['option_text'],
            'value' => $data['value'],
            'order_sequence' => $data['order_sequence'] ?? 0
        ]);
    }

    /**
     * Create multiple options (bulk insert)
     */
    public function createMultiple($questionId, $options)
    {
        $data = [];
        foreach ($options as $sequence => $option) {
            $data[] = [
                'question_id' => $questionId,
                'option_text' => $option['text'] ?? $option,
                'value' => $option['value'] ?? ($sequence + 1),
                'order_sequence' => $sequence
            ];
        }

        return $this->db->insert('question_options', $data);
    }

    /**
     * Update option
     */
    public function update($id, $data)
    {
        $updateData = [];

        if (isset($data['option_text'])) {
            $updateData['option_text'] = $data['option_text'];
        }
        if (isset($data['value'])) {
            $updateData['value'] = $data['value'];
        }
        if (isset($data['order_sequence'])) {
            $updateData['order_sequence'] = $data['order_sequence'];
        }

        return $this->db->update('question_options', $updateData, ['id' => $id]);
    }

    /**
     * Delete option
     */
    public function delete($id)
    {
        return $this->db->delete('question_options', ['id' => $id]);
    }

    /**
     * Delete all options for a question
     */
    public function deleteByQuestion($questionId)
    {
        return $this->db->delete('question_options', ['question_id' => $questionId]);
    }

    /**
     * Get option by value (useful for finding selected option by value)
     */
    public function getByQuestionAndValue($questionId, $value)
    {
        return $this->db->get('question_options', [
            'id',
            'question_id',
            'option_text',
            'value',
            'order_sequence'
        ], [
            'question_id' => $questionId,
            'value' => $value
        ]);
    }

    /**
     * Get max order_sequence for a question
     * @return int
     */
    public function getMaxOrderSequence($questionId)
    {
        $result = $this->db->select('question_options', [
            ['MAX(order_sequence)' => 'max_order']
        ], [
            'question_id' => $questionId
        ]);
        
        return (int)(($result[0]['max_order'] ?? -1) ?: -1);
    }
}
