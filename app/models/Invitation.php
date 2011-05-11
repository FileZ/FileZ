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
 * @property string  $file_name
 * @property string  $available_from    DATE
 * @property string  $available_until   DATE
 * @property int     $created_by
 * @property int     $created_at        TIMESTAMP
 */

class App_Model_Invitation extends Fz_Db_Table_Row_Abstract {

    protected $_tableClass = 'App_Model_DbTable_Invitation';

    /**
     * Constructor
     *
     * @param boolean $exists   Whether the object exists in database or not.
     *                          If false a ID will be automatically choosen
     */
    public function __construct ($exists = false) {
        parent::__construct ($exists);
    }

    /**
     * Return the string representation of the file object (file name)
     * @return string
     */
    public function __toString () {
        return $this->firstname.' '.$this->lastname;
    }

    /**
     * Return every file uploaded by the user (
     *
     * @param  boolean  $expired    Are the expired file included ?
     * @return array                Array of App_Model_File
     */
    public function getInvitations ($includeExpired = false) {
        return Fz_Db::getTable('Invitation')->findByOwnerOrderByUploadDateDesc ($this);
        // TODO handle the $includeExpired parameter
    }
}
