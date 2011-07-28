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
 * Description of Fz_User_Factory_Database
 *
 * possible options are :
 *   - db_use_global_conf       Allow to use the same connection between filez
 *                              and the user factory. If false, the following
 *                              params needs to be set : db_server_dsn,
 *                              db_server_user, db_server_password.
 *   - db_table                 Table where users are stored
 *   - db_username_field        Column containing username
 *   - db_password_field        Column containing password
 *   - db_password_algorithm    Algorithm used to store password. Could be :
 *                                - MD5         (case sensitive)
 *                                - SHA1        (case sensitive)
 *                                - PHP Function name ex: "methodName"
 *                                - PHP Static method ex: "ClassName::Method"
 *                                - Plain SQL
 */
class Fz_User_Factory_Database extends Fz_User_Factory_Abstract {

    protected $_dbCon = null;

    /**
     * Find one user by its ID
     *
     * @param string $id    User id
     * @return array        User attributes or null if not found
     */
    public function _findById ($id) {
        $sql = 'SELECT * FROM '.$this->getOption ('db_table')
              .' WHERE '
              .fz_config_get ('user_attributes_translation', 'id', 'id').'=:id';
        
        return $this->fetchOne($sql, array (':id' => $id));
    }

    /**
     * Retrieve a user corresponding to $username and $password.
     *
     * @param string $username
     * @param string $password
     * @return array            User attributes if user was found, null if not
     */
    protected function _findByUsernameAndPassword ($username, $password) {
        $bindValues = array (':username' => $username,
                             ':password' => $password);
        $sql = 'SELECT * FROM '.$this->getOption ('db_table').' WHERE '
              .fz_config_get ('user_factory_options', 'db_username_field')
              .'=:username AND '
              .fz_config_get ('user_factory_options', 'db_password_field')
              .'=';
        
        $algorithm = trim ($this->getOption ('db_password_algorithm'));
        if (empty ($algorithm)) { // Shame on you !
            $sql .= ':password';
        } else if ($algorithm == 'MD5') {
            $sql .= 'MD5(:password)';
        } else if ($algorithm == 'SHA1') {
            $sql .= 'SHA1(:password)';
	} else if ($algorithm == 'crypt') {
            $sql = 'SELECT * FROM '.$this->getOption ('db_table').' WHERE '
                  .fz_config_get ('user_factory_options', 'db_username_field')
                  .'=:username';
            unset ($bindValues[':password']);
            $user = $this->fetchOne ($sql, $bindValues);
            if( crypt( $password, $user['password']) == $user['password'] ){
                return $user;
            }else{
                return $algorithm;
            }
        } else if (is_callable ($algorithm)) {
            if (strstr ($algorithm, '::') !== false)
                $algorithm = explode ('::', $algorithm);
            $sql .= $this->getConnection ()->quote (
                    call_user_func ($algorithm, $password));
            unset ($bindValues[':password']);
        } else {
            return $algorithm; // Plain SQL
        }

        return $this->fetchOne ($sql, $bindValues);
    }

    /**
     * Return a connection ressource to the database
     */
    protected function getConnection () {
        if ($this->getOption('db_use_global_conf'))
            return option ('db_conn');

        if ($this->_dbCon === null) {
            $this->_dbCon = new PDO ($this->getOption ('db_server_dsn'),
                                     $this->getOption ('db_server_user'),
                                     $this->getOption ('db_server_password'));
            $this->_dbCon->setAttribute (PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            // TODO gérer les erreurs de connexion
        }

        return $this->_dbCon;
    }

    /**
     * Execute a prepared SQL query and return one row as an array.
     * 
     * @param string $sql
     * @param array $values
     * @return array or null if not found
     */
    private function fetchOne ($sql, $values = null) {
        if ($values === null)
            $values = array ();

        $stmt = $this->getConnection()->prepare ($sql);
        $user = null;
        if ($stmt->execute ($values)) {
            $user = $stmt->fetch (PDO::FETCH_ASSOC);
            if ($user === false)
                $user = null;
        } else {
            // TODO handle error
        }
        return $user;
    }
}
?>
