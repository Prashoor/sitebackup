<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class User extends CI_Controller {
	private $error, $message;
	
	public function __construct() {
        parent::__construct();
		$this->load->library('session');
		$this->load->helper(array('form', 'url', 'html'));
		$this->load->library('form_validation');
		
		$action = $this->uri->segment(2);
		if (!$this->session->isuserloggedin && strlen($action) > 0) {
			redirect('/user', 'refresh');
		}
		else if($this->session->isloggedin && strlen($action) == 0){
			redirect('/user/panel', 'refresh');
		}
	}
	
	public function index() {

		$this->form_validation->set_rules('email', 'E-mail Address', 'required');
		$this->form_validation->set_rules('password', 'Password', 'required');

		if ($this->form_validation->run() == FALSE) {
			$this->load->view('user_login');
		}
		else {
			$this->load->model('user_model');
			if ($user = $this->user_model->user_up_in($this->input->post())) {
				$user['isuserloggedin'] = TRUE;
				$this->session->set_userdata($user);
	    		redirect('/user/panel' , 'refresh');
			}
			else {
				redirect('/user', 'refresh');
			}
		}
	}
	
	public function panel() {
		$this->load->model('video_model');		
		$videos = $this->video_model->get_videos();
		$categories = $this->video_model->getcategories();
		$cat_vid = array();
		foreach ($categories as $cat) {
			$my_videos = array();
			foreach($videos as $video) {
				if ($video['category'] == $cat['id']) {
					$video['video'] = base_url('videos/' . $video['video']);
					$video['image_name'] = base_url('images/' . $video['image_name']);
					$my_videos[count($my_videos)] = $video;
				}
			}
			$cat_vid[$cat['category_name']] = $my_videos;
		}
		$this->load->view('user_upload', array("allvideos" => $cat_vid, "categories" => $categories));
	}
	
	public function logout() {
		$this->session->unset_userdata(array('id', 'username', 'email', 'isloggedin'));
		redirect('/user', 'refresh');
	}
}
