<?php

/*
 * Phạm Tiến Thành
 * tienthanh.dqc@gmail.com
 * +841679227582
 */

/**
 * Usermodel
 * 
 * Description...
 * 
 * @package UserModel
 * @author PhamThanh <your@email.com>
 * @version 0.0.0
 */
defined('BASEPATH') OR exit('No direct script access allowed');

class Usermodel extends CI_Model {

    const USER_REGISTER_SUCCEED = 1;
    const USER_REGISTER_FAILED = 0;
    const USER_CHECK_USERNAME_FAILED = -1;
    const USER_USERNAME_BADWORD = -2;
    const USER_USERNAME_EXISTS = -3;
    const USER_EMAIL_FORMAT_ILLEGAL = -4;
    const USER_EMAIL_ACCESS_ILLEGAL = -5;
    const USER_EMAIL_EXISTS = -6;
    const USER_EMAIL_SEND_FAILED = -7;

    public function __construct() {
        parent::__construct();

        // Kết nối tới cơ sở dữ liệu
        $this->load->database();
    }

    function check_username($username) {
        $guestexp = '\xA1\xA1|\xAC\xA3|^Guest|^\xD3\xCE\xBF\xCD|\xB9\x43\xAB\xC8';
        $len = strlen($username);
        if ($len > 15 || $len < 3 || preg_match("/\s+|^c:\\con\\con|[%,\*\"\s\<\>\&]|$guestexp/is", $username)) {
            return FALSE;
        } else {
            return TRUE;
        }
    }

    function check_emailformat($email) {
        return strlen($email) > 6 && preg_match("/^[\w\-\.]+@[\w\-\.]+(\.\w+)+$/", $email);
    }

    /**
     * handles the entire registration process. checks all error possibilities, and creates a new user in the database if
     * everything is fine
     * 
     * Xử lý toàn bộ quá trình đăng ký. 
     * Kiểm tra tất cả các khả năng lỗi,
     * và tạo ra một người dùng mới trong cơ sở dữ liệu nếu mọi thứ đều tốt
     */
    public function new_user($user_name, $user_email, $user_password) {
        // Kiểm tra dữ liệu đầu vào
        $user_name = trim($user_name);
        $user_email = trim($user_email);
        $user_password = trim($user_password);

        if (!$this->check_username($user_name))
            return Usermodel::USER_CHECK_USERNAME_FAILED;
        if (!$this->check_emailformat($user_email))
            return Usermodel::USER_EMAIL_FORMAT_ILLEGAL;

        // check if username or email already exists
        $this->db->select('user_name, user_email');
        $this->db->where('user_name', $user_name);
        $this->db->or_where('user_email', $user_email);
        $query = $this->db->get('ci_users');

        // if username or/and email find in the database
        // TODO: this is really awful!
        if ($query->num_rows() > 0) {
            foreach ($query->result_array() as $row) {
                return ($row['user_name'] == $user_name) ? Usermodel::USER_USERNAME_EXISTS : Usermodel::USER_EMAIL_EXISTS;
            }
        } else {
            // generate password hash with auto secure salt (60 char string)
            $user_password_hash = password_hash($user_password, PASSWORD_DEFAULT);
            // generate random hash for email verification (40 char string)
            $user_activation_hash = sha1(uniqid(mt_rand(), TRUE));

            // write new users data into database
            $new_user_data = array(
                'user_name' => $user_name,
                'user_password_hash' => $user_password_hash,
                'user_email' => $user_email,
                'user_activation_hash' => $user_activation_hash,
                'user_registration_datetime' => time(),
                'user_registration_ip' => $this->input->ip_address(),
            );
            $query = $this->db->insert('ci_users', $new_user_data);
            //print_r($new_user_data);
            // id of new user
            $user_id = $this->db->insert_id();

            if ($query) {
                // send a verification email
                if ($this->send_verification_email($user_id, $user_email, $user_activation_hash)) {
                    // when mail has been send successfully
                    return TRUE;
                } else {
                    // delete this users account immediately, as we could not send a verification email
                    $this->db->where('username', $user_name);
                    $this->db->delete('ci_users');

                    return Usermodel::USER_EMAIL_SEND_FAILED;
                }
            } else {
                return Usermodel::USER_REGISTER_FAILED;
            }
        }
    }

    /**
     * Gửi Email kích hoạt tài khoản
     * 
     * @param int $user_id
     * @param string $user_email
     * @param string $user_activation_hash
     * @return boolean Trã về TRUE nếu đã gửi và ngược lại
     */
    public function send_verification_email($user_id, $user_email, $user_activation_hash) {
        $this->load->library('email');

        $this->email->from('no-reply@example.com', 'Codeigniter Mailer');
        $this->email->to($user_email);

        $this->email->subject('Kích hoạt tài khoản của bạn');
        $this->email->message('Bấm vào liên kết này để hoàn tất đăng ký: ' . site_url('thanh_vien/dang_ky/xac_thuc_tai_khoan/' . urlencode($user_id) . '/' . urlencode($user_activation_hash)));

        if (!$this->email->send()) {
            return FALSE;
        } else {
            return TRUE;
        }
    }

    /**
     * Kiểm tra id / mã xác minh kết hợp và thiết lập trạng thái kích hoạt cho người dùng, để TRUE (= 1) trong cơ sở dữ liệu
     * 
     * @param int $user_id Mã người dùng
     * @param string $user_activation_hash Chuỗi mã xác thực tài khoản
     * @return boolean Trả về TRUE nếu kích hoạt tài khoản thành công và ngược lại
     */
    public function verify_new_user($user_id, $user_activation_hash) {
        $data = array(
            'user_active' => 1,
            'user_activation_hash' => NULL,
            'user_activation_datetime' => time()
        );

        $this->db->where('user_id', $user_id);
        $this->db->where('user_activation_hash', $user_activation_hash);
        $this->db->update('ci_users', $data);

        if ($this->db->affected_rows() > 0)
            return TRUE;
        else
            return FALSE;
    }

}

/* End of file UserModel.php */
/* Location: ./application/models/UserModel.php */