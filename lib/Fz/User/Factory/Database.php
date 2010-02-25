<?php

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
     * @return array        User attributes
     */
    public function _findById ($id) {
        $db         = $this->getConnection();
        $table      = $this->getOption ('dn_table');
        $idColumn   = fz_config_get ('user_attributes_translation', 'id', 'id');
        $sql        = 'SELECT * WHERE '.$table.' WHERE '.$idColumn.'=:id';
        $stmt       = $db->prepare ($sql);
        $stmt->bindValue (':id', $id);

        if ($stmt->execute ()) {
            while ($obj = $stmt->fetchObject ($class_name, array (true))) {
                $result[] = $obj;
            }
        }
        // TODOOOOOOOO

    }

    /**
     * Retrieve a user corresponding to $username and $password.
     *
     * @param string $username
     * @param string $password
     * @return array            User attributes if user was found, null if not
     */
    protected function _findByUsernameAndPassword ($username, $password) {
        // TODO
    }

    /**
     * Return a connection ressource to the database
     */
    private function getConnection () {
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
     * Tranlate the password into SQL with configured algorith.
     * 
     * @param $password
     * @return string       SQL code
     */
    private function translatePasswordToSql ($password) {

        $algorithm = trim ($this->getOption ('db_password_algorithm'));
        if ($algorithm == 'MD5') {

        } else if ($algorithm == 'SHA1')

    }
}
?>
