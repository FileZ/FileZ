<?php

/**
 * Fz_User_Factory_Abstract
 */
abstract class Fz_User_Factory_Abstract {

    protected $_options = array ();

    /**
     * Find one user by its ID
     * 
     * @param string $id    User id
     * @return array        User attributes
     */
    abstract public function findById ($id);

    public function setOptions ($options = array ()) {
        $this->_options = $options;
    }

    public function setOption ($name, $value) {
        $this->_options [$name] = $value;
    }

    public function getOption ($name, $default = null) {
        return (array_key_exists ($name, $this->_options) ?
            $this->_options [$name] : $default);
    }
}
?>
