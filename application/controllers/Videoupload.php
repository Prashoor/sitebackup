<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Videoupload extends CI_Controller {
    
	private $upload_path = './videos/';
	
	private $error, $message;
	
	public function __construct() {
        // Call the CI_Model constructor
        parent::__construct();
		$this->load->library('session');
		$this->load->model('video_model');
    }
	
	public function upload_video() {
		if ($this->input->post('videoname')) {
			$datapost = $this->input->post();
			$config = array(
					'allowed_types' =>  'mp4|3gp|flv',
					'max_size'=>'0',
					'upload_path' => $this->upload_path
			);
			$is_file_error = FALSE;
			
			$success = false;
            //check if file was selected for upload
			if (!$_FILES) {
					$is_file_error = TRUE;
					$this->error = 'Select a video file.';
			}
			//if file was selected then proceed to upload
			if (!$is_file_error) {
				$this->load->library('upload', $config);
				if ($this->upload->do_upload('video')) {
					foreach($this->upload->data() as $key => $value) {
						$datapost[$key] = $value;
					}
					$resp = array();
					$cmd = "/usr/bin/ffmpeg -i \"" . $datapost['full_path'] . "\" -ss 05 -vframes 1 \"/var/www/html/images/" . $datapost['raw_name'] . ".png\"";
					shell_exec($cmd);
					if ($lastid = $this->video_model->store_video($datapost)) {
						$this->message = "Upload Success";
						$success = true;
					}
					else {
						$this->error = "Error Uploading Video";
						$success = false;
					}
				}
				else {
					$this->error = $this->upload->display_errors();
					$success = false;
				}
			}
		}
		else {
			$success = false;
			$this->message = "No details in post";
		}
		echo json_encode(array("success" => $success,"message" => $this->message, "error" => $this->error));
	}
	
	public function editvideo() {
		if ($this->session->isloggedin) {
			$this->load->library('form_validation');

			$this->form_validation->set_rules('videoid', 'Video Identification', 'required');
			$this->form_validation->set_rules('videoname', 'Video Name is Required', 'required');
			$this->form_validation->set_rules('videodesc', 'Video Description Needed', 'required');
			if ($this->form_validation->run() == FALSE) {
				echo json_encode(array("success"=> false, "message" => "Send all params"));
			}
			else {
				$postdata = $this->input->post();
				$vid = $postdata['videoid'];
				unset($postdata['videoid']);
				if($this->video_model->updateVideo($vid, $postdata)) {
					echo json_encode(array("success"=> true, "message" => "Video Updated Succesfully"));
				}
				else {
					echo json_encode(array("success"=> false, "message" => "Video Couldn't be updated"));
				}
			}
		}
		else {
			echo json_encode(array("success"=> false, "message" => "You are not logged in"));
		}
	}
	
	public function delete_video($vid) {
		if ($this->session->isloggedin && isset($vid)) {
			if ($this->video_model->deleteVideo($vid)) {
				echo json_encode(array("success"=> true, "message" => "Video Deleted Succesfully"));
			}
			else {
				echo json_encode(array("success"=> false, "message" => "Video Couldn't be deleted"));
			}
		}
		else {
			echo json_encode(array("success"=> false, "message" => "You are not logged in"));
		}
	}
	
}