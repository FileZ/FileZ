<?php

/**
 * @file
 * Short description.
 * 
 * Long description.
 * 
 * @package FileZ
 */

class Fz_Controller_Security_Internal extends Fz_Controller_Security_Abstract {

    /**
     * Redirect the user to a login page if he isn't logged in.
     *
     * @return void
     */
    protected function _doSecure () {
        redirect_to ('/login');
    }
    
}
