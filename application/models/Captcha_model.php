<?php

/*
 * Phạm Tiến Thành
 * tienthanh.dqc@gmail.com
 * +841679227582
 */

/**
 * Captcha_model
 * 
 * Description...
 * 
 * @package Captcha
 * @author PhamThanh <tienthanh.dqc@gmail.com>
 * @version 0.0.0
 */
defined('BASEPATH') OR exit('No direct script access allowed');

class Captcha_model extends CI_Model {

    var $captcha_url;
    var $captcha_path;

    public function __construct() {
        parent::__construct();

        $this->load->library('session');

        $this->captcha_url = base_url('cache/captcha/');
        $this->captcha_path = FCPATH . 'cache/captcha/';
    }

    /**
     * Tạo mã xác thực mới
     * 
     * @return array
     */
    public function create() {
        $this->load->database();
        $this->load->helper('captcha');

        $vals = array(
            'img_path' => $this->captcha_path,
            'img_url' => $this->captcha_url,
            'word' => rand(0, 999999)
        );

        if (!is_dir($vals['img_path']))
            mkdir($vals['img_path'], 0777, TRUE);

        $cap = create_captcha($vals);
        $data = array(
            'captcha_time' => $cap['time'],
            'ip_address' => $this->input->ip_address(),
            'word' => $cap['word']
        );
        $query = $this->db->insert_string('ci_captcha', $data);
        $this->db->query($query);
        $cap['img_url'] = $vals['img_url'] . '/' . $cap['filename'];

        $this->session->set_userdata('captcha_string', $cap['word']);

        return $cap;
    }

    /**
     * Kiểm tra mã xác thực
     * 
     * @param string $string Chuỗi ký tự captcha người dùng nhập vào
     * @param bool $clear Xóa phiên mã xác minh
     * @return boolean Trả về TRUE nếu đúng và ngược lại
     */
    public function check($string = '', $clear = TRUE) {
        if (ENVIRONMENT == 'development')
            return TRUE;

        $return = FALSE;
        //if (strtolower($string) == strtolower($this->session->userdata('captcha_string')))
        if ($string == $this->session->userdata('captcha_string'))
            $return = TRUE;

        // Dọn bộ tạm
        if ($clear)
            $this->session->unset_userdata('captcha_string');

        return $return;
    }

}

/* End of file Captcha_model.php */
/* Location: ./application/models/Captcha_model.php */