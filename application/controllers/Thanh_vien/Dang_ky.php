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
 * @property Usermodel $usermodel
 * @property recaptcha_model $recaptcha_model
 */
class Dang_ky extends CI_Controller {

    protected $data = array();

    public function __construct() {
        parent::__construct();

        // Nạp các thư viện cần thiết cho cả Control này
        $this->load->model('usermodel');
        $this->load->library('parser');
    }

    /**
     * Control chính
     */
    public function index() {
        // Nạp thư viện
        $this->load->helper(array('form', 'url'));
        $this->load->library('form_validation');

        $this->load->model('recaptcha_model');

        $this->form_validation->set_rules('user_name', 'Bí Danh', 'required');
        $this->form_validation->set_rules('user_password_new', 'Mật khẩu', 'required');
        $this->form_validation->set_rules('user_password_repeat', 'Xác nhận mật khẩu', 'required');
        $this->form_validation->set_rules('user_email', 'Email', 'required');

        // Nếu người dùng đã gửi form đăng ký lên hệ thống
        if ($this->input->post('btn_submit'))
        {
            /**
             * Bắt đầu quy trình kiểm tra đăng ký
             */
            // Bước 1: Kiểm tra Captcha, nếu không đúng yêu cầu nhập lại
            if (!$this->recaptcha_model->check($this->input->post('captcha')))
            {
                $this->data['error'][] = 'Bạn chưa xác thực mình không phải là robot!';
            }

            // Bước 2: Kiểm tra tính hợp lệ của dữ liệu người dùng đã nhập
            elseif ($this->form_validation->run() == TRUE)
            {
                // Lấy các thông tin cần thiết
                $user_name = $this->input->post('user_name');
                $user_email = $this->input->post('user_email');
                $user_password = $this->input->post('user_password_new');

                // Bước 3: Thực hiện yêu cầu tạo tài khoản
                $status = $this->usermodel->new_user($user_name, $user_email, $user_password);

                if ($status == Usermodel::USER_REGISTER_SUCCEED)
                {
                    $this->data['user_email'] = $user_email;
                    $this->parser->parse('thanh_vien/dang_ky_thanh_cong', $this->data);
                    return;
                }
                elseif ($status == Usermodel::USER_EMAIL_FORMAT_ILLEGAL)
                {
                    $this->data['error'][] = 'Email không được hệ thống thông qua!';
                }
                elseif ($status == Usermodel::USER_CHECK_USERNAME_FAILED)
                {
                    $this->data['error'][] = 'Bí danh không được hệ thống thông qua!';
                }
                elseif ($status == Usermodel::USER_USERNAME_EXISTS)
                {
                    $this->data['error'][] = 'Tên người dùng đã tồn tại!';
                }
                elseif ($status == Usermodel::USER_EMAIL_EXISTS)
                {
                    $this->data['error'][] = 'Địa chỉ Email đã có người dùng!';
                }
                elseif ($status == Usermodel::USER_REGISTER_FAILED)
                {
                    $this->data['error'][] = 'Đăng ký thất bại!!!';
                }
                else
                {
                    $this->data['error'][] = 'Có lỗi đã xảy ra!!! Mã lỗi ' . $status;
                }
            }
        }

        $this->data['recaptcha_script'] = $this->recaptcha_model->javascript_url();
        $this->data['recaptcha_body'] = $this->recaptcha_model->create();

        $this->parser->parse('thanh_vien/dang_ky', $this->data);
    }

    /**
     * Xác thực tài khoản đã đăng ký
     * 
     * @param int $user_id
     * @param string $user_activation_hash
     */
    public function Xac_thuc_tai_khoan($user_id = '', $user_activation_hash = '') {
        // Nếu là liên kết kích hoạt không hợp lệ thì báo lỗi 404 và dừng chương trình
        if (!is_numeric($user_id) || empty($user_activation_hash))
            show_404();

        if ($this->usermodel->verify_new_user($user_id, $user_activation_hash))
            $this->load->view('thanh_vien/dang_ky_kich_hoat_thanh_cong');
        else
            $this->load->view('thanh_vien/dang_ky_kich_hoat_that_bai');
    }

}

/* End of file Dang_ky.php */
/* Location: ./application/controllers/Thanh_vien/Dang_ky.php */