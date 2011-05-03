<?php

class App_Model_DbTable_Info extends Fz_Db_Table_Abstract {

    protected $_rowClass = 'App_Model_Info';
    protected $_name = 'fz_info';
    protected $_columns = array (
        'key',
        'value',
    );
    
    public function getLastCronTimestamp()
    {
        $db   = option ('db_conn');
        $sql  = "SELECT value FROM ".$this->getTableName ()." WHERE fz_info.key = 'cron_freq'";
        $stmt = $db->prepare ($sql);
        $stmt->execute ();

        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['value'];
    }

    public function setLastCronTimestamp($date)
    {
        $db   = option ('db_conn');
        $sql  = "UPDATE ".$this->getTableName ()." SET value='".$date."' WHERE fz_info.key='cron_freq'";
        $stmt = $db->prepare ($sql);
        $stmt->execute ();
    }
}
?>