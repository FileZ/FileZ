<?php

/**
 * @file
 * Short description.
 * 
 * Long description.
 * 
 * @package FileZ
 */

class Fz_Controller_Security_Cas extends Fz_Controller_Security_Abstract {

    protected $_casInitialized = false;

    /**
     * Redirect the user to a login page if he isn't logged in.
     *
     * @return void
     */
    protected function _doSecure () {
        // phpCAS is not php5-compliant, we disable error reporting
        $errorReporting = ini_get ('error_reporting');
        error_reporting (0);

        $this->initCasClient ();

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


    /**
     * Destroy the user session
     */
    public function logout () {
        $errorReporting = ini_get ('error_reporting');
        error_reporting ($errorReporting & ~E_STRICT & ~E_NOTICE);
        $this->initCasClient ();
        phpCAS::logoutWithRedirectService ('http://'.$_SERVER['HTTP_HOST'].url_for('/')); // FIXME remove plain "http"
        error_reporting ($errorReporting);
    }

    private function initCasClient () {
        if (! $this->_casInitialized) {
            require_once 'CAS.php';
            phpCAS::setDebug();
            phpCAS::client (CAS_VERSION_2_0,
                $this->getOption ('cas_server_host', 'localhost'),
          (int) $this->getOption ('cas_server_port', 443),
                $this->getOption ('cas_server_path', ''),
                false); // Don't call session_start again
            $this->_casInitialized = true;
        }
    }
}
