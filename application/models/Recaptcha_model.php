<?php

/*
 * Phạm Tiến Thành
 * tienthanh.dqc@gmail.com
 * +841679227582
 */

/**
 * Recaptcha_model
 * 
 * Description...
 * 
 * @package recaptcha
 * @author Phạm Tiến Thành <tienthanh.dqc@gmail.com>
 * @version 0.0.0
 */
defined('BASEPATH') OR exit('No direct script access allowed');

class Recaptcha_model extends CI_Model {

    const CAPTCHA_VALID = 1;
    const CAPTCHA_INVALID = 0;
    const MISSING_API_KEY = -1;
    const LANG = 'vi';

    var $site_key = '';
    var $secret_key = '';

    public function __construct() {
        parent::__construct();

        $this->site_key = $this->config->item('recaptcha_site_key');
        $this->secret_key = $this->config->item('recaptcha_secret_key');
    }

    public function create() {
        if (empty($this->site_key) || empty($this->secret_key))
            return Recaptcha_model::MISSING_API_KEY;

        return '<div class="g-recaptcha" data-sitekey="' . $this->site_key . '"></div>';
    }

    public function javascript_url() {
        return '<script type="text/javascript"
                    src="https://www.google.com/recaptcha/api.js?hl=' . Recaptcha_model::LANG . '">
            </script>';
    }

    public function check() {
        $this->benchmark->mark('reCaptcha_check_start');
        $recaptcha = new \ReCaptcha\ReCaptcha($this->secret_key);
        $resp = $recaptcha->verify($this->input->post('g-recaptcha-response'), $this->input->ip_address());
        $this->benchmark->mark('reCaptcha_check_end');

        return $resp->isSuccess();
    }

}

/* End of file recaptcha_model.php */
/* Location: ./application/models/recaptcha_model.php */