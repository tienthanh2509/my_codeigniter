<?php

defined('BASEPATH') OR exit('No direct script access allowed');

/*
 * Phạm Tiến Thành
 * tienthanh.dqc@gmail.com
 * +841679227582
 */

$config = array(
    'providers' => array(
        'Facebook' => array(
            'enabled' => TRUE,
            'keys' => array('id' => '151909874957133', 'secret' => 'a812b5db7e6235bad658575b1b8ab819'),
            'scope' => 'email, public_profile, user_friends', // optional
            'display' => 'page' // optional
        )));
