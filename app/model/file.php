<?php 

class fzFile extends fzTableRow {

    public function getTableName () { return 'fz_file'; }
    public function getTableColumns () { return array (
            'del_notif_sent',
            'file_name',
            'uploader_email',
            'file_size',
            'available_from',
            'available_until',
            'download_count',
            'notify_uploader',
            'uploader_uid',
            'extends_count'
    );}

    public function getHash () {
        return fz_model_file_id2hash ($this->id);
    }

    public function __toString () {
        return $this->file_name;
    }

    public function __construct ($exists = false) {
        parent::__construct ($exists);
        if (! $exists)
            $this->id = fz_model_file_get_free_id ();
    }

    public function getAvailableUntil () {
        return new Zend_Date($this->available_until, Zend_Date::ISO_8601);
    }

    public function getAvailableFrom () {
        return new Zend_Date($this->available_from, Zend_Date::ISO_8601);
    }

    protected function setAvailableUntil (Zend_Date $date) {
        $this->available_until = $date->get (Zend_Date::ISO_8601);
    }

    protected function setAvailableFrom (Zend_Date $date) {
        $this->available_from = $date->get (Zend_Date::ISO_8601);
    }
}

class fzFileTable extends fzTable {

    // TODO mettre les fonctions ci dessous ici.

}


// Retrieve functions

function fz_model_file_find_all () {
    $sql = "SELECT * FROM fz_file";
    return fz_db_find_objects_by_sql ($sql, fzFile::$TABLE_NAME);
}

function fz_model_file_find_by_id ($id) {
    $sql = "SELECT FROM fz_file WHERE id=:id";
    return fz_db_find_object_by_sql ($sql, fzFile::$TABLE_NAME, array (':id' => $id));
}

function fz_model_file_find_by_hash ($hash) {
    $id = fz_model_file_hash2id ($hash);
    return fz_model_file_find_by_id ($id);
}

function fz_model_file_find_by_owner ($uid) {
    $sql = "SELECT FROM fz_file WHERE uploader_uid=:uid";
    return fz_db_find_object_by_sql ($sql, fzFile::$TABLE_NAME, array (':uid' => $uid));
}

// Update functions

function fz_model_file_update_obj ($file_obj) {
    return fz_db_update_object ($file_obj);
}

// Create functions

function fz_model_file_make_file_obj ($params, $obj = null) {
    return fz_db_make_model_object ($params, $obj);
}

// Delete functions

function fz_model_file_delete_obj ($man_obj) {
    fz_db_delete_object_by_id ($man_obj->id, 'fz_files');
}

function fz_model_file_delete_by_id ($file_id) {
    fz_db_delete_object_by_id ($file_id, 'fz_files');
}

// Model functions

function fz_model_file_hash2id ($hash) {
    return base_convert ($hash, 10, 36);
}

function fz_model_file_id2hash ($id) {
    return base_convert ($id, 10, 36);
}

function fz_model_file_get_free_id () {
    $min = 0;
    $max = base_convert ('zzzzz', 36, 10); // hash is 5 chars max
    $id = rand ($min, $max);
    while (fz_model_file_exists ($id))
        $id = rand ($min, $max);
    return $id;
}

function fz_model_file_exists ($id) {
    return fz_db_id_exists ($id, 'fz_file');
}

