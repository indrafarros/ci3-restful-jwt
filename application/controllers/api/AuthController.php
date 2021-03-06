<?php

defined('BASEPATH') or exit('No direct script access allowed');


use chriskacerguis\RestServer\RestController;

class AuthController extends RestController
{
    public function __construct()
    {
        parent::__construct();

        // header('Access-Control-Allow-Origin: *');
        // header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
        $this->load->model('Auth_model', 'auth');
        $this->load->library('Authorization_Token');
    }

    public function users_get()
    {
        header("Access-Control-Allow-Origin: *");
        // Users from a data store e.g. database
        $id = $this->get('id');


        if ($id === null) {
            $userCheck = $this->auth->getUsers();

            $page = $this->get('page');
            $page = (empty($page) ? 1 : $page);
            $totalData = $this->auth->userCount();
            $totalPage = ceil($totalData / 5);
            $startPage = ($page - 1) * 5;
            $listData = $this->auth->getUsers(null, 5, $startPage);

            $returnData =
                [
                    'status' => true,
                    'page' => $page,
                    'total_data' => $totalData,
                    'total_page' => $totalPage,
                    'data' => $listData
                ];
            $this->response([
                'status' => true,
                'message' => $returnData
            ], 200);
        } else {
            $userCheck = $this->auth->getUsers($id);
            if ($userCheck) {

                $this->response([
                    'status' => true,
                    'message' => $userCheck
                ], 200);
            } else {
                $this->response([
                    'status' => false,
                    'message' => 'Data not found'
                ], 404);
            }
        }
    }

    public function login_post()
    {

        header("Access-Control-Allow-Origin: *");
        $this->form_validation->set_rules('email', 'Email', 'required|valid_email');
        $this->form_validation->set_rules('password', 'password', 'required');

        if ($this->form_validation->run() == FALSE) {
            $this->response([
                'status' => false,
                'message' => 'No users were found'
            ], 404);
        } else {
            $email = $_POST['email'];
            $password = $_POST['password'];

            $emailCheck = $this->auth->check_email($email);

            if ($emailCheck) {
                $passwordUser = $emailCheck['password'];
                if (password_verify($password, $passwordUser)) {
                    $userData = [
                        'id' => $emailCheck['id'],
                        'first_name' => $emailCheck['first_name'],
                        'email' => $emailCheck['email'],
                        'role_id' => $emailCheck['role_id'],
                    ];

                    $generateToken = $this->authorization_token->generateToken($userData);

                    $returnData = [
                        'id' => $emailCheck['id'],
                        'first_name' => $emailCheck['first_name'],
                        'email' => $emailCheck['email'],
                        'role_id' => $emailCheck['role_id'],
                        'token' => $generateToken
                    ];

                    $this->response([
                        'status' => true,
                        'data' => $returnData,
                    ], 200);
                } else {
                    $this->response([
                        'status' => false,
                        'message' => 'Something wrong, please try again'
                    ], 404);
                }
            } else {
                $this->response([
                    'status' => false,
                    'message' => 'Something wrong, please try again'
                ], 404);
            }

            // $email = $_POST['email'];
            // $password = $_POST['password'];
            // $user_check = $this->auth->check_email($email);
            // if ($user_check) {
            //     $token['id'] =  $user_check['id'];
            //     $token['first_name'] =  $user_check['first_name'];
            //     $token['email'] =  $user_check['email'];
            //     $token['role_id'] = $user_check['role_id'];
            //     $userToken = $this->Authorization_Token->generateToken($token);

            //     if (password_verify($password, $user_check['password'])) {
            //         $session = [
            //             'is_login' => 'true',
            //             'first_name' => $user_check['first_name'],
            //             'email' => $user_check['email'],
            //             'roles' => $user_check['roles'],
            //             'token' => $userToken
            //         ];
            //         // $this->session->set_userdata($session);

            //         $this->response([
            //             'status' => true,
            //             'data' =>   $this->session->set_userdata($session)
            //         ], 200);
            //     } else {
            //         $this->response([
            //             'status' => false,
            //             'message' => 'Email or password wrong'
            //         ], 404);
            //     }
            // } else {
            //     $this->response([
            //         'status' => false,
            //         'message' => 'Email or password wrong'
            //     ], 404);
            // }
        }
    }

