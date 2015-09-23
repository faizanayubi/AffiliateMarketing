<?php

/**
 * Scheduler Class which executes daily and perfoms the initiated job
 * 
 * @author Faizan Ayubi
 */

class CRON extends Auth {

    public function __construct($options = array()) {
        parent::__construct($options);
        $this->willRenderLayoutView = false;
        $this->willRenderActionView = false;
    }

    public function index() {
        $this->_secure();
        $this->variftClick();
    }
    
    protected function variftClick() {
        
    }
    
    protected function calculateEarnings() {
        
    }

    /**
     * @protected
     */
    public function _secure() {
        if ($_SERVER['REMOTE_ADDR'] != $_SERVER['SERVER_ADDR']) {
            die('access is not permitted');
        }
    }

}
