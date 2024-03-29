<?php
defined('BASEPATH') or exit('No direct script access allowed');
header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");

class Authentication_controller extends CI_Controller
{

	public function __construct()
	{
		parent::__construct(); 
		$this->load->database();
		$this->load->model("User");
		$this->load->model("Post");
		$this->load->model("Genre");
	}

	public function registerUser() // Controller function to register a New user
	{

		$imageURL = $this->Post->checkProfileImage($this->input->post("imgURL"));
		if (!$imageURL) {
			// Discussed with Simon
			echo "<script>
			alert('Invalid Image URL given for profile picture');
			window.location.href=\"".$this->config->item('application_url')."\"
				</script>";
				return;
		}

		$result = $this->User->createUser(
			$this->input->post("username"),
			$this->input->post("password"),
			$this->input->post("fname"),
			$this->input->post("lname"),
			$this->input->post("dob"),
			$this->input->post("email"),
			$imageURL,
			$this->input->post("genres")
		);

		if ($result === 1062) {  //Check for duplicated username
			echo "<script>
			alert('User already exists');
			window.location.href=\"".$this->config->item('application_url')."\"
				</script>";
		} else {
			echo "<script>
			alert('User created successfully');
			window.location.href=\"".$this->config->item('application_url')."\"
				</script>";
		}
	}

	public function signInUser()  // Controller function to login a user
	{
		$userData = $this->User->validateSignIn($this->input->post("username"), $this->input->post("password"));
		if (isset($userData)) {

			$newdata = array(
				'username'  => $userData->username,
				'firstname' => $userData->firstname,
				'lastname'  => $userData->lastname,
				'imageURL'  => $userData->imageURL,
				'email'   => $userData->email,
				'dob' => $userData->dob,
				'logged_in' => TRUE
			);

			$this->session->set_userdata('userdata', $newdata);
			redirect("welcome/");
		} else {
			//Discussed with Simon
			echo "<script>
			alert('Username or Password is wrong!!! Try again');
			window.location.href=\"".$this->config->item('application_url')."\"
				</script>";
		}
	}

	public function logoutuser()  // Controller function to logout user
	{
		$this->session->sess_destroy();
		redirect($this->config->item('entry_point'));
	}
}
