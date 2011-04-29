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
/**
 * Application controller
 */
class Fz_Controller {

    // Most of this attributes are static in order to share data between controllers
    // while forwarding request for example
    protected static $_user = null;
    protected static $_authHandler = null;
    protected static $_mailTransportSet = false;

    /**
     * Check if the current user is authenticated and forward
     * to a login action if not.
     *
     * @param string  $credential
     */
    protected function secure ($credential = null) {
        $this->getAuthHandler ()->secure ();
        // TODO check credentials

        // setting user template var
        set ('user', $this->getUser());
    }

    /**
     * Return the current user profile
     */
    protected function getUser () {
        $auth = $this->getAuthHandler ();
        if (self::$_user === null && $auth->isSecured ()) {
            self::$_user = $this->getUserFactory ()->findById ($auth->getUserId ());
        }
        return self::$_user;
    }

    /**
     * Returns the config
     */
    protected function getConfig () {

    }

    /**
     * Return an instance of the authentication handler class
     * 
     * @return Fz_Controller_Security_Abstract
     */
    protected function getAuthHandler () {
        if (self::$_authHandler === null) {
            $authClass = fz_config_get ('app', 'auth_handler_class',
                                        'Fz_Controller_Security_Cas');
            self::$_authHandler = new $authClass ();
            self::$_authHandler->setOptions (
                                fz_config_get ('auth_options', null, array ()));
        }
        return self::$_authHandler;
    }

    /**
     * Return an instance of the user factory
     *
     * @return Fz_User_Factory_Abstract
     */
    protected function getUserFactory () {
        return option ('userFactory');
    }

    /**
     * Tells if the request was made from an xml http request object
     *
     * @return boolean
     */
    protected function isXhrRequest () {
        return (array_key_exists ('HTTP_X_REQUESTED_WITH', $_SERVER)
                      && $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest');
    }

    /**
     * Create an instance of Zend_Mail, set the default transport and the sender
     * info.
     *
     * @return Zend_Mail
     */
    protected function createMail () {
        if (self::$_mailTransportSet === false) {
            $config = fz_config_get ('email');
            $config ['name'] = 'filez';
            $transport = new Zend_Mail_Transport_Smtp ($config ['host'], $config);
            Zend_Mail::setDefaultTransport ($transport);
            self::$_mailTransportSet = true;
        }
        $mail = new Zend_Mail ('utf-8');
        $mail->setFrom ($config ['from_email'], $config ['from_name']);
        return $mail;
    }

    /**
     * Redirect the user to the previous page
     */
    protected function goBack () {
        redirect ($_SERVER["HTTP_REFERER"]);
    }
}
