<?php

/**
 * @file
 * Short description.
 *
 * Long description.
 *
 * @package FileZ
 */

include fz_config_get ('user_factory_options', 'samlIncludeDir');

class Fz_Controller_Security_Saml extends Fz_Controller_Security_Abstract {

    public $objAuthSaml;

    /**
     * Fz_Controller_Security_Saml constructor.
     * Login on application load
     */
    public function __construct()
    {
        $this->objAuthSaml = new SimpleSAML_Auth_Simple(fz_config_get ('user_factory_options', 'spName'));
        /*$entry = $this->getSSOUser();
        var_dump($entry);*/
    }

    public function login($username = null, $password = null){

        $entry = $this->getSSOUser();

        $user = array();
        foreach ($entry as $k => $v){
            if (is_array ($v) && count ($v) === 1){
                $user [$k] = $v[0];
            }
        }

        if($entry && !empty($user)){
            $this->setUserId ($user[fz_config_get('user_attributes_translation', 'id')]);
            fz_log ('successful login SSO SAML of "'.$user[fz_config_get('user_attributes_translation', 'email')].'"');
            return $user;
        } else {
            fz_log ('unsuccessful SSO SAML', FZ_LOG_ERROR);
            return null;
        }
    }

    public function logout(){
        $this->objAuthSaml->logout();
        exit();
    }

    /**
     * Return user id
     *
     * @return string
     */
    public function getUserId()
    {
        $entry = $this->getSSOUser();

        foreach ($entry as $k => $v){
            if (is_array ($v) && count ($v) === 1){
                if ($k == fz_config_get('user_attributes_translation', 'id')) {
                    return $v[0];
                }
            }

        }

        return null;
    }

    /**
     * Redirect the user to a login page if he isn't logged in.
     *
     * @return void
     */
    protected function _doSecure () {
        redirect_to ('/login');
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
     * Retrieve SSO User
     * @return object           User attributes
     */
    public function getSSOUser(){
        if(count($this->objAuthSaml->getAttributes()) > 0){
            return $this->objAuthSaml->getAttributes();
        } else {
            return null;
        }
    }

}