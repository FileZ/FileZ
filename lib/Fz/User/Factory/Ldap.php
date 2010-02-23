<?php

/**
 * Description of Fz_User_Factory_Ldap
 */
class Fz_User_Factory_Ldap extends Fz_User_Factory_Abstract {
    
    protected $_ldapCon = null;

    public function _findById ($id) {
        $entry = $this->getLdap()->getEntry ('uid='.$id.','.$this->getOption('baseDn'));
        foreach ($entry as $k => $v)
            if (is_array ($v) && count ($v) === 1)
                $entry [$k] = $v[0];

        return $entry;
    }

    /**
     *
     * @return Zend_Ldap
     */
    protected function getLdap () {
        if ($this->_ldapCon === null) {
            $this->_ldapCon = new Zend_Ldap ($this->_options);
            try {
                $this->_ldapCon->bind();
            } catch (Zend_Ldap_Exception $zle) {
                // TODO throw application exception
                throw $zle;
            }

            // TODO handle errors
        }
        return $this->_ldapCon;
    }
}
?>
