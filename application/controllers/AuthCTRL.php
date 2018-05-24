<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class AuthCTRL extends CI_Controller {
	
	function __construct(){
		parent::__construct();		
	}
	
	public function home(){
		$data['title'] = "Home";;
		$data['content'] = "Public/home.php";
		$this->load->view('Template', $data);
	}

	public function Access(){
		$sess = $this->session->userdata('loggedIn');
		
		$where = array(
			'id_user' => $sess['id_user']
		);
				
		$show = $this->M__db->cek('users','id_user, access',$where)->row_array();
		if($show['access']=='admin'){
			$accessData = 'user';
		}else{
			$accessData = 'admin';
		}
		$data = array(
			'access'	=> $accessData
		);
		$this->M__db->update('users',$where,$data);
		$this->session->unset_userdata('loggedIn');
		$this->session->sess_destroy();
		$this->session->set_flashdata('success','You have been successfully change access');
		redirect(base_url().'Login');
	}

	public function login(){
		if($this->session->userdata('loggedIn')){
			redirect(base_url().'Home');
		}else{
			$data['title'] = "Login";
			$this->load->view('Public/loginView.php', $data);
		}
	}
	
	public function prosesLogin(){
		$this->form_validation->set_rules('username','Username','trim|required');
		$this->form_validation->set_rules('password','Password ','trim|required');
		if($this->form_validation->run()==FALSE){
			$this->session->set_flashdata('error',validation_errors());
			redirect(base_url().'Login');
		}else{
			$where = array(
				'username' => paramEncrypt($this->input->post('username')),
				'password' => paramEncrypt($this->input->post('password')),
			);
			$check = $this->M__db->cek('users','id_user, fullname, access',$where);
			if($check->num_rows()==0){
				$this->session->set_flashdata('error','Username or password is incorrect!');
				redirect(base_url().'Login');
			}else{
				$rowUser = $check->row_array();
				$sess_array = array(
					'id_user' => $rowUser['id_user'],
					'access' => $rowUser['access']
				);
				$this->session->set_userdata('loggedIn',$sess_array);
				$this->session->set_flashdata('messageUser',$rowUser['fullname']);
				redirect(base_url().'Home');
					
			}
		}
		
	}

	public function register(){
		$data['title'] = "Register";
		$this->load->view('Public/registerView.php', $data);
	}
	
	public function prosesRegister(){
		$this->form_validation->set_rules('fullname','Full Name','trim|required');
		$this->form_validation->set_rules('username','Username','trim|required');
		$this->form_validation->set_rules('password','Password ','trim|required');

		if($this->form_validation->run()==FALSE){
			$this->session->set_flashdata('error',validation_errors());
		}else{
			$pass = $this->input->post('password');
			$confPass = $this->input->post('confirmPassword');
			if($pass !== $confPass){
				$this->session->set_flashdata('error','Confirm password is not the same!');
			}else{
				$where = array(
					'username' => paramEncrypt($this->input->post('username'))
				);
				$check = $this->M__db->cek('users','id_user',$where);
				if($check->num_rows()>0){
					$this->session->set_flashdata('error','Username already exists!');
				}else{
					$data = array(
						'fullname' 	=> $this->input->post('fullname'),
						'username' 	=> paramEncrypt($this->input->post('username')),
						'password' 	=> paramEncrypt($this->input->post('password')),
						'access'	=> 'user'
					);
					$this->db->trans_begin();
					$this->M__db->simpan('users',$data);
					if ($this->db->trans_status() === FALSE) {
						$this->db->trans_rollback();
					}else{
						$this->db->trans_commit();
						$this->session->set_flashdata('success','You have been successfully registered and <a href="'.base_url().'Login">logged in here</a>.');
					}
				}
			}
		}
		redirect(base_url().'Register');
	}

	public function deleteSession(){
		$this->session->unset_userdata('loggedIn');
		$this->session->sess_destroy();
		redirect (base_url().'Login');
	}
}
