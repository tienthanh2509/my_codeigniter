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
 * @author Phạm Tiến Thành <tienthanh.dqc@gmail.com>
 * @version 0.0.0
 */
defined('BASEPATH') OR exit('No direct script access allowed');

class Usermodel extends CI_Model {

    // Register Status
    const USER_REGISTER_SUCCEED = 1;
    const USER_REGISTER_FAILED = 0;
    const USER_CHECK_USERNAME_FAILED = -1;
    const USER_USERNAME_BADWORD = -2;
    const USER_USERNAME_EXISTS = -3;
    const USER_EMAIL_FORMAT_ILLEGAL = -4;
    const USER_EMAIL_ACCESS_ILLEGAL = -5;
    const USER_EMAIL_EXISTS = -6;
    const USER_EMAIL_SEND_FAILED = -7;
    // Login Status
    const USER_LOGIN_SUCCEED = 1;
    const USER_DOES_NOT_EXIST = 0;
    const USER_LOGIN_FAILED = 0;
    const USER_PASSWORD_WRONG = 0;
    const USER_PASSWORD_WRONG_3_TIMES = -11;
    const USER_ACCOUNT_NOT_ACTIVATED = -12;
    const USER_COOKIE_INVALID = -14;

    /**
     * @var int $user_id Stt của người dùng trong CSDL
     */
    private $user_id = NULL;

    /**
     * @var string $user_name Tên tài khoản của người dùng
     */
    private $user_name = '';

    /**
     * @var string $user_email Địa chỉ email của người dùng
     */
    private $user_email = '';

    /**
     * @var boolean $user_is_logged_in Trạng thái đăng nhập
     */
    private $user_is_logged_in = FALSE;

    public function __construct() {
        parent::__construct();

        // Kết nối tới cơ sở dữ liệu
        $this->load->database();

        // Thư viện quản lý phiên làm việc
        $this->load->library('session');
    }

    /**
     * 
     * @param string $username
     * @return boolean
     */
    function check_username($username) {
        $guestexp = '\xA1\xA1|\xAC\xA3|^Guest|^\xD3\xCE\xBF\xCD|\xB9\x43\xAB\xC8';
        $len = strlen($username);
        if ($len > 15 || $len < 3 || preg_match("/\s+|^c:\\con\\con|[%,\*\"\s\<\>\&]|$guestexp/is", $username))
        {
            return FALSE;
        }
        else
        {
            return TRUE;
        }
    }

    /**
     * 
     * @param string $email
     * @return boolean
     */
    function check_emailformat($email) {
        return strlen($email) > 6 && preg_match("/^[\w\-\.]+@[\w\-\.]+(\.\w+)+$/", $email);
    }

