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
 * Fz_Db
 */
class Fz_Db {

    protected static $_tables = array ();

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
        $db = self::getConnection ();

        if ($limit > 1)
            $sql .= ' LIMIT '.$limit;

        $result = array ();
        $stmt = $db->prepare ($sql);
        $stmt->execute ($params);
        while ($obj = $stmt->fetchObject ($class_name, array (true)))
            $result[] = $obj;

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
     * Generic function to retrieve multiple rows as an array
     *
     * @param   string $sql
     * @param   string $class_name  Class used for object instanciation (?)
     * @param   string $params      Params used to prepare the query
     * @param   string $limit       Limit the number of result (default: O = no limit)
     *
     * @return  array   Array of $class_name objects
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
            $prefix = 'App_Model_DbTable_';
            $tableClass = substr ($table, 0, strlen ($prefix)) == $prefix ?
                                $table : ($prefix.$table);
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

    public static function getConnection () {
        if (($conn = option ('db_conn')) !== null)
            return $conn;
        else
            throw new Exception ('Unexisting database connection');
    }
}


