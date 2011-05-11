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
    abstract protected function _findById ($id);

    /**
     * Retrieve a user corresponding to $username and $password
     *
     * @param string $username
     * @param string $password
     * @return array            User attributes if user was found, null if not
     */
    abstract protected function _findByUsernameAndPassword ($username, $password);

    /**
     * Retrieve a user corresponding to $username and $password
     *
     * @param string $username
     * @param string $password
     * @return array            User attributes if user was found, null if not
     */
    public function findByUsernameAndPassword ($username, $password) {
        if (null === ($p = $this->_findByUsernameAndPassword ($username, $password)))
            return null;

        return $this->buildUserProfile ($p);
    }

    /**
     * Find one user by its ID
     *
     * @param string $id    User id
     * @return array        User attributes
     */
    public function findById ($id) {
        if (null === ($p = $this->_findById ($id))) {
            fz_log ('User_Factory: Can\'t find profile "'.$id.'"', FZ_LOG_ERROR);
            return null;
        }
        
        return $this->buildUserProfile ($p);
    }

    /**
     * Translate profile var name from their original name.
     *
     * @param array   $profile
     * @return array            Translated profile
     */
    protected function buildUserProfile (array $profile) {
        $p = array ();
        $translation = fz_config_get ('user_attributes_translation', null, array ());
        foreach ($translation as $key => $value) {
            if (array_key_exists ($value, $profile)) {
                if (is_array ($profile [$value]))
                    $p [$key] = (count ($profile[$value]) > 0 ? $profile [$value][0] : null);
                else
                    $p [$key] = $profile [$value];
            }
            else {
                fz_log ('User_Factory: Missing attribute "'.$value.'" in user profile :', FZ_LOG_ERROR, $profile);
            }
        }
        return $p;
    }

    /**
     * Tells if users are retrieved from the build-in user table or from an external source
     * 
     * @return boolean
     */
    public function isInternal () {
        return false;
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
?>
