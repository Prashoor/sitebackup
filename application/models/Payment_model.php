<?php

class Payment_model extends CI_Model {
    public function __construct() {
        // Call the CI_Model constructor
        parent::__construct();
        $this->load->database();
    }
    
    public function paymentCreate($post){
        $post['pay_amount'] = $post['amount'];
        $post['pay_status'] = 'pending';
        unset($post['amount']);
        
        $this->db->insert('payment_table', $post);
        if ($this->db->affected_rows() > 0) {
            $uid = $this->db->insert_id();
            $userdetails['uid'] = $uid;
            return $userdetails;
        }
        return FALSE;
    }
    
    public function updatePayment($pay_id, $success, $refid) {
        $this->db->where('pay_id',$pay_id);
        $this->db->update('payment_table', array('pay_status'=> ($success?'confirmed':'failed'), 'ref_id' => $refid));
        return ($this->db->affected_rows() > 0);
    }
}