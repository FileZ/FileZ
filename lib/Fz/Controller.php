<?php

/**
 * Application controller
 *
 * Everything is static (and uggly) but limonade PHP needs callback to execute actions.
 *
 */
class Fz_Controller {

    protected static $_user = null;

    /**
     * Check if the current user is authenticated and forward
     * to a login action if not.
     *
     * @param string  $credential
     */
    protected function secure ($credential) {
        if (! $this->getUser ()->isAuthenticated ()) {
            // TODO use Zend plugin loader
            $authClass = fz_config_get ('auth', 'handler_class', 'Fz_User_Authentication_Cas');
            call_user_func_array (array ($authClass, 'secure'));
        }
        else {
            // TODO check credentials
        }
    }

    /**
     * Return the current user profile
     */
    protected function getUser () {
        if (self::$user === null) {
            // TODO init the user
        }
    }

    /**
     * Returns the config
     */
    protected function getConfig () {

    }
}

