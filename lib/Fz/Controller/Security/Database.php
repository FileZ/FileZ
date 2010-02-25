<?php

class Fz_Controller_Security_Database extends Fz_Controller_Security_Internal {

    /**
     * Check a user password
     *
     * @param string    $username
     * @param string    $password
     * @return string   id of the user or false if the user/pass is incorrect
     */
    public function checkPassword ($username, $password) {

        // TODO Check password from database or LDAP


    }

}