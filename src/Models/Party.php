<?php

namespace App\Models;

use Medoo\Medoo;

class Party
{
    private $db;

    public function __construct(Medoo $database)
    {
        $this->db = $database;
    }

    public function getAll()
    {
        return $this->db->select('party', ['id', 'name']);
    }

    public function getById($id)
    {
        return $this->db->get('party', ['id', 'name'], ['id' => $id]);
    }

    public function create($data)
    {
        return $this->db->insert('party', [
            'name' => $data['name']
        ]);
    }

    public function update($id, $data)
    {
        return $this->db->update('party', [
            'name' => $data['name']
        ], ['id' => $id]);
    }

    public function delete($id)
    {
        return $this->db->delete('party', ['id' => $id]);
    }
}
