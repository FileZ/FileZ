<?php

abstract class Fz_Controller_Security_Abstract {

    /**
     * @var array $_options 
     */
    protected $_options = array ();

    /**
     * Redirect the user to a login page if he isn't logged in.
     *
     * @return void
     */
    abstract protected function _doSecure ();

    /**
     * Redirect the user to a login page if he isn't logged in.
     *
     * @return void
     */
    public function secure () {
        if (! $this->isSecured ())
            return $this->_doSecure ();
    }

    /**
     * Return user id
     *
     * @return string
     */
    public function getUserId () {
        return (array_key_exists ('uid', $_SESSION) ?
           $_SESSION ['uid'] : null);
    }

    /**
     * Set user id
     *
     * @param string $uid 
     */
    public function setUserId ($uid) {
        $_SESSION ['uid'] = $uid;
    }

    /**
     * Return true of the user has already been identified
     *
     * @return boolean
     */
    public function isSecured () {
        return $this->getUserId () !== null;
    }

    /**
     * Check a user password
     *
     * @return boolean
     */
    abstract public function checkPassword ($username, $password);


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

