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
 * @password int     $id
 * @password string  $username
 * @password string  $password
 * @password string  $salt
 * @password string  $firstname
 * @password string  $lastname
 * @password string  $email
 * @password boolean $is_admin
 * @property int     $created_at TIMESTAMP
 */
class App_Model_User extends Fz_Db_Table_Row_Abstract {

    protected $_tableClass = 'App_Model_DbTable_User';

    /**
     * Constructor
     *
     * @param boolean $exists   Whether the object exists in database or not.
     *                          If false a ID will be automatically choosen
     */
    public function __construct ($exists = false) {
        parent::__construct ($exists);

        if ($exists == false)
            $this->salt = sha1 (uniqid (mt_rand (), true));
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
    public function getFiles ($includeExpired = false) {
        return Fz_Db::getTable('File')->findByOwnerOrderByUploadDateDesc ($this);
        // TODO handle the $includeExpired parameter
    }
    
    /**
     * Function used to encrypt the password
     *
     * @param string password
     */
    public function setPassword ($password) {
        $algorithm = fz_config_get ('user_factory_options', 'db_password_algorithm');
        $this->password = $password;

        $sql = null;
        if ($algorithm === null) {
            $sql = 'SHA1(CONCAT(:salt,:password))';
            $this->_updatedColumns [] = 'salt'; // to force PDO::bindValue when updating
        }
        else if ($algorithm == 'MD5') {
            $sql = 'MD5(:password)';
        }
        else if ($algorithm == 'SHA1') {
            $sql = 'SHA1(:password)';
        }
        else if (is_callable ($algorithm)) {
            if (strstr ($algorithm, '::') !== false)
                $algorithm = explode ('::', $algorithm);
            $sql = Fz_Db::getConnection ()->quote (call_user_func ($algorithm, $password));
        }
        else {
            $sql = $algorithm; // Plain SQL
        }

        if ($sql !== null)
            $this->setColumnModifier ('password', $sql);
    }

    /**
     * Function used to check if a new or updated user is valid
     *
     * @param $action the action is to update or to create a user. Value 'new' or 'update'.
     * @return array (attribut => error message)
     */
    public function isValid ( $action = 'new' ) {
        $return = array();
        if (! filter_var ($this->email, FILTER_VALIDATE_EMAIL) ) {
          $return['email']=__r('"%s%" is not a valid email.',array('s'=>$this->email));
        }
        if ( null == $this->username ) {
          $return['username']=__('The username should not be blank');
        }
        if ( 4 > strlen($this->password) ) {
          $return['password']=__('The password is too short.');
        }
        if ( 'new' == $action ) {
            if ($this->getTable()->findByUsername ($this->username) !== null) {
               $return['username']=__('This username is already used.');
            }
            if ($this->getTable()->findByEmail ($this->email) !== null) {
               $return['email']=__('This email is already used.');
            }
        } elseif ( 'update' == $action ) {
            if ( null != $this->getTable()->findByUsername ($this->username) 
              && params ('id') != $this->getTable()->findByUsername ($this->username)->getId() ) {
               $return['username']=__('This username belongs to another user.');
            }
            if ( null != $this->getTable()->findByEmail ($this->email) 
              && params ('id') != $this->getTable()->findByEmail ($this->email)->getId() ) {
               $return['email']=__('This email belongs to another user.');
            }
        }
        return $return;
    }

    /**
     * Function used to get the user disk usage
     *
     * @return disk space used by the user
     */
    public function getDiskUsage () {
        return bytesToShorthand( Fz_Db::getTable('File')->getTotalDiskSpaceByUser ($this));
    }

}
