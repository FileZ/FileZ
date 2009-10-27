<?php 

class fzFile extends fzTableRow {

    protected $_tableClass = 'fzFileTable';

    public function getHash () {
        return fz_model_file_id2hash ($this->id);
    }

    public function __toString () {
        return $this->file_name;
    }

    public function __construct ($exists = false) {
        parent::__construct ($exists);
        if (! $exists)
            $this->id = $this->getTable ()->getFreeId ();
    }

    public function getAvailableUntil () {
        return new Zend_Date($this->available_until, Zend_Date::ISO_8601);
    }

    public function getAvailableFrom () {
        return new Zend_Date($this->available_from, Zend_Date::ISO_8601);
    }

    protected function setAvailableUntil ($date) {
        $this->available_until = $date instanceof Zend_Date ?
            $date->get (Zend_Date::ISO_8601) : $data;
    }

    protected function setAvailableFrom ($date) {
        $this->available_from = $date instanceof Zend_Date ?
            $date->get (Zend_Date::ISO_8601) : $data;
    }
}

class fzFileTable extends fzTable {

    protected $_rowClass = 'fzFile';
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
        $min = 0;
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
        return fz_db_find_object_by_sql ($sql, fzFile::$TABLE_NAME, array (':uid' => $uid));
    }
}


