<?php

namespace App\Models;

use Medoo\Medoo;

class Candidate
{
    private $db;
    private $config;

    public function __construct(Medoo $database, $config)
    {
        $this->db = $database;
        $this->config = $config;
    }

    public function getAll()
    {
        return $this->db->select('candidate', [
            '[>]party' => ['party_id' => 'id']
        ], [
            'candidate.id',
            'candidate.candidate_code',
            'candidate.lastname',
            'candidate.firstname',
            'candidate.middlename',
            'candidate.gender',
            'candidate.picture',
            'candidate.created_at',
            'party.id(party_id)',
            'party.name(party_name)'
        ], [
            'ORDER' => ['candidate.id' => 'DESC']
        ]);
    }

    public function getById($id)
    {
        return $this->db->get('candidate', [
            '[>]party' => ['party_id' => 'id']
        ], [
            'candidate.id',
            'candidate.candidate_code',
            'candidate.lastname',
            'candidate.firstname',
            'candidate.middlename',
            'candidate.gender',
            'candidate.picture',
            'candidate.created_at',
            'party.id(party_id)',
            'party.name(party_name)'
        ], ['candidate.id' => $id]);
    }

    public function create($data)
    {
        return $this->db->insert('candidate', [
            'candidate_code' => $data['candidate_code'],
            'lastname' => $data['lastname'],
            'firstname' => $data['firstname'],
            'middlename' => $data['middlename'] ?? null,
            'party_id' => $data['party_id'],
            'gender' => $data['gender'],
            'picture' => $data['picture'] ?? null
        ]);
    }

    public function update($id, $data)
    {
        $updateData = [
            'candidate_code' => $data['candidate_code'],
            'lastname' => $data['lastname'],
            'firstname' => $data['firstname'],
            'middlename' => $data['middlename'] ?? null,
            'party_id' => $data['party_id'],
            'gender' => $data['gender']
        ];

        if (isset($data['picture'])) {
            $updateData['picture'] = $data['picture'];
        }

        return $this->db->update('candidate', $updateData, ['id' => $id]);
    }

    public function delete($id)
    {
        // Get the picture path first to delete the file
        $candidate = $this->getById($id);
        if ($candidate && $candidate['picture']) {
            $picturePath = $this->config['upload_path'] . '/' . $candidate['picture'];
            if (file_exists($picturePath)) {
                unlink($picturePath);
            }
        }

        return $this->db->delete('candidate', ['id' => $id]);
    }

    public function getPictureUrl($picture)
    {
        if (!$picture) {
            return '/uploads/placeholder.png';
        }
        return '/uploads/' . $picture;
    }
}
