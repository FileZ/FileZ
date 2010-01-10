<?php

class Fz_Controller_Security_Internal extends Fz_Controller_Security_Abstract {

    /**
     * Redirect the user to a login page if he isn't logged in.
     *
     * @return void
     */
    protected function _doSecure () {
        // TODO redirect to the
    }

    /**
     * Check a user password
     *
     * @return boolean
     */
    public function checkPassword ($username, $password) {

        // TODO Check password from database or LDAP


    }

}