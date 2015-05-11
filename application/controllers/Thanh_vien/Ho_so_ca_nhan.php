<?php

/*
 * Phạm Tiến Thành
 * tienthanh.dqc@gmail.com
 * +841679227582
 */

defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * 
 * 
 * @author Phạm Tiến Thành <tienthanh.dqc@gmail.com>
 * @property UserModel $usermodel
 */
class Ho_so_ca_nhan extends CI_Controller {

    public function __construct() {
        parent::__construct();
    }

    public function index() {
        $this->load->model('usermodel');
        if (!$this->usermodel->is_user_loggedin())
        {
            $this->output->set_header('Location: ' . $this->config->site_url('thanh_vien/dang_nhap'));
            return;
        }

        $this->load->view('thanh_vien/ho_so_ca_nhan');
    }

}

/* End of file Ho_so_ca_nhan.php */
/* Location: Ho_so_ca_nhan.php */