    /**
     * Xử lý toàn bộ quá trình đăng ký. 
     * Kiểm tra tất cả các khả năng lỗi,
     * và tạo ra một người dùng mới trong cơ sở dữ liệu nếu mọi thứ đều tốt
     * 
     * @param string $user_name
     * @param string $user_email
     * @param string $user_password
     * @return int Mã trạng thái đăng ký
     */
    public function new_user($user_name, $user_email, $user_password) {
        // Kiểm tra dữ liệu đầu vào
        $user_name = trim($user_name);
        $user_email = trim($user_email);
        $user_password = trim($user_password);

        // Kiểm duyệt theo chính sách riêng của hệ thống
        if (!$this->check_username($user_name))
            return Usermodel::USER_CHECK_USERNAME_FAILED;
        elseif (!$this->check_emailformat($user_email))
            return Usermodel::USER_EMAIL_FORMAT_ILLEGAL;

        // Kiểm tra tên tài khoản đã tồn tại không
        $this->db->select('user_name, user_email');
        $this->db->where('user_name', $user_name);
        $this->db->or_where('user_email', $user_email);
        $query = $this->db->get('ci_users');

        // Nếu đã tồn tại thì thông báo tên tài khoản hoặc email đã có người dùng
        if ($query->num_rows() > 0)
        {
            foreach ($query->result_array() as $row)
            {
                return ($row['user_name'] == $user_name) ? Usermodel::USER_USERNAME_EXISTS : Usermodel::USER_EMAIL_EXISTS;
            }
        }
        // Nếu chưa tồn tại tên tài khoản thì tiếp tục thủ tục đăng ký
        else
        {
            // Tạo một mã băm với "salt" bảo mật tự động (60 ký tự)
            $user_password_hash = password_hash($user_password, PASSWORD_DEFAULT);
            // Tạo mã băm cho mã kích hoạt (40 ký tự)
            $user_activation_hash = sha1(uniqid(mt_rand(), TRUE));

            // Mọi thứ đã ổn thỏa, tổng hợp dữ liệu và ghi vào cơ sở dữ liệu
            $new_user_data = array(
                'user_name' => $user_name,
                'user_password_hash' => $user_password_hash,
                'user_email' => $user_email,
                'user_activation_hash' => $user_activation_hash,
                'user_registration_datetime' => time(),
                'user_registration_ip' => $this->input->ip_address(),
            );
            $query = $this->db->insert('ci_users', $new_user_data);
            if (!$query)
                return Usermodel::USER_REGISTER_FAILED;

            // ID của tài khoản mới tạo
            $user_id = $this->db->insert_id();

            // Gửi Email yêu cầu kích hoạt tài khoản
            if ($this->send_verification_email($user_id, $user_email, $user_activation_hash))
            {
                return Usermodel::USER_REGISTER_SUCCEED;
            }
            else
            {
                // Nếu không gửi được Email kích hoạt thì lập tức xóa tài khoản mới tạo
                $this->db->where('username', $user_name);
                $this->db->delete('ci_users');

                return Usermodel::USER_EMAIL_SEND_FAILED;
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

        if (!$this->email->send())
        {
            return FALSE;
        }
        else
        {
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

    /**
     * 
     * @param type $user_name
     * @return boolean|array
     */
    public function get_userdata($user_name) {
        $this->db->where('user_name', $user_name);
        $this->db->limit(1);
        $query = $this->db->get('ci_users');

        if ($query->num_rows() < 1)
        {
            return FALSE;
        }

        foreach ($query->result_array() as $row)
        {
            return $row;
        }
    }

    /**
     * 
     * @param type $user_email
     * @return boolean|array
     */
    public function get_userdata_by_email($user_email) {
        $this->db->where('user_email', $user_email);
        $this->db->limit(1);
        $query = $this->db->get('ci_users');

        if ($query->num_rows() < 1)
        {
            return FALSE;
        }

        foreach ($query->result_array() as $row)
        {
            return $row;
        }
    }

    /**
     * Logs in with S_SESSION data.
     * Technically we are already logged in at that point of time, as the $_SESSION values already exist.
     */
    public function login_with_sessiondata() {
        if (empty($this->session->userdata('user_id')) && empty($this->session->userdata('user_name')))
            return Usermodel::USER_LOGIN_FAILED;

        $user_data = $this->get_userdata($this->session->userdata('user_name'));

        $this->user_id = $user_data['user_id'];
        $this->user_name = $user_data['user_name'];
        $this->user_email = $user_data['user_email'];
        $this->user_is_logged_in = TRUE;

        return Usermodel::USER_LOGIN_SUCCEED;
    }

    /**
     * Logs in via the Cookie
     * @return bool success state of cookie login
     */
    public function login_with_cookiedata($cookie_rememberme = '') {
        $cookie_rememberme = !empty($cookie_rememberme) ? $cookie_rememberme : $this->input->cookie('rememberme', TRUE);

        if (!empty($cookie_rememberme))
        {
            // extract data from the cookie
            list ($user_id, $token, $hash) = explode(':', $cookie_rememberme);
            // check cookie hash validity
            if ($hash == hash('sha256', $user_id . ':' . $token) && !empty($token))
            {
                // cookie looks good, try to select corresponding user
                // get real token from database (and all other data)
                $this->db->select('user_id, user_name, user_email');
                $this->db->where('user_id', $user_id);
                $this->db->where('user_rememberme_token', $token);
                $this->db->limit(1);
                //$this->db->where('user_rememberme_token IS NOT NULL');
                $this->db->get('ci_users');

                if ($query->num_rows() > 0)
                {
                    foreach ($query->result_array() as $user_data_row)
                    {
                        // write user data into PHP SESSION [a file on your server]
                        $this->load->library('session');
                        $session_data = array(
                            'user_id' => $user_data_row['user_id'],
                            'user_name' => $user_data_row['user_name'],
                            'user_email' => $user_data_row['user_email'],
                            'user_logged_in' => 1,
                        );
                        $this->session->set_userdata($session_data);

                        // declare user id, set the login status to true
                        $this->user_id = $user_data_row['user_id'];
                        $this->user_name = $user_data_row['user_name'];
                        $this->user_email = $user_data_row['user_email'];
                        $this->user_is_logged_in = TRUE;

                        // Cookie token usable only once
                        $this->create_rememberme_cookie();

                        return Usermodel::USER_LOGIN_SUCCEED;
                    }
                }
            }
            // A cookie has been used but is not valid... we delete it
            $this->delete_rememberme_cookie();
            return Usermodel::USER_COOKIE_INVALID;
        }

        return Usermodel::USER_LOGIN_FAILED;
    }

    public function login_with_postdata($user_name, $user_password, $user_rememberme = FALSE) {
        // Kiểm tra dữ liệu đầu vào
        $user_name = trim($user_name);
        $user_password = trim($user_password);

        // user can login with his username or his email address.
        // if user has not typed a valid email address, we try to identify him with his user_name
        if (!filter_var($user_name, FILTER_VALIDATE_EMAIL))
        {
            if (!$this->check_username($user_name))
                return Usermodel::USER_CHECK_USERNAME_FAILED;

            // database query, getting all the info of the selected user
            $user_data_row = $this->get_userdata($user_name);
            // if user has typed a valid email address, we try to identify him with his user_email
        }
        else
        {
            if (!$this->check_emailformat($user_name))
                return Usermodel::USER_EMAIL_FORMAT_ILLEGAL;

            $user_data_row = $this->get_userdata_by_email($user_name);
        }

        // if this user not exists
        if (!isset($user_data_row['user_id']))
        {
            // was MESSAGE_USER_DOES_NOT_EXIST before, but has changed to MESSAGE_LOGIN_FAILED
            // to prevent potential attackers showing if the user exists
            return Usermodel::USER_LOGIN_FAILED;
        }
        else if (($user_data_row['user_failed_logins'] >= 3) && ($user_data_row['user_last_failed_login'] > (time() - 30)))
        {
            return Usermodel::USER_PASSWORD_WRONG_3_TIMES;
        }
        // using PHP 5.5's password_verify() function to check if the provided passwords fits to the hash of that user's password
        else if (!password_verify($user_password, $user_data_row['user_password_hash']))
        {
            // increment the failed login counter for that user
            $data = array(
                'user_failed_logins' => $user_data_row['user_failed_logins'] + 1, // @TODO Sai 3 lần phải áp dụng cho từng session chứ không nên bỏ vào CSDL
                'user_last_failed_login' => time()
            );
            $this->db->where('user_name', $user_name);
            $this->db->or_where('user_email', $user_name);
            $this->db->update('ci_users', $data);

            return Usermodel::USER_LOGIN_FAILED;
        }
        // has the user activated their account with the verification email
        else if ($user_data_row['user_active'] != 1)
        {
            return Usermodel::USER_ACCOUNT_NOT_ACTIVATED;
        }
        else
        {
            // write user data into PHP SESSION [a file on your server]
            $session_data = array(
                'user_id' => $user_data_row['user_id'],
                'user_name' => $user_data_row['user_name'],
                'user_email' => $user_data_row['user_email'],
                'user_logged_in' => 1,
            );
            $this->session->set_userdata($session_data);

            // declare user id, set the login status to true
            $this->user_id = $user_data_row['user_id'];
            $this->user_name = $user_data_row['user_name'];
            $this->user_email = $user_data_row['user_email'];
            $this->user_is_logged_in = TRUE;

            // reset the failed login counter for that user
            $data = array(
                'user_failed_logins' => 0,
                'user_last_failed_login' => NULL
            );
            $this->db->where('user_id', $this->user_id);
            $this->db->where('user_failed_logins !=', 0);
            $this->db->update('ci_users', $data);

            // @TODO: ADD THIS FEATURE
            // if user has check the "remember me" checkbox, then generate token and write cookie
            if ($user_rememberme)
            {
                $this->create_rememberme_cookie();
            }
            else
            {
                // Reset remember-me token
                $this->delete_rememberme_cookie();
            }

            // OPTIONAL: recalculate the user's password hash
            // DELETE this if-block if you like, it only exists to recalculate users's hashes when you provide a cost factor,
            // by default the script will use a cost factor of 10 and never change it.
            // @TODO: ADD THIS FEATURE

            return Usermodel::USER_LOGIN_SUCCEED;
        }
    }

    /**
     * Create all data needed for remember me cookie connection on client and server side
     */
    private function create_rememberme_cookie() {
        if ($this->user_id)
        {
            // generate 64 char random string and store it in current user data
            $random_token_string = hash('sha256', mt_rand());
            $data = array(
                'user_rememberme_token' => $random_token_string,
            );
            $this->db->where('user_id', $this->user_id);
            $this->db->update('ci_users', $data);

            // generate cookie string that consists of userid, randomstring and combined hash of both
            $cookie_string_first_part = $this->user_id . ':' . $random_token_string;
            $cookie_string_hash = hash('sha256', $cookie_string_first_part);
            $cookie_string = $cookie_string_first_part . ':' . $cookie_string_hash;

            // set cookie
            $this->input->set_cookie('rememberme', $cookie_string, 2 * 7 * 24 * 60 * 60);
        }
    }

    /**
     * Delete all data needed for remember me cookie connection on client and server side
     */
    private function delete_rememberme_cookie() {
        // Reset rememberme token
        if (!$this->user_id)
            return FALSE;

        $data = array(
            'user_rememberme_token' => NULL,
        );
        $this->db->where('user_id', $this->user_id);
        $this->db->update('ci_users', $data);

        $this->input->set_cookie('rememberme', FALSE, time() - (3600 * 3650));

        return TRUE;
    }

    /**
     * Perform the logout, resetting the session
     */
    public function do_logout() {
        $this->delete_rememberme_cookie();

        $this->load->library('session');
        $this->session->sess_destroy();

        $this->user_is_logged_in = FALSE;
    }

    /**
     * Simply return the current state of the user's login
     * @return bool user's login status
     */
    public function is_user_loggedin() {
        if ($this->user_is_logged_in == TRUE)
            return TRUE;
        elseif ($this->login_with_sessiondata())
        {
            
        }
        else
            $this->login_with_cookiedata();


        return $this->user_is_logged_in;
    }

}

/* End of file UserModel.php */
/* Location: ./application/models/UserModel.php */