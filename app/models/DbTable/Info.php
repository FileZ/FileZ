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
 * Table to manipulate key/value informations about filez, like db_version,
 * last CRON execution, etc.
 */
class App_Model_DbTable_Info extends Fz_Db_Table_Abstract {

    protected $_rowClass = 'App_Model_Info';
    protected $_name = 'fz_info';
    protected $_columns = array (
        'key',
        'value',
    );

    /**
     * Method used to set a value for a specified key.
     * The row must exist in the DB
     *
     * @param string $key
     * @param string $value
     */
    public function update ($key, $value) {
        $db   = Fz_Db::getConnection();
        $sql  = 'UPDATE `'.$this->getTableName ().'` SET `value` = :value WHERE `fz_info`.`key` = :key ';
        $stmt = $db->prepare ($sql);
        return $stmt->execute (array (
            ':key' => $key,
            ':value' => $value,
        ));
    }

    /**
     * Method used to insert a value for a specified key.
     *
     * @param string $key
     * @param string $value
     */
    public function insert ($key, $value) {
        $db   = Fz_Db::getConnection();
        $sql  = 'INSERT INTO `'.$this->getTableName ().'` (`key`, `value`) VALUES (:value, :key)';
        $stmt = $db->prepare ($sql);
        return $stmt->execute (array (
            ':key' => $key,
            ':value' => $value,
        ));
    }

    /**
     * Method used to get a value for a specified key.
     *
     * @param string $key
     * @return string or false if the key wasn't found
     */
    public function get ($key) {
        $db   = Fz_Db::getConnection();
        $sql  = 'SELECT `value` FROM `'.$this->getTableName ().'` WHERE `fz_info`.`key` = ?';
        $stmt = $db->prepare ($sql);
        $stmt->execute (array ($key));

        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return ($result ? $result['value'] : false);
    }

    /**
     * Method used to set a value for a specified key.
     * If the key already exist, an update is done, otherwise an insert
     *
     * @param string $key
     * @param string $value
     */
    public function set ($key, $value) {
        if ($this->get ($key) === false)
            $this->insert ($key, $value);
        else
            $this->update ($key, $value);
    }
     
    /**
     * Retrieve the last time the CRON was executed
     *
     * @return string  Timestamp of the last CRON execution
     */
    public function getLastCronTimestamp() {
        return $this->get ('cron_freq');
    }

    /**
     * set the last time the CRON was executed
     *
     * @param string $date  Timestamp of the last CRON execution
     * @return string
     */
    public function setLastCronTimestamp($date) {
        return $this->set ('cron_freq', $date);
    }
   
    /**
     * Retrieve the last time the CRON was executed
     *
     * @return string
     */
    public function getDatabaseVersion () {
        return $this->get ('db_version');
    }

    /**
     * set the last time the CRON was executed
     *
     * @param string $version
     * @return string
     */
    public function setDatabaseVersion ($version) {
        return $this->set ('db_version', $version);
    }

}
?>
