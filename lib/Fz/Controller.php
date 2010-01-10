<?php

/**
 * Application controller
 */
class Fz_Controller {

    protected static $_user = null;
    protected static $_userFactory = null;
    protected static $_authHandler = null;

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
}

