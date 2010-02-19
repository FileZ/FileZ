<?php

/**
 * Application controller
 */
class Fz_Controller {

    // Most of this attributes are static in order to share data between controllers
    // while forwarding request for example
    protected static $_user = null;
    protected static $_userFactory = null;
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
    }

    /**
     * Return the current user profile
     */
    protected function getUser () {
        $auth = $this->getAuthHandler ();
        if (self::$_user === null && $auth->isSecured ()) {
            self::$_user = $this->buildUserProfile (
                           $this->getUserFactory ()->findById ($auth->getUserId ()));
        }
        return self::$_user;
    }

    /**
     * Translate profile var name from their original name.
     *
     * @param array   $profile
     * @return array            Translated profile
     */
    protected function buildUserProfile ($profile) {
        $p = array ();
        $translation = fz_config_get ('user_attributes_translation', null, array ());
        foreach ($profile as $key => $value)
            if (array_key_exists ($key, $translation))
                    $p [$translation [$key]] = $value;
        return $p;
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
            $authClass = fz_config_get ('auth', 'handler_class',
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
        if (self::$_userFactory === null) {
            $factoryClass = fz_config_get ('user_factory', 'user_factory_class',
                                           'Fz_User_Factory_Ldap');
            self::$_userFactory = new $factoryClass ();
            self::$_userFactory->setOptions (
                        fz_config_get ('user_factory_options', null, array ()));
        }
        return self::$_userFactory;
    }

    /**
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

