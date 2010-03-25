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
 * Fz_Db_Table_Abstract
 * Abstract class to represent a table from the database
 */
abstract class Fz_Db_Table_Abstract {

    protected $_rowClass;
    protected $_name;
    protected $_columns;

    /**
     * Return current table name
     *
     * @return  string
     */
    public function getTableName () {
        return $this->_name;
    }

    /**
     * Return all columns name from the current table
     *
     * @return  array table columns
     */
    public function getTableColumns () {
        return $this->_columns;
    }

    /**
     * Return the row class used
     *
     * @return  string
     */
    public function getRowClass () {
        return $this->_rowClass;
    }

    /**
     * Retrieve all rows of the current table
     *
     * @return array  Array of Fz_Table_Row_Abstrat
     */
    public function findAll () {
        $sql = "SELECT * FROM ".$this->getTableName ();
        return Fz_Db::findObjectsBySQL ($sql, $this->getRowClass ());
    }

    /**
     * Retrieve a table row by its id
     *
     * @param   int     $id
     * @return  Fz_Table_Row_Abstrat
     */
    public function findById ($id) {
        $sql = "SELECT * FROM ".$this->getTableName ().' WHERE id = ?';
        return $this->findOneBySQL ($sql, array ($id));
    }

    /**
     * Retrieve table rows from a sql query
     *
     * @param   string  $sql
     * @return  array of Fz_Table_Row_Abstrat
     */
    public function findBySql ($sql, $data = array ()) {
        return Fz_Db::findObjectsBySQL ($sql, $this->getRowClass (), $data);
    }

    /**
     * Retrieve a table row from a sql query
     *
     * @param   string  $sql
     * @return  Fz_Table_Row_Abstrat or null
     */
    public function findOneBySql ($sql, $data = array ()) {
        return Fz_Db::findObjectBySQL ($sql, $this->getRowClass (), $data);
    }

    /**
     * Return true or false wheter a row of id $id exists or not.
     *
     * @param   int     $id
     * @return  boolean
     */
    public function rowExists ($id) {
        $db   = option ('db_conn');
        $sql  = 'SELECT id FROM `'.$this->getTableName ().'` WHERE id = ?';
        $stmt = $db->prepare ($sql);
        $stmt->execute (array ($id));

        return $stmt->fetchColumn () === false ? false : true;
    }

    /**
     * Return the class of the current table
     *
     * @return  string
     */
    protected function getClass () {
        return get_class ($this);
    }
}
