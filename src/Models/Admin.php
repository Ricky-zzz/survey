<?php

namespace App\Models;

use Medoo\Medoo;

class Admin
{
    private $db;

    public function __construct(Medoo $database)
    {
        $this->db = $database;
    }

    /**
     * Get all admins
     */
    public function getAll()
    {
        return $this->db->select('admins', [
            'id',
            'username',
            'email',
            'created_at'
        ], [
            'ORDER' => ['id' => 'DESC']
        ]);
    }

    /**
     * Get admin by ID
     */
    public function getById($id)
    {
        return $this->db->get('admins', [
            'id',
            'username',
            'email',
            'created_at'
        ], ['id' => $id]);
    }

    /**
     * Get admin by username
     */
    public function getByUsername($username)
    {
        return $this->db->get('admins', [
            'id',
            'username',
            'email',
            'password_hash',
            'created_at'
        ], ['username' => $username]);
    }

    /**
     * Create a new admin
     */
    public function create($data)
    {
        return $this->db->insert('admins', [
            'username' => $data['username'],
            'email' => $data['email'],
            'password_hash' => password_hash($data['password'], PASSWORD_BCRYPT)
        ]);
    }

    /**
     * Update admin
     */
    public function update($id, $data)
    {
        $updateData = [];
        
        if (isset($data['username'])) {
            $updateData['username'] = $data['username'];
        }
        if (isset($data['email'])) {
            $updateData['email'] = $data['email'];
        }
        if (isset($data['password'])) {
            $updateData['password_hash'] = password_hash($data['password'], PASSWORD_BCRYPT);
        }

        return $this->db->update('admins', $updateData, ['id' => $id]);
    }

    /**
     * Delete admin
     */
    public function delete($id)
    {
        return $this->db->delete('admins', ['id' => $id]);
    }

    /**
     * Verify admin password
     */
    public function verifyPassword($username, $password)
    {
        $admin = $this->getByUsername($username);
        if (!$admin) {
            return false;
        }
        return password_verify($password, $admin['password_hash']);
    }
}
