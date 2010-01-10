<?php

class Fz_Controller_Security_Cas extends Fz_Controller_Security_Abstract {

    /**
     * Redirect the user to a login page if he isn't logged in.
     *
     * @return void
     */
    protected function _doSecure () {
        require_once 'CAS.php';

        // phpCAS is not php5-compliant, we disable error reporting
        $errorReporting = ini_get ('error_reporting');
        error_reporting ($errorReporting & ~E_STRICT & ~E_NOTICE);

        phpCAS::setDebug();
        phpCAS::client (CAS_VERSION_2_0,
            $this->getOption ('cas_server_host', 'localhost'),
            $this->getOption ('cas_server_port', 443),
            $this->getOption ('cas_server_path', ''),
            false); // Don't call session_start again

        //phpCAS::handleLogoutRequests ();
        phpCAS::setNoCasServerValidation ();
        phpCAS::forceAuthentication (); // if necessary the user will be
                                        // redirected to the cas server

        // At this point the user is authenticated, we log him in
        $this->setUserId (phpCAS::getUser ());

        // Previous settings can now be restored
        error_reporting ($errorReporting);
    }

    /**
     * Check a user password
     *
     * @return boolean
     */
    public function checkPassword ($username, $password) {}

}
