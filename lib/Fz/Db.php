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
 * Fz_Db
 */
class Fz_Db {

    protected static $_tables = array ();

    /**
     * Generic function to retrieve multiple rows and create objects
     *
     * @param   string $sql 
     * @param   string $className  Class used for object instanciation (?) 
     * @param   string $params      Params used to prepare the query
     * @param   string $limit       Limit the number of result (default: O = no limit)
     *
     * @return  array   Array of $className objects
     */
    public static function findObjectsBySQL ($sql, $className = PDO::FETCH_OBJ, $params = array (), $limit = 0) {
        $db = self::getConnection ();

        if ($limit > 1)
            $sql .= ' LIMIT '.$limit;

        $result = array ();
        $stmt = $db->prepare ($sql);
        $stmt->execute ((array) $params);
        while ($obj = $stmt->fetchObject ($className, array (true))) {
            if (method_exists($obj, ('resetModifiedColumns')))
                $obj->resetModifiedColumns ();
            $result[] = $obj;
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
     * @param   string $className  Class used for object instanciation (?)
     * @param   string $params      Params used to prepare the query
     *
     * @return  object  Object of clas $className
     */
    public static function findObjectBySQL ($sql, $className = PDO::FETCH_OBJ, $params = array ()) {
        return self::findObjectsBySQL ($sql, $className, $params, 1);
    }

    /**
     * Generic function to retrieve multiple rows as an array
     *
     * @param   string $sql
     * @param   string $className  Class used for object instanciation (?)
     * @param   string $params      Params used to prepare the query
     * @param   string $limit       Limit the number of result (default: O = no limit)
     *
     * @return  array   Array of $className objects
     */
    public static function findAssocBySQL ($sql, $params = array (), $limit = 0) {
        $db = self::getConnection ();

        if ($limit > 1)
            $sql .= ' LIMIT '.$limit;

        $result = array ();
        $stmt = $db->prepare ($sql);
        $stmt->execute ($params);
        while ($row = $stmt->fetch (PDO::FETCH_ASSOC))
            $result[] = $row;

        return ($limit == 1 ?
            (count ($result) > 0 ? $result [0] : null) :
            $result
        );
    }


    /**
     * Return an instance of a table
     *
     * @param   string $table
     * @return  object 
     */
    public static function getTable ($table) {
        if (! array_key_exists($table, self::$_tables)) {
            $dialect = fz_config_get('db', 'db_dialect');
            $prefix = 'App_Model_DbTable_';
            $tableClass = substr ($table, 0, strlen ($prefix)) == $prefix ?
                                $table : ($prefix.$table);
            $tableClass = "$tableClass$dialect";
            self::$_tables [$table] = new $tableClass ();
        }

        return self::$_tables [$table];
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

    /**
     * Helper for writing prepared queries
     *
     * @param   string $x 
     * @return  string 
     */
    public static function nameEqSql (&$sql, $columnName) { $sql = $columnName . ' = ' . $sql; }

    public static function getConnection () {
        if (($conn = option ('db_conn')) !== null)
            return $conn;
        else
            throw new Exception ('Unexisting database connection');
    }
}


