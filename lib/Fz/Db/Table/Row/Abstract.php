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
 * Fz_Db_Table_Row_Abstract
 */
abstract class Fz_Db_Table_Row_Abstract {

    protected $_updatedColumns  = array ();
    protected $_sqlModifiers    = array ();
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
        if (array_key_exists ($var, $this->_data) && $this->_data [$var] == $value)
            return $value;

        $this->_data [$var] = $value;
        $this->_updatedColumns [] = $var;
        return $value;
    }

    /**
     *
     * @param  string $var    Column name
     * @return boolean
     */
    public function __isset ($var) {
        return in_array ($var, $this->getTable()->getTableColumns ());
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
        return array_unique ($this->_updatedColumns);
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
        $sqlModifiersColumnsName = array_keys ($this->_sqlModifiers);
        $unmodifiedColumns =  array_diff ($columnsName, $sqlModifiersColumnsName);

        if (count ($columnsName) == 0 && count ($sqlModifiersColumnsName) == 0)
            return $this;

        array_walk ($this->_sqlModifiers, array ('Fz_Db','nameEqSql'));
        $sql = "UPDATE `$table` SET " .
            implode (', ', array_merge (array_map  (array ('Fz_Db','nameEqColonName'),$unmodifiedColumns), $this->_sqlModifiers)) .
            ' WHERE id = :id';

        fz_log ($sql, FZ_LOG_DEBUG);

        $stmt = $db->prepare ($sql);
        $stmt->bindValue (':id', $this->id);
        $this->bindUpdatedColumnsValues ($stmt);
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
        $columnsName = $this->getUpdatedColumns ();
        $sqlModifiersColumnsName = array_keys ($this->_sqlModifiers);
        $unmodifiedColumns =  array_diff ($columnsName, $sqlModifiersColumnsName);

        $sql =
            "INSERT INTO `$table` (" .
            implode (', ', array_merge ($unmodifiedColumns, $sqlModifiersColumnsName)) . // reorder columns
            ') VALUES (' .
            implode (', ', array_merge (array_map (array ('Fz_Db','addColon'), $unmodifiedColumns), $this->_sqlModifiers)) . ')';

        fz_log ($sql, FZ_LOG_DEBUG);

        $stmt = $db->prepare ($sql);
        $this->bindUpdatedColumnsValues ($stmt);
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
     * Bind updated object values to the prepared statement
     *
     * @param PDO_Statement $stmt
     * @return PDO_Statement
     */
    private function bindUpdatedColumnsValues ($stmt) {
        foreach ($this->getUpdatedColumns () as $column)
            $stmt->bindValue (':'.$column, $this->_data[$column]);

        return $stmt;
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
     * Reset the updated columns references
     */
    public function resetModifiedColumns () {
        $this->_updatedColumns = array ();
    }

    /**
     * Use this method when you want to execute your custom sql to alter the 
     * value of a column.
     *
     * To make a reference to the user value, for the password column for
     * example, use :password in your sql command.
     *
     * Ex: $user->setColumnModifier ('password', 'SHA1(:password)');
     *
     * @param string $columnName
     * @param string $sql
     */
    public function setColumnModifier ($columnName, $sql) {
        $this->_sqlModifiers [$columnName] = $sql;
    }
}
