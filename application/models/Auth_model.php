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

    public function checkUserId($id = null)
    {

        return $this->db->get_where('users', ['id' => $id])->row_array();
    }

    public function getUsers($id = null, $limit = 5, $offset = 0)
    {
        if ($id === null) {

            return $this->db->get('users', $limit, $offset)->result();
        } else {
            return $this->db->get_where('users', ['id' => $id])->result_array();
        }
    }

    public function userCount()
    {
        return $this->db->get('users')->num_rows();
    }
}
