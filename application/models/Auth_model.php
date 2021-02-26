<?php

class Auth_model extends CI_Model
{

    public function check_email($email)
    {
        return $this->db->get_where('users', ['email' => $email])->row_array();
    }

    public function registerUser($data)
    {
        return $this->db->insert('users', $data);
    }

    public function checkUserId($id)
    {
        return $this->db->get_where('users', ['id' => $id])->row_array();
    }

    public function getUsers()
    {
        return $this->db->get('users')->result_array();
    }
}
