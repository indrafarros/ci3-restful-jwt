<?php

defined('BASEPATH') or exit('No direct script access allowed');

use chriskacerguis\RestServer\RestController;

class AuthController extends RestController
{

    public function __construct()
    {
        parent::__construct();
        $this->load->model('Auth_model', 'auth');
    }

    public function login_post()
    {
        $email = $_POST['email'];
        $passwordInput = $_POST['password'];

        if (!empty($email) && !empty($passwordInput)) {
            $checkEmail = $this->auth->check_email($email);
            if ($checkEmail) {
                $passwordUser = $checkEmail['password'];
                if (password_verify($passwordInput, $passwordUser)) {
                    $set_session = [
                        'is_login' => 'true',
                        'first_name' => $checkEmail['first_name'],
                        'email' => $checkEmail['email'],
                        'roles' => $checkEmail['role_id']
                    ];

                    $this->session->userdata($set_session);

                    $this->response([
                        'status' => 'true',
                        'message' => 200,
                        'data' => $set_session
                    ], 200);
                }
            }
        }
    }
}
