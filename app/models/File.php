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

    /**
     * Constructor
     *
     * @param boolean $exists   Whether the object exists in database or not.
     *                          If false a ID will be automatically choosen
     */
    public function __construct ($exists = false) {
        parent::__construct ($exists);
        if (! $exists)
            $this->id = $this->getTable ()->getFreeId ();
    }

    /**
     * Return the unique hash code of the file
     * 
     * @return string
     */
    public function getHash () {
        return $this->getTable ()->idToHash ($this->id);
    }

    /**
     * Return the string representation of the file object (file name)
     * @return string
     */
    public function __toString () {
        return $this->file_name;
    }

    /**
     * Return the available from date
     * @return Zend_Date
     */
    public function getAvailableUntil () {
        return new Zend_Date ($this->available_until, Zend_Date::ISO_8601);
    }

    /**
     * Return the available from date
     * @return Zend_Date
     */
    public function getAvailableFrom () {
        return new Zend_Date ($this->available_from, Zend_Date::ISO_8601);
    }

    /**
     * Return the created at date
     * @return Zend_Date
     */
    public function getCreatedAt () {
        return new Zend_Date ($this->created_at, Zend_Date::ISO_8601);
    }

    /**
     * Set the avaulable until date.
     * If $date is a Zend_Date, it will be converted to the correct database format
     *
     * @param mixed $date       String or Zend_Date
     */
    protected function setAvailableUntil ($date) {
        $this->available_until = $date instanceof Zend_Date ?
            $date->get (Zend_Date::ISO_8601) : $date;
    }

    /**
     * Set the available date.
     * If $date is a Zend_Date, it will be converted to the correct database format
     *
     * @param mixed $date       String or Zend_Date
     */
    protected function setAvailableFrom ($date) {
        $this->available_from = $date instanceof Zend_Date ?
            $date->get (Zend_Date::ISO_8601) : $date;
    }

    /**
     * Set the created date.
     * If $date is a Zend_Date, it will be converted to the correct database format
     *
     * @param mixed $date       String or Zend_Date
     */
    protected function setCreatedAt ($date) {
        $this->created_at = $date instanceof Zend_Date ?
            $date->get (Zend_Date::ISO_8601) : $date;
    }

    /**
     * Return the absolute URL to the file
     * 
     * @return string
     */
    public function getDownloadUrl () {
        // TODO passer en https si nécessaire
        return 'http://'.$_SERVER["SERVER_NAME"].url_for ('/').$this->getHash ();
    }

    /**
     * Return file size to be read by human
     *
     * @return string
     */
    public function getReadableFileSize ($precision = 2) {
        $units = array(__('B'), __('KB'), __('MB'), __('GB'), __('TB'));

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
        return option ('userFactory')->findById ($this->uploader_uid);

        // TODO retrieve user from database if he has been invited
    }

    /**
     * Checks if the user passed is the owner of the file
     *
     * @param array $user
     * @return boolean
     */
    public function isOwner ($user) {
        return ($this->uploader_email == $user ['email'] // check for invited users
             || $this->uploader_uid   == $user ['id']); // or registered users
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

    /**
     * Delete the file from disk and database
     * 
     * @return void
     */
    public function delete () {
        $this->deleteFromDisk();
        return parent::delete();
    }

    /**
     * Delete the file from disk
     *
     */
    public function deleteFromDisk () {
        return unlink ($this->getOnDiskLocation ());
    }

    /**
     * Move upoaded file
     *
     * @param array     uploaded file informations from $_FILES
     * @return boolean  whether the file was successfully moved or not.
     */
    public function moveUploadedFile ($uploadedFile) {
        if (is_uploaded_file($uploadedFile ['tmp_name'])
            && move_uploaded_file ($uploadedFile ['tmp_name'],
                                   $this->getOnDiskLocation ())) {
            return true;
        } else {
            fz_log('Can\'t move the uploaded file to its final destination "'
                    .$this->getOnDiskLocation (), FZ_LOG_ERROR);
            return false;
        }

    }

    /**
     * Return the absolute location of the file on disk
     * 
     * @return string
     */
    public function getOnDiskLocation () {
        if (! empty ($this->nom_physique) && fz_config_get('app', 'filez1_compat'))
            return fz_config_get ('app', 'upload_dir').'/'.$this->nom_physique;
        else
            return fz_config_get ('app', 'upload_dir').'/'.$this->getHash();
    }

    /**
     * Extend file lifetime to one more day
     */
    public function extendLifetime () {
        $this->setAvailableUntil ($this->getAvailableUntil()->addDay(1));
        $this->extends_count = $this->extends_count + 1;
    }

    /**
     * Set the password for the file. This function use the creation date to salt
     * the password hash meaning that 'created_at' must have been already set
     * before setting the password.
     * 
     * @param string    $secret
     * @return void
     */
    public function setPassword ($secret) {
        $this->password = sha1 ($this->created_at.$secret);
    }

    /**
     * Check if the password provided as parameter is valid
     *
     * @param string    $secret
     * @return boolean              true if password is correct, false else
     */
    public function checkPassword ($secret) {
        return ($this->password == sha1 ($this->created_at.$secret));
    }
}
