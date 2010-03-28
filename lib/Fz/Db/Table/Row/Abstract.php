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
 * Fz_Db_Table_Row_Abstract
 */
abstract class Fz_Db_Table_Row_Abstract {

    protected $_updatedColumns  = array ();
    protected $_data            = array ();
    protected $_exists          = false;
    protected $_tableClass      ;

    /**
     * Constructor
     *
     * @return Fz_Table_Row
     */
    public function __construct ($exists = false) {
        $this->_exists = $exists;
    }

    /**
     * Table row getter from column name. ex: $row->column_name
     *
     * @param  string   $var    Column name
     * @return mixed            Column value
     */
    public function __get ($var) {
        if (array_key_exists ($var, $this->_data))
            return $this->_data [$var];
        else if (in_array ($var, $this->getTable()->getTableColumns ()))
            return null;
        else
            throw new Exception ('Unknown table attribute "'.$var.'"');
    }

    /**
     * Table row getter from column name. ex: $row->getColumnName ()
     */
    public function __call ($method, $args) {
        if (method_exists ($this, $method))
            return call_user_func_array (array ($this, $method), $args);

        $var = substr ($method, 3);
        $var[0] = strtolower ($var[0]);
        $func = create_function ('$c', 'return "_" . strtolower ($c[1]);');
        $var = preg_replace_callback ('/([A-Z])/', $func, $var);

        if (substr ($method, 0, 3) == 'get') {
            return $this->$var;
        } else   if (substr ($method, 0, 3) == 'set') {
            return $this->$var = $args[0]; // TODO check args size & trigger error
        } else {
            throw new Exception ('Unknown method "'.$method.'"');
        }
    }

    /**
     * Table row setter from column name. ex: $row->column_name = 'foo';
     *
     * @param  string $var    Column name
     * @param  mixed  $value  Column value
     * @return mixed          Column value
     */
    public function __set ($var, $value) {
        $method = 'set'.self::camelify ($var);
        $this->_data [$var] = $value;
        $this->_updatedColumns [] = $var;
        return $value;
    }

    /**
     * Transform a string from underscore_format to CamelFormat
     *
     * @param   string $var
     * @return  string
     */
    private static function camelify ($var) {
        return str_replace (' ', '', ucwords (str_replace ('_', ' ', $var)));
    }

    /**
     * Return a string representation of the table row ('id' by default)
     *
     * @return string
     */
    public function __toString () {
        return $this->id;
    }

    /**
     * Return an array reprensentation of the table row.
     * Custom getter will be called if they exist.
     *
     * @return array
     */
    public function toArray () {
        $array = array ();
        foreach ($this->getTableColumns as $column) {
            $method = 'get'.self::camelify ($var);
            $array [$column] = method_exists  ($this, $method) ?
                call_user_method_array (array ($this, $method)) : $this->$var;
        }
        return $array;
    }

    /**
     * Return an array of all updated columns since last select/update
     *
     * @return array Columns name
     */
    public function getUpdatedColumns () {
        return $this->_updatedColumns;
    }

    /**
     * Save a row into the database
     *
     * @return self
     */
    public function save () {
        if ($this->_exists)
            $this->update ();
        else
            $this->insert ();

        $this->resetModifiedColumns();
        $this->_exists = true;

        return $this;
    }

    /**
     * Save an existing row into the database
     *
     * @return self
     */
    protected function update () {
        $db = option ('db_conn');
        $table = $this->getTableName ();
        $columnsName = $this->getUpdatedColumns ();

        if (count ($columnsName) == 0)
            return $this;

        $sql =
            "UPDATE `$table` SET " .
            implode (', ', array_map (array ('Fz_Db','nameEqColonName'), $columnsName)) .
            ' WHERE id = :id';

        fz_log ($sql, FZ_LOG_DEBUG);

        $stmt = $db->prepare ($sql);
        $stmt->bindValue (':id', $this->id);
        foreach ($columnsName as $column) {
            $stmt->bindValue (':' . $column, $this->$column);
        }

        $stmt->execute ();

        return $this;
    }

    /**
     * Save a new row into the database
     *
     * @return self
     */
    protected function insert () {
        $db = option ('db_conn');
        $table = $this->getTableName ();
        $obj_columns = $this->getUpdatedColumns ();

        $sql =
            "INSERT INTO `$table` (" .
            implode (', ', $obj_columns) .
            ') VALUES (' .
            implode (', ', array_map (array ('Fz_Db','addColon'), $obj_columns)) . ')';

        fz_log ($sql, FZ_LOG_DEBUG);

        $stmt = $db->prepare ($sql);
        foreach ($obj_columns as $column) {
            $stmt->bindValue (':' . $column, $this->$column);
        }

        $stmt->execute ();

        return $db->lastInsertId ();
    }

    /**
     * Delete an existing row from the database
     */
    public function delete () {
        $db = option ('db_conn');
        if ($this->_exists === false) return;
        $stmt = $db->prepare ('DELETE FROM `'.$this->getTableName ().'` WHERE id = ?');
        $stmt->execute (array ($this->id));
    }

    /**
     * Return a string representation of the object for debug purpose
     *
     * @return string
     */
    public function debug () {
        echo "\n";
        foreach ($this->getTableColumns () as $c) {
            echo $c.': '.$this->$c."\n";
        }
    }

    /**
     * Return the table name of the current row
     *
     * @return string Table name
     */
    public function getTableName () {
        return $this->getTable ()->getTableName ();
    }

    /**
     * Return the table object of the current row
     *
     * @return Fz_Table_Abstract
     */
    public function getTable () {
        return Fz_Db::getTable ($this->_tableClass);
    }

    /**
     * Return all columns name from the current table row
     *
     * @return array
     */
    public function getTableColumns () {
        return $this->getTable ()->getTableColumns ();
    }

    /**
     * Indicate whether the object exists or not
     *
     * @param boolean
     */
    protected function setExist ($exists) {
        $this->_exists = $exists;
    }

    /**
     * 
     */
    public function resetModifiedColumns () {
        $this->_updatedColumns = array ();
    }
}
