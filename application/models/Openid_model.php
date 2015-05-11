<?php

/*
 * Phạm Tiến Thành
 * tienthanh.dqc@gmail.com
 * +841679227582
 */

/**
 * Openid_model
 * 
 * Description...
 * 
 * @package Openid
 * @author Phạm Tiến Thành <tienthanh.dqc@gmail.com>
 * @version 0.0.0
 */
defined('BASEPATH') OR exit('No direct script access allowed');

class Openid_model extends CI_Model {

    public function __construct() {
        parent::__construct();

        require_once('vendor/hybridauth/hybridauth/hybridauth/Hybrid/Auth.php');
    }

    public function Facebook_login() {
        try {
            log_message('debug', 'controllers.HAuth.login: loading HybridAuthLib');
            $this->load->library('HybridAuthLib');
            if ($this->hybridauthlib->providerEnabled($provider)) {
                log_message('debug', "controllers.HAuth.login: service $provider enabled, trying to authenticate.");
                $service = $this->hybridauthlib->authenticate($provider);
                if ($service->isUserConnected()) {
                    log_message('debug', 'controller.HAuth.login: user authenticated.');
                    $user_profile = $service->getUserProfile();
                    log_message('info', 'controllers.HAuth.login: user profile:' . PHP_EOL . print_r($user_profile, TRUE));
                    $data['user_profile'] = $user_profile;
                    $this->load->view('hauth/done', $data);
                } else { // Cannot authenticate user
                    show_error('Cannot authenticate user');
                }
            } else { // This service is not enabled.
                show_404();
            }
        } catch (Exception $e) {
            $error = 'Unexpected error';
            switch ($e->getCode()) {
                case 0 : $error = 'Unspecified error.';
                    break;
                case 1 : $error = 'Hybriauth configuration error.';
                    break;
                case 2 : $error = 'Provider not properly configured.';
                    break;
                case 3 : $error = 'Unknown or disabled provider.';
                    break;
                case 4 : $error = 'Missing provider application credentials.';
                    break;
                case 5 :
                    show_error('User has cancelled the authentication or the provider refused the connection.');
                    break;
                case 6 : $error = 'User profile request failed. Most likely the user is not connected to the provider and he should to authenticate again.';
                    break;
                case 7 : $error = 'User not connected to the provider.';
                    break;
            }
            if (isset($service)) {
                $service->logout();
            }
            show_error('Error authenticating user.');
        }
    }

}

/* End of file Openid_model.php */
/* Location: ./application/models/Openid_model.php */