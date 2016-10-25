<?php

class User_model extends CI_Model {
    public function __construct() {
        // Call the CI_Model constructor
        parent::__construct();
        $this->load->database();
    }
    
    public function signin($params) {
        $query = $this->db->query("SELECT `id`, `username`, `email` FROM `admin` WHERE `password`='" . bin2hex($params['passwd']) . "' AND `username`='" . $params['uname'] ."'" );
        if ($query->num_rows() > 0) {
            return $query->result_array()[0];
        }
        return FALSE;
    }

    public function user_up_in($userdetails) {
        $userdetails["password"] = bin2hex($userdetails["password"]);
        $userdetails["usertype"] = 1;
        $this->db->select('`id`, CONCAT(firstname, " ", lastname) as username, `email`');
        $query = $this->db->get_where('user', $userdetails);
        if ($query->num_rows() > 0) {
            return $query->result_array()[0];
        }
        return FALSE;
    }
    
    public function user_sign_up($userdetails) {
        $userdetails["auth_token"] = bin2hex(openssl_random_pseudo_bytes(16));
        $userdetails["password"] = bin2hex($userdetails["password"]);
        $this->db->insert('user', $userdetails);
        if ($this->db->affected_rows() > 0) {
            $uid = $this->db->insert_id();
            $userdetails['uid'] = $uid;
            return $userdetails;
        }
        return FALSE;
    }
    
    public function user_sign_in($userdetails) {
        $userdetails["password"] = bin2hex($userdetails["password"]);
        $query = $this->db->get_where('user', $userdetails);
        if ($query->num_rows() > 0) {
            $token = bin2hex(openssl_random_pseudo_bytes(16));
            $userdetails = $query->result_array()[0];
            if ($this->db->update('user', array('auth_token' => $token), array('uid' => $userdetails['uid']))) {
                $userdetails['auth_token'] = $token;
                return $userdetails;
            }
        }
        return FALSE;
    }
    
    
    public function social_sign_in($userdetails) {
        
        $signtype = $userdetails['signintype'];       
        $token = bin2hex(openssl_random_pseudo_bytes(16));

        $query = $this->db->get_where('user', array($signtype .'_id' => $userdetails['ident']));
        if ($query->num_rows() > 0) {
            $userdetails= $query->result_array()[0];
            if ($this->db->update('user', array('auth_token' => $token), array('uid' => $userdetails['uid']))) {
                $userdetails['auth_token'] = $token;
                return $userdetails;
            }
        }
        else {
            unset($userdetails['signintype']);
            $userdetails[$signtype .'_id'] = $userdetails['ident'];
            unset($userdetails['ident']);
            $userdetails["auth_token"] = $token;
            $this->db->insert('user', $userdetails);
            if ($this->db->affected_rows() > 0) {
                $uid = $this->db->insert_id();
                $userdetails['uid'] = $uid;
                return $userdetails;
            }
        }
        return FALSE;
    }
    
    public function user_verify($userdetails) {
        $query = $this->db->get_where('user', $userdetails);
        if ($query->num_rows() > 0) {
            return TRUE;
        }
        return FALSE;
    }
    
}