<?php

/**
 * @file
 * Short description.
 * 
 * Long description.
 * 
 * @package FileZ
 */

class App_Model_DbTable_File extends Fz_Db_Table_Abstract {

    protected $_rowClass = 'App_Model_File';
    protected $_name = 'fz_file';
    protected $_columns = array (
        'del_notif_sent',
        'file_name',
        'file_size',
        'nom_physique',
        'available_from',
        'available_until',
        'download_count',
        'notify_uploader',
        'created_by',
        'created_at',
        'extends_count',
        'comment',
        'password',
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
        $size = mt_rand ($min, min ($max, 10));
        $hash = '';
        for ($i = 0; $i < $size; ++$i) {
            $hash .= base_convert (mt_rand (0, 35), 10, 36);
        }
        return $hash;
    }

    /**
     * Return a free slot id in the fz_file table
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
        $sql = 'SELECT * FROM '.$this->getTableName ().' WHERE adresse = ?';
        return $this->findOneBySQL ($sql, array ($hash));
    }

    /**
     * Return all file owned by $uid which are available (not deleted)
     *
     * @param App_Model_User $user
     * @return array of App_Model_File
     */
    public function findByOwnerOrderByUploadDateDesc ($user) {
        $sql = 'SELECT * FROM '.$this->getTableName ()
              .' WHERE created_by=:id '
              .' AND  available_until >= CURRENT_DATE() '
              .' ORDER BY created_at DESC';
        return $this->findBySql ($sql, array (':id' => $user->id));
    }

    /**
     * Delete files whose lifetime expired
     */
    public function deleteExpiredFiles () {
        $select = 'SELECT * FROM '.$this->getTableName ();
        $where  = ' WHERE available_until<CURRENT_DATE()';
        foreach ($this->findBySql ($select.$where) as $file) {
            if ($file->deleteFromDisk () === true) {
                fz_log ('Deleted file "'.$file->getOnDiskLocation ().'"',
                        FZ_LOG_CRON);
            } else {
                fz_log ('Failed deleting file "'.$file->getOnDiskLocation ().'"',
                        FZ_LOG_CRON_ERROR);
            }
        }
        option ('db_conn')->exec ('DELETE FROM '.$this->getTableName ().$where);
    }

    /**
     * Return files which will be deleted within X days and where uploader wants
     * to be notified but hasn't been yet
     * 
     * @param integer   $days   Number of days before being deleted
     * @return App_Model_File
     */
    public function findFilesToBeDeleted ($days = 2) {
        $sql = 'SELECT * FROM '.$this->getTableName ()
              .' WHERE available_until BETWEEN CURRENT_DATE() '
              .'AND DATE_ADD(CURRENT_DATE(), INTERVAL '.$days.' DAY) '
              .'AND del_notif_sent=0 AND notify_uploader=1';

        return $this->findBySql ($sql);
    }

    /**
     * Return disk space used by someone
     *
     * @param App_Model_User    $user   User
     * @return float            Size in bytes
     */
    public function getTotalDiskSpaceByUser ($user) {
        $result = option ('db_conn')
            ->prepare ('SELECT sum(file_size) FROM `'
                .$this->getTableName ()
                .'` WHERE created_by = ?'
                .' AND  available_until >= CURRENT_DATE() ');
        $result->execute (array ($user->id));
        return (float) $result->fetchColumn ();
    }

    /**
     * Return readable disk space used by someone
     *
     * @param App_Model_User    $user   User
     * @return string           Readable size
     */
    public function getReadableTotalDiskSpaceByUser ($user) {
        return $this->getReadableSize ($this->getTotalDiskSpaceByUser ($user));
    }


    /**
     * Return remaining disk space available for user $user
     *
     * @param App_Model_User    $user   User data
     * @return float            Size in bytes or string if $shorthand = true
     */
    public function getRemainingSpaceForUser ($user) {
        return ($this->shorthandSizeToBytes (fz_config_get ('app', 'user_quota'))
              - $this->getTotalDiskSpaceByUser ($user));
    }
    
    /**
     * Transform a size in the shorthand format ('K', 'M', 'G') to bytes
     *
     * @param   string      $size
     * @return  float
     */
    public function shorthandSizeToBytes ($size) {
        $size = str_replace (' ', '', $size);
        switch (strtolower ($size [strlen($size) - 1])) {
            case 'g': $size *= 1024;
            case 'm': $size *= 1024;
            case 'k': $size *= 1024;
        }
        return floatval ($size);
    }

    /**
     * Count the number of files
     * 
     * @return integer number of files
     */
    public function getNumberOfFiles () {
        $sql = 'SELECT COUNT(*) AS count FROM '.$this->getTableName ();
        $res = Fz_Db::findAssocBySQL($sql);
        return $res[0]['count'];
    }

    /**
     * Return disk space used by everybody
     *
     * @return float  Size in bytes
     */
    public function getTotalDiskSpace () {
        $result = option ('db_conn')
            ->prepare ('SELECT sum(file_size) FROM `'
                .$this->getTableName ()
                .'` WHERE available_until >= CURRENT_DATE() ');
        $result->execute ();
        return $this->getReadableSize ($result->fetchColumn ());
    }

    /**
     * Return bytes size to be read by human
     *
     * @param float $bytes
     * @return string
     */
    public function getReadableSize ($bytes, $precision = 2) {
        $units = array(__('B'), __('KB'), __('MB'), __('GB'), __('TB'));
        $pow = floor (($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);

        $bytes /= pow(1024, $pow);

        return round($bytes, $precision) . ' ' . $units[$pow];
    }
}
