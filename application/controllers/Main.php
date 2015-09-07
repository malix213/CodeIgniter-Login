<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Main extends CI_Controller {
	public function index()
	{
		$this->login();
	}

	public function login() 
	{
		$this->load->view('login');
	}

	public function signup()
	{
		$this->load->view('signup');
	}

	public function members()
	{
		if ($this->session->userdata('is_logged_in')){
			$this->load->view('members');
		} else {
			redirect('main/restricted');
		}
	}

	public function restricted(){
		$this->load->view('restricted');
	}

	public function login_validation()
	{
		$this->load->library('form_validation');

		$this->form_validation->set_rules('email','Email','required|trim|xss_clean|valid_email|callback_validate_credentials');
		$this->form_validation->set_rules('password','Password','required|md5|trim');

		if($this->form_validation->run()){
			$data = array(
				'email' => $this->input->post('email'),
				'is_logged_in' => 1
			 );
			$this->session->set_userdata($data);
			redirect('main/members');
		} else {
			$this->load->view('login');
		}
	}

	public function signup_validation()
	{
		$this->load->library('form_validation');

		$this->form_validation->set_rules('email','Email','required|trim|valid_email|is_unique[users.email]');
		$this->form_validation->set_rules('password','Password','required|trim');
		$this->form_validation->set_rules('cpassword','Confirm Password','required|trim|matches[password]');

		$this->form_validation->set_message('is_unique','That email address already exists.');
		if($this->form_validation->run()){
			
			// generate random key
			$key = md5(uniqid());

			// create email 
			$this->load->library('email');

			$config['protocol']    = 'smtp';
            $config['smtp_host']    = 'ssl://smtp.gmail.com';
            $config['smtp_port']    = '465';
            $config['smtp_timeout'] = '7';
            $config['smtp_user']    = 'abdelmalek.lahmar@gmail.com';
            $config['smtp_pass']    = 'B@rcelone1';
            $config['charset']    = 'utf-8';
            $config['newline']    = "\r\n";
            $config['mailtype'] = 'html'; // or html
            $config['validation'] = TRUE; // bool whether to validate email or not  
			$this->email->initialize($config);

			$this->load->model('model_users');

			
			$this->email->from('abdelmalek.lahmar@gmail.com', 'Abdelamlek');
			$this->email->to($this->input->post('email'));
			$this->email->subject('Confirm your account.');

			$message = "<p>Thank you for signing up!";
			$message .= "<p><a href='".base_url()."main/register_user/$key' >Click here</a> to confirm your account</p>";

			$this->email->message($message);

			// send and email to the user
			if ($this->model_users->add_temp_user($key)){
				if($this->email->send()){
					echo "The email has been send!";
				} else echo "could not send the email.";
			} else echo "Problem adding to the database.";

			// add them the temp_users db

		} else {
			$this->load->view('signup');
		}


	}

	public function validate_credentials()
	{
		$this->load->model('model_users');

		if ($this->model_users->can_log_in()){
			return true;
		} else {
			$this->form_validation->set_message('validate_credentials','Incorrect username/password');
			return false;
		}
	}

	public function logout()
	{
		$this->session->sess_destroy();
		redirect('main/login');
	}

	public function register_user($key)
	{
		$this->load->model('model_users');
		if($this->model_users->is_key_valid($key)){
			if($this->model_users->add_user($key)){
				echo "success";
			} else echo "Failed to add user, please try again";
		} else echo "invalid key";

	}
}
