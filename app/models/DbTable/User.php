<?php

/**
 * @file
 * Short description.
 * 
 * Long description.
 * 
 * @package FileZ
 */

class App_Model_DbTable_User extends Fz_Db_Table_Abstract {

    protected $_rowClass = 'App_Model_User';
    protected $_name = 'fz_user';
    protected $_columns = array (
        'id',
        'username',
        'password',
        'salt',
        'firstname',
        'lastname',
        'email',
        'is_admin',
        'created_at',
    );

    /**
     * Retrieve a user by its username
     *
     * @param string $username
     * @return App_Model_User or null if not found
     */
    public function findByUsername ($username) {
        $sql = 'SELECT * FROM '.$this->getTableName ().' WHERE username = ?';
        return $this->findOneBySQL ($sql, $username);
    }

    /**
     * Retrieve a user by its email
     *
     * @param string $email
     * @return App_Model_User or null if not found
     */
    public function findByEmail ($email) {
        $sql = 'SELECT * FROM '.$this->getTableName ().' WHERE email = ?';
        return $this->findOneBySQL ($sql, $email);
    }

    /**
     * Count the number of users
     * 
     * @return integer number of users
     */
    public function getNumberOfUsers () {
        $sql = 'SELECT COUNT(*) AS count FROM '.$this->getTableName ();
        $res = Fz_Db::findAssocBySQL($sql);
        return $res[0]['count'];
    }
}
