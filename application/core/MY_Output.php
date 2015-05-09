<?php

/*
 * Phạm Tiến Thành
 * +841679227582
 * tienthanh.dqc@gmail.com
 */

/**
 * Tinh chỉnh lại một số thiết lập của CodeIgniter khi ở chế độ phát triển
 *
 * @author PhamThanh
 */
class MY_Output extends CI_Output{
    function __construct() {
        parent::__construct();
        
        if (ENVIRONMENT === 'development') {
            $this->enable_profiler(TRUE);
        }
    }
}
