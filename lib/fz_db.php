<?php

/**
 * TODO Documented this
 * 
 */
abstract class fzTableRow {

    protected $_updatedColumns  = array ();
    protected $_exists          = false;
    protected $_tableClass      ;

    public function __construct ($exists = false) {
        $this->_exists = $exists;
    }

    public function __get ($var) {
        return $this->$var;
    }

    public function __set ($var, $value) {
        $method = 'set'.self::camelify ($var);
        if (method_exists ($this, $method))
            $this->$method ($value);
        else
            $this->$var = $value;

        $this->_updatedColumns [] = $var;
        return $value;
    }

    private static function camelify ($var) {
        return str_replace (' ', '', ucwords (str_replace ('_', ' ', $var)));
    }

    public function __toString () {
        return $this->id;
    }

    public function getUpdatedColumns () {
        return $this->_updatedColumns;
    }

    public function save () {
        if ($this->_exists)
            $this->update ();
        else
            $this->insert ();

        $this->_updatedColumns = array ();
        $this->_exists = true;
    }

    protected function update () {
        $db = option ('db_conn');
        $table = $this->getTableName ();
        $obj_columns = $this->getUpdatedColumns ();

        $sql =
            "UPDATE `$table` SET " .
            implode (', ', array_map ('fz_db_name_eq_colon_name', $obj_columns)) .
            ' WHERE id = :id';

        $stmt = $db->prepare ($sql);
        $stmt->bindValue (':id', $this->id);
        foreach ($obj_columns as $column) {
            $stmt->bindValue (':' . $column, $this->$column);
        }

        $stmt->execute ();
    }

    protected function insert () {
        $db = option ('db_conn');
        $table = $this->getTableName ();
        $obj_columns = $this->getUpdatedColumns ();

        $sql =
            "INSERT INTO `$table` (" .
            implode (', ', $obj_columns) .
            ') VALUES (' .
            implode (', ', array_map ('fz_db_add_colon', $obj_columns)) . ')';

        $stmt = $db->prepare ($sql);
        foreach ($obj_columns as $column) {
            $stmt->bindValue (':' . $column, $this->$column);
        }

        $stmt->execute ();
        return $db->lastInsertId ();
    }

    public function delete () {
        $db = option ('db_conn');
        if ($this->_exists === false) return;
        $stmt = $db->prepare ('DELETE FROM `'.$this->getTableName ().'` WHERE id = ?');
        $stmt->execute (array ($this->id));
    }

    public function debug () {
        echo "\n";
        foreach ($this->getTableColumns () as $c) {
            echo $c.': '.$this->$c."\n";
        }
    }

    public function getTableName () {
        return $this->getTable ()->getTableName ();
    }

    public function getTable () {
        return fzDb::getTable ($this->_tableClass);
    }

    public function getTableColumns () {
        return $this->getTable ()->getTableColumns ();
    }
}


abstract class fzTable {

    protected $_rowClass;
    protected $_name;
    protected $_columns;

    public function getTableName () {
        return $this->_name;
    }

    public function getTableColumns () {
        return $this->_columns;
    }

    public function getRowClass () {
        return $this->_rowClass;
    }

    public function findAll () {
        $sql = "SELECT * FROM ".$this->getTableName ();
        return fzDb::findObjectsBySQL ($sql, $this->getRowClass ());
    }

    public function findBydId ($id) {
        $sql = "SELECT * FROM ".$this->getTableName ().' WHERE id = ?';
        return fzDb::findObjectBySQL ($sql, $this->getRowClass (), array ($id));
    }

    public function rowExists ($id) {
        $db   = option ('db_conn');
        $sql  = 'SELECT id FROM `'.$this->getTableName ().'` WHERE id = ?';
        $stmt = $db->prepare ($sql);
        $stmt->execute (array ($id));
        return $stmt->fetchColumn () === false ? false : true;
    }

    protected function getClass () {
        return get_class ($this);
    }
}

class fzDb {

    protected static $_tables;

    public static function findObjectsBySQL ($sql, $class_name = PDO::FETCH_OBJ, $params = array (), $limit = 0) {
        $db = option ('db_conn');

        if ($limit > 1)
            $sql .= ' LIMIT '.$limit;

        $result = array ();
        $stmt = $db->prepare ($sql);
        if ($stmt->execute ($params)) {
            while ($obj = $stmt->fetchObject ($class_name, array (true))) {
                $result[] = $obj;
            }
        }

        return ($limit == 1 ?
            (count ($result) > 0 ? $result [0] : null) :
            $result
        );
    }

    public static function findObjectBySQL ($sql, $class_name = PDO::FETCH_OBJ, $params = array ()) {
        return self::findObjectsBySQL ($sql, $class_name, $params, 1);
    }

    public static function getTable ($tableClass) {
        if (! self::$_tables [$tableClass])
            self::$_tables [$tableClass] = new $tableClass ();

        return self::$_tables [$tableClass];
    }

}

function fz_db_add_colon ($x) { return ':' . $x; };


function fz_db_name_eq_colon_name ($x) { return $x . ' = :' . $x; };



