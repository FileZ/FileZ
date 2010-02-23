<?php

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
     * Find one user by its ID
     *
     * @param string $id    User id
     * @return array        User attributes
     */
    public function findById ($id) {
        if (null === ($p = $this->_findById ($id)))
            return null;
        
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
        foreach ($profile as $key => $value)
            if (array_key_exists ($key, $translation))
                    $p [$translation [$key]] = $value;
        return $p;
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
