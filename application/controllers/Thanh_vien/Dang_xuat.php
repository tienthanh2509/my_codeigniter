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
class Dang_xuat extends CI_Controller {

    public function __construct() {
        parent::__construct();
    }

    public function index() {
        $this->load->model('usermodel');
        $this->usermodel->do_logout();
    }

}

/* End of file Dang_xuat.php */
/* Location: Dang_xuat.php */