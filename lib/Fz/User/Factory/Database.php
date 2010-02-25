<?php

/**
 * Description of Fz_User_Factory_Database
 */
class Fz_User_Factory_Ldap extends Fz_User_Factory_Abstract {

    protected $_dbCon = null;

    /**
     * Find one user by its ID
     *
     * @param string $id    User id
     * @return array        User attributes
     */
    public function _findById ($id) {
        // TODO
    }

    /**
     * Retrieve a user corresponding to $username and $password.
     *
     * @param string $username
     * @param string $password
     * @return array            User attributes if user was found, null if not
     */
    protected function _findByUsernameAndPassword ($username, $password) {
        // TODO
    }
}
?>
