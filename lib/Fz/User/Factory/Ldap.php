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
 * Description of Fz_User_Factory_Ldap
 */
class Fz_User_Factory_Ldap extends Fz_User_Factory_Abstract {
    
    protected $_ldapCon = null;

    /**
     * Find one user by its ID
     *
     * @param string $id    User id
     * @return array        User attributes
     */
    public function _findById ($id) {
        $entry = $this->getLdap()->getAccount ($id);
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
        // Let's try to connect to the ldap server with the specified user/password
        $ldapTest = new Fz_Ldap ($this->_options);
        try {
            $ldapTest->bind ($username, $password);
            return $this->_findById ($username);
        } catch (Zend_Ldap_Exception $zle) {
            // TODO throw error if we can't reach the ldap host
            return null;
        }
    }

    /**
     *
     * @return Fz_Ldap
     */
    protected function getLdap () {
        if ($this->_ldapCon === null) {
            $this->_ldapCon = new Fz_Ldap ($this->_options);
            try {
                $this->_ldapCon->bind();
            } catch (Zend_Ldap_Exception $zle) {
                fz_log ('Fz_User_Factory_Ldap: Can\'t bind ldap server', FZ_LOG_ERROR);
                throw $zle;
            }
        }
        return $this->_ldapCon;
    }
}
?>
