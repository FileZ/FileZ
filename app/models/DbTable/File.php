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
        'deleted',
        'notify_uploader',
        'uploader_uid',
        'extends_count',
        'comment',
        'created_at',
    );

    /**
     * Transform a hash in base 36 to an integer
     *
     * @param string $hash
     * @return integer
     */
    public function hashToId ($hash) {
        return base_convert ($hash, 36, 10);
    }

    /**
     * Transforme an integer into a hash in base 36
     *
     * @param integer $id
     * @return string   hash code
     */
    public function idToHash ($id) {
        return base_convert ($id, 10, 36);
    }


    /**
     * Generate a random code in base 36.
     *
     * @param  integer  $min    Minimum size of the hash
     * @param  integer  $max    Maximum size of the hash
     * @return string           Hash code
     */
    protected function generateRandomHash ($min, $max) {
        $size = mt_rand ($min, $max);
        $hash = '';
        for ($i = 0; $i < $size; ++$i) {
            $hash .= base_convert (mt_rand (0, 35), 10, 36);
        }
        return $hash;
    }

    /**
     * Return a free slot in the fz_file table
     * 
     * @return integer
     */
    public function getFreeId () {
        $min = fz_config_get('app', 'min_hash_size');
        $max = fz_config_get('app', 'max_hash_size');
        $id = null;
        do {
            $id = base_convert ($this->generateRandomHash ($min, $max), 36, 10);
        } while ($this->rowExists ($id));
        return $id;
    }

    /**
     * Find a file by its hash code
     * 
     * @param string $hash
     * @return App_Model_File
     */
    public function findByHash ($hash) {
        return $this->findById ($this->hashToId ($hash));
    }

    /**
     * Find a file by its hash code (Filez 1.x only)
     *
     * @param string $hash
     * @return App_Model_File
     */
    public function findByFzOneHash ($hash) {
        $sql = "SELECT * FROM ".$this->getTableName ().' WHERE adresse = ?';
        return $this->findOneBySQL ($sql, array ($hash));
    }

    /**
     * Return all file owned by $uid which are available (not deleted)
     *
     * @param string $uid
     * @return array of App_Model_File
     */
    public function findByOwnerOrderByUploadDateDesc ($uid) {
        $sql = 'SELECT * FROM fz_file WHERE uploader_uid=:uid ORDER BY created_at DESC';
        return $this->findBySql ($sql, array (':uid' => $uid));
    }

    /**
     * Delete files whose lifetime expired
     */
    public function deleteExpiredFiles () {
       $where = ' WHERE available_until<CURRENT_DATE()';
       foreach ($this->findBySql ('SELECT * FROM fz_file'.$where) as $file) {
           $file->deleteFromDisk ();
       }

       $count = option ('db_conn')->exec ('DELETE FROM fz_file'.$where);
       fz_log ($count.' files deleted');
    }

    /**
     * Return files which will be deleted within X days
     * 
     * @param integer   $days   Number of days before being deleted
     * @return App_Model_File
     */
    public function findFilesToBeDeleted ($days = 2) {
        $sql = 'SELECT * FROM fz_file WHERE available_until BETWEEN '
              .'CURRENT_DATE() AND DATE_ADD(CURRENT_DATE(), INTERVAL '.$days.' DAY) '
              .'AND del_notif_sent=0';

        return $this->findBySql ($sql);
    }
}


