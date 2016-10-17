<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Api extends CI_Controller {
	
	public function __construct() {
        // Call the CI_Model constructor
        parent::__construct();
		$this->load->model('video_model');
		$this->load->model('user_model');
		$this->load->model('payment_model');
		$this->load->library('form_validation');
		$this->load->helper('url');

		$action = $this->uri->segment(2);
		
		$paths = array('featured', 'register', 'sign_in', 'categorized_videos', 'social_signin', 'get_comments', 'get_applause', 'get_video_details', 'total_views', 'add_view', 'all_videos');
		
		if (!in_array($action, $paths)) {
			$validated = FALSE;
			if ($token = $this->input->get_request_header('Token', TRUE)) {
				$userid = $this->input->post('userid');
				if ($this->user_model->user_verify(array('uid'=> $userid, 'auth_token' => $token))) {
					$validated =TRUE;
				}
			}
			if (!$validated) {
				echo json_encode(array('success'=> false, 'message' => 'Unauthorized access'));die;
			}
		}
    }
	
	public function featured() {
		$videos = $this->video_model->get_featured();
		$my_videos = array();
		foreach($videos as $video) {
			$video['video'] = base_url('videos/' . $video['video']);
			$video['image_name'] = base_url('images/' . $video['image_name']);
			$my_videos[count($my_videos)] = $video;
		}
		
		echo json_encode(array("success" => (count($videos) > 0), "videos" => $my_videos));
	}
	
	public function all_videos() {
		$videos = $this->video_model->get_videos();
		$my_videos = array();
		$draw = $this->input->post('draw');
		$start = $this->input->post('start');
		$length = $this->input->post('length');

		$categories = $this->video_model->getcategories();

		for($i = $start; $i < ($start + $length); $i++) {
			if (isset($videos[$i])) {
				$video = $videos[$i];
				unset($video['video']);// = base_url('videos/' . $video['video']);
				unset($video['image_name']);// = base_url('images/' . $video['image_name']);
				$cat_id = $video['category'];
				$date = new DateTime($video['created']);
				$video['created'] = $date->format('d-m-Y H:i a');
				foreach($categories as $cat) {
					if ($cat_id == $cat['id']) {
						$video['category'] = $cat['category_name'];
						$video['category_id'] = $cat_id;
					}
				}
				$my_videos[count($my_videos)] = $video;
			}
		}
		echo json_encode(array("draw" => $draw,"recordsTotal" => count($videos), "recordsFiltered" => count($my_videos), "data" => $my_videos));
	}
	
	public function categorized_videos() {
		$videos = $this->video_model->get_videos();
		$categories = $this->video_model->getcategories();
		$cat_vid = array();
		foreach($categories as $cat) {
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
		
		echo json_encode(array("success" => (count($videos) > 0), "categorized_videos" => $cat_vid));
	}
	
	public function get_comments($video_id) {
		if ( $comments = $this->video_model->getCommentsForVideo($video_id)){
			echo json_encode(array("success" => (count($comments) > 0), "comments" => $comments));
		}
		else {
			echo json_encode(array("success" => false, "message" => "No comments yet"));	
		}
	}
	
	public function get_video_details($video_id, $userid = FALSE) {
		$response = array();
		if ($comments = $this->video_model->getCommentsForVideo($video_id)) {
			$response['comments'] = $comments;
		}
		else {
			$response['comments'] = [];	
		}
		$response['reviews'] = $this->video_model->getApplauses(array('v_id'=>$video_id));
		
		if ($userid) {
			$response['my_review'] = $this->video_model->getUsersApplauses(array('v_id'=>$video_id, 'userid' => $userid));
		}
		
		$response['views'] = $this->video_model->totalViews(array('video_id'=>$video_id));
		echo json_encode($response);
	}
	
	public function get_applause($video_id) {
		echo json_encode($this->video_model->getApplauses(array('v_id'=>$video_id)));
	}
	
	public function total_views($video_id) {
		echo json_encode(array('views' => $this->video_model->totalViews(array('video_id'=>$video_id))));
	}
	
	public function add_view() {
		$this->form_validation->set_rules('videoid', 'Video Identification', 'required');
		if ($this->form_validation->run() == FALSE) {
			echo json_encode(array("success"=> false, "message" => "Send Video Id"));
		}
		else {
			$uid = 0;
			$post = $this->input->post();
			if (isset($post['userid'])) {
				$uid = $this->input->post('userid');
			}
			if ($this->video_model->addView(array('userid' => $uid, 'video_id' => $this->input->post('videoid')))) {
				echo json_encode(array("success"=> true, "message" => "View Added"));
			}
			else {
				echo json_encode(array("success"=> false, "message" => "View Not Added"));
			}
		}
	}
	
	public function applause() {
		$this->form_validation->set_rules('videoid', 'Video Identification', 'required');
		$this->form_validation->set_rules('userid', 'User Id', 'required');
		$this->form_validation->set_rules('applause', 'Applause', 'required');
		if ($this->form_validation->run() == FALSE) {
			echo json_encode(array("success"=> false, "message" => "Send all params"));
		}
		else {
			$this->video_model->applause(($this->input->post('applause') == "true"), array('v_id'=> $this->input->post('videoid'), 'userid' => $this->input->post('userid')));
		}	
	}
	
	public function add_comment() {
		$this->form_validation->set_rules('videoid', 'Video Identification', 'required');
		$this->form_validation->set_rules('userid', 'User Id', 'required');
		$this->form_validation->set_rules('comment', 'Users Comment', 'required');
		
		if ($this->form_validation->run() == FALSE) {
			echo json_encode(array("success"=> false, "message" => "Send all details"));
		}
		else {
			if ($commentid = $this->video_model->addcomments(array('v_id'=> $this->input->post('videoid'), 'userid' => $this->input->post('userid'), 'comment' => $this->input->post('comment')))) {
				echo json_encode(array("success"=> true, "message" => "Comment Added", "comment_id" => $commentid));
			}
			else {
				echo json_encode(array("success"=> false, "message" => "Failed to add comment"));
			}
		}
	}
	
	public function create_payment() {
		$this->form_validation->set_rules('amount', 'Payment Amount', 'required');
		$this->form_validation->set_rules('userid', 'User Id', 'required');
		
		if ($this->form_validation->run() == FALSE) {
			echo json_encode(array("success"=> false, "message" => "Send Payment Amount details"));
		}
		else {
			if ($pay_id = $this->payment_model->paymentCreate($this->input->post())) {
				echo json_encode(array("success"=> true, "message" => "Payment Request Created", "pay_id" => $pay_id));
			}
			else {
				echo json_encode(array("success"=> false, "message" => "Payment Creation failed"));
			}
		}
	}
	
	public function update_payment() {
		$this->form_validation->set_rules('pay_status', 'Payment Reference', 'required');
		$this->form_validation->set_rules('ref_id', 'User Id', 'required');
		$this->form_validation->set_rules('pay_id', 'Paymnt Id', 'required');
		$pst = $this->input->post();
		if ($this->form_validation->run() == FALSE) {
			echo json_encode(array("success"=> false, "message" => "Send Payment Amount details"));
		}
		else {
			if ($pay_id = $this->payment_model->updatePayment($pst['pay_id'], $pst['success'], $pst['refid'])) {
				echo json_encode(array("success"=> true, "message" => "Payment Updated", "pay_id" -> $pay_id));
			}
			else {
				echo json_encode(array("success"=> false, "message" => "Payment Updation failed"));
			}
		}
	}
		
	public function register() {
		$this->form_validation->set_rules('firstname', 'Firstname', 'required');
		$this->form_validation->set_rules('lastname', 'Lastname', 'required');
		$this->form_validation->set_rules('email', 'E-mail Address', 'required|valid_email');
		$this->form_validation->set_rules('password', 'Password', 'required');

		if ($this->form_validation->run() == FALSE) {
			echo json_encode(array("success"=> false, "message" => "Send all details"));
		}
		else {
			if ($userdetails = $this->user_model->user_sign_up($this->input->post())) {
				echo json_encode(array("success"=> true, "message" => "Sign Up successfull", "userdetail" => $userdetails));
			}
			else {
				echo json_encode(array("success"=> false, "message" => "Sign Up failed"));
			}
		}
	}
	
	public function sign_in() {
		$this->form_validation->set_rules('email', 'E-mail Address', 'required');
		$this->form_validation->set_rules('password', 'Password', 'required');

		if ($this->form_validation->run() == FALSE) {
			echo json_encode(array("success"=> false, "message" => "Please fill both email and password"));
		}
		else {
			$this->load->model('user_model');
			if ($userdetails = $this->user_model->user_sign_in($this->input->post())) {
				echo json_encode(array("success"=> true, "message" => "Sign In successfull", "userdetail" => $userdetails));
			}
			else {
				echo json_encode(array("success"=> false, "message" => "Sign In failed.\nInvalid Email or Password"));
			}
		}

	}
	
	public function social_signin() {
		$this->form_validation->set_rules('email', 'E-mail Address', 'required|valid_email');
		$this->form_validation->set_rules('firstname', 'Firstname', 'required');
		$this->form_validation->set_rules('lastname', 'Lastname', 'required');
		$this->form_validation->set_rules('ident', 'Token', 'required');
		$this->form_validation->set_rules('signintype', 'Social Type', 'required');
		
		if ($this->form_validation->run() == FALSE) {
			echo json_encode(array("success"=> false, "message" => "Please fill all details"));
		}
		else {
			if ($userdetails = $this->user_model->social_sign_in($this->input->post())) {
				echo json_encode(array("success"=> true, "message" => "Sign In successfull", "userdetail" => $userdetails));
			}
			else {
				echo json_encode(array("success"=> false, "message" => "Sign In failed.\nEmail already exists"));
			}
		}
	}
}
