<?php

/**
 * fzTableRow
 */
abstract class fzTableRow {

    protected $_updatedColumns  = array ();
    protected $_exists          = false;
    protected $_tableClass      ;

    /**
     * Constructor
     *
     * @return fzTableRow
     */
    public function __construct () {
    }

    /**
     * Table row getter from column name. ex: $row->column_name
     *
     * @param  string   $var    Column name
     * @return mixed            Column value 
     */
    public function __get ($var) {
        return $this->$var;
    }

    /**
     * Table row getter from column name. ex: $row->getColumnName ()
     */
    public function __call ($method, $args) {
        if (method_exists ($this, $method))
            return call_user_func_array (array ($this, $method), $args);

        if (substr ($method, 0, 3) != 'get')
            throw new Exception('Unknown method "'.$method.'"');

        $var = substr ($method, 3);

        $var[0] = strtolower($var[0]);
        $func = create_function('$c', 'return "_" . strtolower($c[1]);');
        $var = preg_replace_callback('/([A-Z])/', $func, $var);

        if (! property_exists ($this, $var))
            throw new Exception('Unknown table attribute "'.$var.'"');

        return $this->$var;
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
        if (method_exists ($this, $method))
            $this->$method ($value);
        else
            $this->$var = $value;

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

        $this->_updatedColumns = array ();
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
        $obj_columns = $this->getUpdatedColumns ();

        $sql =
            "UPDATE `$table` SET " .
            implode (', ', array_map (array ('fzDb','nameEqColonName'), $obj_columns)) .
            ' WHERE id = :id';

        $stmt = $db->prepare ($sql);
        $stmt->bindValue (':id', $this->id);
        foreach ($obj_columns as $column) {
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
            implode (', ', array_map (array ('fzDb','addColon'), $obj_columns)) . ')';

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
     * @return fzTable
     */
    public function getTable () {
        return fzDb::getTable ($this->_tableClass);
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
}

/**
 * fzTable
 *
 * TODO document
 */
abstract class fzTable {

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
     * @return array  Array of fzTableRow
     */
    public function findAll () {
        $sql = "SELECT * FROM ".$this->getTableName ();
        return fzDb::findObjectsBySQL ($sql, $this->getRowClass ());
    }

    /**
     * Retrieve a table row by its id
     *
     * @param   int     $id 
     * @return  fzTableRow
     */
    public function findBydId ($id) {
        $sql = "SELECT * FROM ".$this->getTableName ().' WHERE id = ?';
        return fzDb::findObjectBySQL ($sql, $this->getRowClass (), array ($id));
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

/**
 * fzDb
 */
class fzDb {

    protected static $_tables;

    /**
     * Generic function to retrieve multiple rows and create objects
     *
     * @param   string $sql 
     * @param   string $class_name  Class used for object instanciation (?) 
     * @param   string $params      Params used to prepare the query
     * @param   string $limit       Limit the number of result (default: O = no limit)
     *
     * @return  array   Array of $class_name objects
     */
    public static function findObjectsBySQL ($sql, $class_name = PDO::FETCH_OBJ, $params = array (), $limit = 0) {
        $db = option ('db_conn');

        if ($limit > 1)
            $sql .= ' LIMIT '.$limit;

        $result = array ();
        $stmt = $db->prepare ($sql);
        if ($stmt->execute ($params)) {
            while ($obj = $stmt->fetchObject ($class_name)) {
                $obj->setExist (true);
                $result[] = $obj;
            }
        }

        return ($limit == 1 ?
            (count ($result) > 0 ? $result [0] : null) :
            $result
        );
    }

    /**
     * Generic function to retrieve one row
     *
     * @param   string $sql 
     * @param   string $class_name  Class used for object instanciation (?) 
     * @param   string $params      Params used to prepare the query
     *
     * @return  object  Object of clas $class_name
     */
    public static function findObjectBySQL ($sql, $class_name = PDO::FETCH_OBJ, $params = array ()) {
        return self::findObjectsBySQL ($sql, $class_name, $params, 1);
    }

    /**
     * Return an instance of a table
     *
     * @param   string $tableClass 
     * @return  object 
     */
    public static function getTable ($tableClass) {
        if (! self::$_tables [$tableClass])
            self::$_tables [$tableClass] = new $tableClass ();

        return self::$_tables [$tableClass];
    }

    /**
     * Helper for writing prepared queries
     *
     * @param   string $x 
     * @return  string 
     */
    public static function addColon ($x) { return ':' . $x; }

    /**
     * Helper for writing prepared queries
     *
     * @param   string $x 
     * @return  string 
     */
    public static function nameEqColonName ($x) { return $x . ' = :' . $x; }
}


