<?php 

/**
 * @property boolean $del_notif_sent
 * @property string  $file_name
 * @property string  $uploader_email
 * @property int     $file_size
 * @property string  $available_from    DATE
 * @property string  $available_until   DATE
 * @property int     $download_count
 * @property string  $comment
 * @property boolean $notify_uploader
 * @property string  $uploader_uid
 * @property int     $extends_count
 * @property int     $created_at        TIMESTAMP
 */
class App_Model_File extends Fz_Db_Table_Row_Abstract {

    protected $_tableClass = 'App_Model_DbTable_File';

    public function getHash () {
        return $this->getTable ()->idToHash ($this->id);
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
        return new Zend_Date ($this->available_until, Zend_Date::ISO_8601);
    }

    public function getAvailableFrom () {
        return new Zend_Date ($this->available_from, Zend_Date::ISO_8601);
    }

    public function getCreatedAt () {
        return new Zend_Date ($this->created_at, Zend_Date::ISO_8601);
    }

    protected function setAvailableUntil ($date) {
        $this->available_until = $date instanceof Zend_Date ?
            $date->get (Zend_Date::ISO_8601) : $date;
    }

    protected function setAvailableFrom ($date) {
        $this->available_from = $date instanceof Zend_Date ?
            $date->get (Zend_Date::ISO_8601) : $date;
    }

    protected function setCreatedAt ($date) {
        $this->created_at = $date instanceof Zend_Date ?
            $date->get (Zend_Date::ISO_8601) : $date;
    }

    public function getDownloadUrl () {
        return 'http://'.$_SERVER["SERVER_NAME"].url_for ('/').$this->getHash ();
    }

    /**
     *
     */
    public function getReadableFileSize ($precision = 2) {
        $units = array('B', 'KB', 'MB', 'GB', 'TB'); // TODO i18n

        $bytes = $this->file_size;
        $pow = floor (($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);

        $bytes /= pow(1024, $pow);

        return round($bytes, $precision) . ' ' . $units[$pow];
    }

    /**
     * Initialise file attributes from an array.
     * 
     * @param array $file       Associative array with the following keys :
     *                              - name
     *                              - size
     * @return void
     *
     */
    public function setFileInfo (array $file) {
        $this->file_name = $file ['name'];
        $this->file_size = $file ['size'];
    }

    /**
     * Set the uploader of the file from an associative array containing
     * 'id' & 'email' keys.
     *
     * @param array $user
     */
    public function setUploader (array $user) {
        $this->uploader_uid     = $user ['id'];
        $this->uploader_email   = $user ['email'];
    }
    /**
     * Return file uploader info 
     *
     * @return array $user
     */
    public function getUploader () {
        return option ('userFactory')->findById ($user ['id']);

        // TODO retrieve user from database if he has been invited
    }

    /**
     * Checks if the user passed is the owner of the file
     *
     * @param array $user
     * @return boolean
     */
    public function isOwner ($user) {
        return ($file->uploader_email == $user ['email'] // check for invited users
         || $file->uploader_uid   == $user ['id']); // or registered users
    }

    /**
     * Checks if the file is available for download now
     *
     * @return boolean
     */
    public function isAvailable () {
        $now = new Zend_Date ();
        return ($this->getAvailableFrom()->compare ($now) <= 0
             && $this->getAvailableUntil()->compare ($now) >= 0);

    }
}
