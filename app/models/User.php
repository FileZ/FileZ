<?php 
/**
 * Copyright 2010  Université d'Avignon et des Pays de Vaucluse 
 * email: gpl@univ-avignon.fr
 *
 * This file is part of Filez.
 *
 * Filez is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Filez is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Filez.  If not, see <http://www.gnu.org/licenses/>.
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
    
}

