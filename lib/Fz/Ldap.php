<?php

/**
 * @file
 * Short description.
 * 
 * Long description.
 * 
 * @package FileZ
 */

class Fz_Ldap extends Zend_Ldap {

    public function getAccount ($acctname, array $attrs = array()) {
        return $this->_getAccount ($acctname, $attrs);
    }

}
