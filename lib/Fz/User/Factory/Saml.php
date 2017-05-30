<?php

/**
 * @file
 * Short description.
 *
 * Long description.
 *
 * @package FileZ
 */

/**
 * Description of Fz_User_Factory_Saml
 */
class Fz_User_Factory_Saml extends Fz_User_Factory_Abstract {

    protected $_samlCon = null;

    /**
     * Find connected user
     *
     * @param string $id    User id
     * @return array        User attributes
     */
    public function _findById ($id = null) {

        $entry = $this->getSSOUser();

        foreach ($entry as $k => $v)
            if (is_array ($v) && count ($v) === 1)
                $entry [$k] = $v[0];

        return $entry;
    }

    /**
     * Retrieve a user corresponding to $username and $password.
     *
     * @param string $username
     * @param string $password
     * @return array            User attributes if user was found, null if not
     */
    protected function _findByUsernameAndPassword ($username, $password) {
        return $this->_findById ();
    }

    /**
     * Retrieve SSO User
     * @return object           User attributes
     */
    public function getSSOUser(){
        $this->objAuthSaml = new SimpleSAML_Auth_Simple(fz_config_get ('user_factory_options', 'spName'));
        if(count($this->objAuthSaml->getAttributes()) > 0){
            return $this->objAuthSaml->getAttributes();
        } else {
            return false;
        }
    }
}

