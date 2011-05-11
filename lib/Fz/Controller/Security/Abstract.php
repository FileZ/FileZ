<?php

/**
 * @file
 * Short description.
 * 
 * Long description.
 * 
 * @package FileZ
 */

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
     * @param string    $username
     * @param string    $password
     * @return string   id of the user or false if the user/pass is incorrect
     */
    public function login ($username, $password) {
        $user = option ('userFactory')->findByUsernameAndPassword ($username, $password);
        if ($user !== null) {
            $this->setUserId ($user['id']);
            fz_log ('successful login of "'.$user['email'].'"');
            return $user;
        } else {
            fz_log ('unsuccessful login of "'.$username.'"', FZ_LOG_ERROR);
            return null;
        }
    }

    /**
     * Destroy the user session
     */
    public function logout () {
        $uid = $this->getUserId ();
        if ($uid !== null) {
            fz_log ('user id:'.$uid.' logs out.');
            session_unset();
            session_destroy();
        }
    }
    
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

