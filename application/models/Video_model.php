<?php

class Video_model extends CI_Model {
    public function __construct() {
        // Call the CI_Model constructor
        parent::__construct();
        $this->load->database();
    }
    
    public function store_video($details) {
        $this->db->insert('videos', array('video_name' => $details['videoname'], 'video_descript' => $details['videodesc'], 'video' => $details['file_name'], 'fullpath' => $details['full_path'], 'reviewed' => true, 'image_name' => $details["raw_name"] . ".png" , "cost" => ($details["cost"] == "free")?0:10, "video_category" => $details["category"]));
        return $this->db->insert_id();
    }
    
    public function store_video_in_review($details) {
        $this->db->insert('videos', array('video_name' => $details['videoname'], 'video_descript' => $details['videodesc'], 'video' => $details['file_name'], 'fullpath' => $details['full_path'], 'image_name' => $details["raw_name"] . ".png" , "cost" => ($details["cost"] == "free")?0:10, "video_category" => $details["category"]));
        return $this->db->insert_id();
    }
    
    public function get_videos() {
        $query = $this->db->query('SELECT  `id`,`video_name`, `video_descript` as description, `image_name`, `video`, `cost`, `video_category` as category, `video_time` as created FROM `videos` WHERE `deleted`=FALSE');
        return $query->result_array();
    }
    
    public function get_featured() {
        $query = $this->db->query('SELECT  `id`,`video_name`, `video_descript` as description, `image_name`, `video`, `cost`, `video_category` as category, `video_time` as created FROM `videos` WHERE `featured`=TRUE AND `deleted`=FALSE');
        return $query->result_array();
    }
    
    public function getcategories(){
        $result = $this->db->get('category');
        return $result->result_array();
    }
    
    public function addcomments($comments){
        $this->db->insert('comments', $comments);
        return $this->db->insert_id();
    }
    
    public function addView($details) {
        $this->db->select('*');
        $results = $this->db->get_where('video_stats', $details);
        if ($results->num_rows() > 0) {
            $this->db->select('SUM(views) as total_views');
            $resul = $this->db->get_where('video_stats', $details);
            $rst = $resul->result_array()[0];
            $count = $rst['total_views'];
            return $this->db->update('video_stats', array('views' => $count + 1), $details);
        }
        else {
            $details['views'] = 1;
            $this->db->insert('video_stats', $details);
            $ins_id = $this->db->insert_id();
            return isset($ins_id);
        }
    }
    
    public function totalViews($details) {
        $this->db->select('*');
        $res = $this->db->get_where('video_stats', $details);
        if ($res->num_rows() > 0) {
            $this->db->select('SUM(views) as total_views');
            $results = $this->db->get_where('video_stats', $details);
            $rst = $results->result_array()[0];
            return $rst['total_views'];
        }
        return 0;
    }
    
    public function updateVideo($vid, $updates) {
        return $this->db->update('videos', array('video_name' => $updates['videoname'], 'video_descript' => $updates['videodesc'], "cost" => ($updates["cost"] == "free")?0:10, "video_category" => $updates["category"]), array('id' => $vid));
    }
    
    public function deleteVideo($vid) {
        return $this->db->update('videos', array('deleted' => TRUE), array('id' => $vid));
    }
    
    public function getCommentsForVideo($v_id) {
        
        $this->db->select('comments.*, CONCAT(firstname, " ", lastname) as username');
        $this->db->where("v_id", $v_id);
        $this->db->from('comments');
        $this->db->join('user', 'user.uid = comments.userid');
        
        $results = $this->db->get();

        if ($results->num_rows() > 0) {
            return $results->result_array();
        }
        return FALSE;
    }
    
    public function applause($isApplaused, $details) {
        $results = $this->db->get_where('like_tally', $details);
        if ($results->num_rows() > 0) {
            //`v_id``applause``userid`
            return $this->db->update('like_tally', array('applause' => $isApplaused), $details);
        }
        else {
            $details['applause'] = $isApplaused;
            $this->db->insert('like_tally', $details);
            $id = $this->db->insert_id();
            return isset($id);
        }
    }
    
    public function getApplauses($details) {
        $details['applause'] = TRUE;
        $applauses = $this->db->get_where('like_tally', $details);
        
        $details['applause'] = FALSE;
        $dislikes = $this->db->get_where('like_tally', $details);
        
        return array("applauses" => $applauses->num_rows(), "dislikes" => $dislikes->num_rows());
    }
    
    public function getUsersApplauses($details) {
        $details['applause'] = TRUE;
        $applauses = $this->db->get_where('like_tally', $details);
        
        $details['applause'] = FALSE;
        $dislikes = $this->db->get_where('like_tally', $details);
        
        return array("applaused" => ($applauses->num_rows() > 0), "disliked" => ($dislikes->num_rows() > 0));
    }
    
    public function getCommentsCountForVideo($v_id) {
        $this->db->select('COUNT(*) as commentCount');
        $this->db->where("v_id", $v_id);
        $this->db->from('comments');
        $comm = $this->db->get();
        return $comm->result_array()[0]['commentCount'];
    }
}

?>