<?php

class Auth_model extends CI_Model
{

    public function check_email($email)
    {
        return $this->db->get_where('users', ['email' => $email])->row_array();
    }
}
