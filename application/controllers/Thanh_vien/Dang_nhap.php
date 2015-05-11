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
 * @property Openid_model $openid_model
 * @property Usermodel $usermodel
 */
class Dang_nhap extends CI_Controller {

    private $data = array();

    public function __construct() {
        parent::__construct();
    }

    public function index() {
        $this->load->model('usermodel');

        if ($this->usermodel->is_user_loggedin())
        {
            $this->output->set_header('Location: ' . $this->config->site_url('thanh_vien/ho_so_ca_nhan'));
            return;
        }

        $this->load->helper(array('form'));
        $this->load->library('form_validation');

        $this->form_validation->set_rules('user_name', 'Bí Danh', 'required');
        $this->form_validation->set_rules('user_password', 'Mật khẩu', 'required');

        if ($this->input->post('btn_submit'))
        {
            if ($this->form_validation->run() == TRUE)
            {
                $user_name = $this->input->post('user_name');
                $user_password = $this->input->post('user_password');
                $user_rememberme = !empty($this->input->post('user_rememberme')) ? TRUE : FALSE;

                $status = $this->usermodel->login_with_postdata($user_name, $user_password, $user_rememberme);

                if ($status == Usermodel::USER_CHECK_USERNAME_FAILED)
                {
                    $this->data['error'][] = 'Tên tài khoản không được hệ thống thông qua!';
                }
                elseif ($status == Usermodel::USER_EMAIL_FORMAT_ILLEGAL)
                {
                    $this->data['error'][] = 'Email không được hệ thống thông qua!';
                }
                elseif ($status == Usermodel::USER_LOGIN_FAILED)
                {
                    $this->data['error'][] = 'Sai tài khoản hoặc mật khẩu!';
                }
                elseif ($status == Usermodel::USER_PASSWORD_WRONG)
                {
                    $this->data['error'][] = 'Sai tài khoản hoặc mật khẩu!';
                }
                elseif ($status == Usermodel::USER_PASSWORD_WRONG_3_TIMES)
                {
                    $this->data['error'][] = 'Bạn đã đăng nhập sai 3 lần, hãy thử lại sau 30 giây!';
                }
                elseif ($status == Usermodel::USER_ACCOUNT_NOT_ACTIVATED)
                {
                    $this->data['error'][] = 'Tài khoản chưa được kích hoạt, hãy kiểm tra hộp thư điện tử của bạn!';
                }
                elseif ($status == Usermodel::USER_LOGIN_SUCCEED)
                {
                    $this->output->set_header('Location: ' . $this->config->site_url('thanh_vien/ho_so_ca_nhan'));
                    return;
                }
            }
        }

        $this->load->view('thanh_vien/dang_nhap', $this->data);
    }

}

/* End of file Dang_nhap.php */
/* Location: Dang_nhap.php */