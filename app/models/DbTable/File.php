<?php

class App_Model_DbTable_File extends Fz_Db_Table_Abstract {

    protected $_rowClass = 'App_Model_File';
    protected $_name = 'fz_file';
    protected $_columns = array (
        'del_notif_sent',
        'file_name',
        'uploader_email',
        'file_size',
        'available_from',
        'available_until',
        'download_count',
        'notify_uploader',
        'uploader_uid',
        'extends_count',
    );

    public function hashToId ($hash) {
        return base_convert ($hash, 10, 36);
    }

    public function idToHash ($id) {
        return base_convert ($id, 10, 36);
    }

    public function getFreeId () {
        $min = base_convert ('aaaa' , 36, 10);
        $max = base_convert ('zzzzz', 36, 10); // hash is 5 chars max
        $id = rand ($min, $max);
        while ($this->rowExists ($id))
            $id = rand ($min, $max);
        return $id;
    }

    public static function findByHash ($hash) {
        $id = $this->hashToId ($hash);
        return $this->findById ($id);
    }

    public static function findByOwner ($uid) {
        $sql = "SELECT FROM fz_file WHERE uploader_uid=:uid";
        return fz_db_find_object_by_sql ($sql, App_Model_File::$TABLE_NAME, array (':uid' => $uid));
    }
}


