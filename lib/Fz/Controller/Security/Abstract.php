<?php
/**
 * Copyright 2010  Université d'Avignon et des Pays de Vaucluse 
 * email: gpl@univ-avignon.fr
 *
 * This file is part of Filez.
 *
 * Filez is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Filez is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Filez.  If not, see <http://www.gnu.org/licenses/>.
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
        fz_log ('user id:'.$this->getUserId ().' logs out.');
        session_unset();
        session_destroy();
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

