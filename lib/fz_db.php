<?php

abstract class fzTableRow {

    protected $updatedColumns   = array ();
    protected $exists           = false;

    public abstract function getTableName ();
    public abstract function getTableColumns ();

    public function __construct ($exists = false) {
        $this->exists = $exists;
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

        $this->updatedColumns [] = $var;
        return $value;
    }

    private static function camelify ($var) {
        return str_replace (' ', '', ucwords (str_replace ('_', ' ', $var)));
    }

    public function __toString () {
        return $this->id;
    }

    public function getUpdatedColumns () {
        return $this->updatedColumns;
    }

    public function save () {
        if ($exists)
            $this->update ();
        else
            $this->insert ();

        $this->updatedColumns = array ();
        $this->exists = true;
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
        if ($this->exists === false) return;
        $stmt = $db->prepare ('DELETE FROM `'.$this->getTableName ().'` WHERE id = ?');
        $stmt->execute (array ($this->id));
    }

    public function debug () {
        echo "\n";
        foreach ($this->getTableColumns () as $c) {
            echo $c.': '.$this->$c."\n";
        }
    }
}


abstract class fzTable {

// TODO

}

function fz_db_find_objects_by_sql ($sql = '', $class_name = PDO::FETCH_OBJ, $params = array ()) {
    $db = option ('db_conn');

    $result = array ();
    $stmt = $db->prepare ($sql);
    if ($stmt->execute ($params)) {
        while ($obj = $stmt->fetchObject ($class_name, array (true))) {
            $result[] = $obj;
        }
    }
    return $result;
}

function fz_db_find_object_by_sql ($sql = '', $class_name = PDO::FETCH_OBJ, $params = array ()) {
    $db = option ('db_conn');

    $stmt = $db->prepare ($sql);
    if ($stmt->execute ($params) && $obj = $stmt->fetchObject ($class_name, array (true))) {
        return $obj;
    }
    return null;
}

function fz_db_make_model_object ($params, $obj = null) {
    if (is_null ($obj)) {
        $obj = new stdClass ();
    }
    foreach ($params as $key => $value) {
        $obj->$key = $value;
    }
    return $obj;
}

function fz_db_delete_object_by_id ($obj_id, $table) {
    $db = option ('db_conn');

    $stmt = $db->prepare ("DELETE FROM `$table` WHERE id = ?");
    $stmt->execute (array ($obj_id));
}

function fz_db_add_colon ($x) { return ':' . $x; };


function fz_db_name_eq_colon_name ($x) { return $x . ' = :' . $x; };


function fz_db_id_exists ($id, $table) {
    $db   = option ('db_conn');
    $sql  = 'SELECT id FROM `'.$table.'` WHERE id = ?';
    $stmt = $db->prepare ($sql);
    $stmt->execute (array ($id));
    return $stmt->fetchColumn () === false ? false : true;
}

