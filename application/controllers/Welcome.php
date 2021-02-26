<?php

defined('BASEPATH') or exit('No direct script access allowed');


use chriskacerguis\RestServer\RestController;

class Welcome extends RestController
{
	public function __construct()
	{
		parent::__construct();

		// header('Access-Control-Allow-Origin: *');
		// header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
		$this->load->model('Auth_model', 'auth');
	}

	public function index_get()
	{
		$this->response([
			'status' => true,
			'data' => 'ok'
		], RestController::HTTP_OK);
	}

	public function users_get()
	{
		// Users from a data store e.g. database
		$users = [
			['id' => 0, 'name' => 'John', 'email' => 'john@example.com'],
			['id' => 1, 'name' => 'Jim', 'email' => 'jim@example.com'],
		];

		$id = $this->get('id');

		if ($id === null) {
			// Check if the users data store contains users
			if ($users) {
				// Set the response and exit
				$this->response($users, 200);
			} else {
				// Set the response and exit
				$this->response([
					'status' => false,
					'message' => 'No users were found'
				], 404);
			}
		} else {
			if (array_key_exists($id, $users)) {
				$this->response($users[$id], 200);
			} else {
				$this->response([
					'status' => false,
					'message' => 'No such user found'
				], 404);
			}
		}
	}

	public function fetch_get()
	{
		echo $this->response(array('test' => 'test'), 200);
	}

	public function login_post()
	{

		$email = $this->post('email');
		$password = $this->post('password');


		if (!empty($email) && !empty($password)) {
			$user_check = $this->user->getEmail($email);
			if ($user_check) {
				if (password_verify($password, $user_check['password'])) {
					$session = [
						'is_login' => 'true',
						'first_name' => $user_check['first_name'],
						'email' => $user_check['email'],
						'roles' => $user_check['roles']
					];
					$this->session->set_userdata($session);

					$this->response([
						'status' => true,
						'data' =>   $this->session->set_userdata($session)
					], 200);
				} else {
					$this->response([
						'code' => 404,
						'status' => false,
						'message' => 'Email or password wrong'
					], 404);
				}
			} else {
				$this->response([
					'code' => 404,
					'status' => false,
					'message' => 'Email or password wrong'
				], 404);
			}
		} else {
			$this->response([
				'status' => false,
				'message' => 'Email and password are required',
				'data' => $this->post('email')
			], 404);
		}
	}

	public function registration_post()
	{


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