    public function registration_post()
    {
        $config = [
            [
                'field' => 'first_name',
                'label' => 'first_name',
                'rules' => 'required|alpha_dash|trim',
                'errors' => [
                    'required' => 'This field cannot be null',
                    'alpha_dash' => 'You can only use a-z 0-9 _ . – characters for input'

                ],
            ],
            [
                'field' => 'last_name',
                'label' => 'last_name',
                'rules' => 'required|alpha_dash|trim',
                'errors' => [
                    'required' => 'This field cannot be null',
                    'alpha_dash' => 'You can only use a-z 0-9 _ . – characters for input',
                ],
            ],
            [
                'field' => 'email',
                'label' => 'Email',
                'rules' => 'required|valid_email|is_unique[users.email]',
                'errors' => [
                    'required' => 'This field cannot be null.',
                    'is_unique' => 'This email has already registered!'
                ],
            ],
            [
                'field' => 'password',
                'label' => 'Password',
                'rules' => 'required|min_length[6]|matches[password_confirmation]',
                'errors' => [
                    'matches' => 'Password dont match!',
                    'required' => 'You must provide a Password.',
                    'min_length' => 'Minimum Password length is 6 characters',
                ],
            ],
            [
                'field' => 'password_confirmation',
                'label' => 'password_confirmation',
                'rules' => 'required|min_length[6]|matches[password]',
                'errors' => [
                    'matches' => 'Password dont match!',
                    'required' => 'You must provide a Password.',
                    'min_length' => 'Minimum Password length is 6 characters',
                ],
            ],
            [
                'field' => 'phone',
                'label' => 'phone',
                'rules' => 'numeric',
                'errors' => [
                    'numeric' => 'This field only accept numbers',
                ],
            ],
            [
                'field' => 'accept_terms',
                'label' => 'Terms',
                'rules' => 'trim|required|greater_than[0]',
                'errors' => [
                    'required' => 'You should accept terms'
                ]
            ]
        ];
        $this->form_validation->set_rules($config);

        if ($this->form_validation->run() == FALSE) {
            $this->response([
                'status' => false,
                'message' => validation_errors()
            ], 404);
        } else {
            $firstName = $_POST['first_name'];
            $lastName = $_POST['last_name'];
            $email = $_POST['email'];
            $password = $_POST['password'];
            $photoPath = 'default.png';
            $phone = '';
            $createdAt = time();
            $roles = 1;

            $token = base64_encode(random_bytes(32));

            $emailConfig = [
                'protocol'  => 'smtp',
                'smtp_host' => 'ssl://smtp.googlemail.com',
                'smtp_user' => 'indradullanov1@gmail.com',
                'smtp_pass' => 'emansudirman123',
                'smtp_port' => 465,
                'mailtype'  => 'html',
                'charset'   => 'utf-8',
                'newline'   => "\r\n"
            ];

            $user_check = $this->user->check_user($email);

            if ($user_check > 0) {
                $this->response([
                    'status' => false,
                    'message' => 'Email already registered, check your email for verification'
                ], 404);
            } else {
                $data = [
                    'first_name' => $firstName,
                    'last_name' => $lastName,
                    'email' => $email,
                    'password' => password_hash($password, PASSWORD_DEFAULT),
                    'photo_path' => $photoPath,
                    'phone' => $phone,
                    'created_at' => $createdAt,
                    'modified' => '',
                    'is_active' => 0,
                    'role_id' => $roles,
                    'deleted_at' => '',
                    'last_login' => ''
                ];
            }
        }



        $emailConfig = [
            'protocol'  => 'smtp',
            'smtp_host' => 'ssl://smtp.googlemail.com',
            'smtp_user' => 'indradullanov1@gmail.com',
            'smtp_pass' => 'emansudirman123',
            'smtp_port' => 465,
            'mailtype'  => 'html',
            'charset'   => 'utf-8',
            'newline'   => "\r\n"
        ];

        if (!empty($firstName) && !empty($lastName) && !empty($email) && !empty($password)) {
            // $data['email'] = $email;
            $user_check = $this->user->check_user($email);

            if ($user_check > 0) {
                $this->response([
                    'status' => false,
                    'message' => 'Email already registered, check your email for verification'
                ], 404);
            } else {
                $data = [
                    'first_name' => $firstName,
                    'last_name' => $lastName,
                    'email' => $email,
                    'password' => password_hash($password, PASSWORD_DEFAULT),
                    'photo_path' => $photoPath,
                    'phone' => $phone,
                    'created_at' => $createdAt,
                    'modified' => '',
                    'is_active' => 0,
                    'role_id' => $roles,
                    'deleted_at' => '',
                    'last_login' => ''
                ];

                $insertdata = $this->auth->registerUser($data);

                if ($insertdata) {

                    $this->email->initialize($emailConfig);
                    $this->email->from('indradullanov1@gmail.com', 'User Activation');
                    $this->email->to($email);
                    $this->email->subject('Account Verification');
                    $this->email->message('Click this link to verify you account : <a href="' . base_url() . 'auth/verify?email=' . $email . '&token=' . urlencode($token) . '">Activate</a>');
                    if ($this->email->send()) {
                        $this->response([
                            'status' => true,
                            'message' => 'Success registered, please check email for verification your account'
                        ], 200);
                    } else {
                        $this->response([
                            'status' => false,
                            'message' => 'Failed to register, please try again'
                        ], 404);
                    }
                } else {
                    $this->response([
                        'status' => false,
                        'message' => 'Failed to register, please try again'
                    ], 404);
                }
            }
        } else {
            $this->response([
                'status' => false,
                'message' => 'Something wrong'
            ], 404);
        }
    }
}
