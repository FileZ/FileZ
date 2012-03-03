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
        $user = $this->getUser();

        // setting user template var
        set ('fz_user', $user);

        if ($credential == 'admin') { // 
            if (! $user->is_admin)
                halt (HTTP_FORBIDDEN, __('This page is secured'));
        }
    }

    /**
     * Return the current user profile
     */
    protected function getUser () {
        $auth = $this->getAuthHandler ();
        $factory = $this->getUserFactory ();
        if (self::$_user === null && $auth->isSecured ()) {
            self::$_user = Fz_Db::getTable('User')->findByUsername ($auth->getUserId ());
            if (! $factory->isInternal ()) {
                if (self::$_user === null)
                    self::$_user = new App_Model_User ();

                // Update fields
                $userData = $factory->findById ($auth->getUserId ());
                self::$_user->username     = $userData['id'];
                self::$_user->email        = $userData['email'];
                self::$_user->firstname    = $userData['firstname'];
                self::$_user->lastname     = $userData['lastname'];
                self::$_user->save (); // will issue an update or insert only if a property changed
            }
        }
        return self::$_user;
    }

    /**
     * Returns the config
     */
    protected function getConfig () {

    }

    /**
     * Initialize the controller
     */
    public function init () {}


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
        $config = fz_config_get ('email');    
        if (self::$_mailTransportSet === false) {
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


