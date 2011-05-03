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
 * Fz_Db_Schema
 * Abstract class to represent a table from the database
 */
class Fz_Db_Schema {

    protected $_dbFilesRootDir;

    public function __construct ($dbFilesRootDir) {
        $this->_dbFilesRootDir = $dbFilesRootDir;
    }

    /**
     * Update the database schema to the latest version
     */
    public function migrate () {
        $sql = '';
        $version = $this->getCurrentVersion ();
    
        if ($version === false) {
            $sql = file_get_contents ($this->_dbFilesRootDir.'/schema.sql');
        } else {
            $pattern = '/filez-(([0-9]*)\.([0-9]*)\.([0-9]*)-([0-9]*))\.sql$/';
            $matches = array ();
            foreach ($this->getMigrationScripts () as $file) {
                $matches = array ();
                if (preg_match ($pattern, $file, $matches) === 1) {
                    if (strcmp ($matches [1], $version) > 0)
                        $sql .= file_get_contents ($file);
                }
            }

            // Update filez version
            $sql .= 'UPDATE `fz_info` SET `value`=\''.$matches[1].'\' WHERE `key`=\'db_version\'';
        }

        print_r($sql);
        if (! empty ($sql))
            Fz_Db::getConnection ()->exec ($sql);
    }

    /**
     * Return the current database version
     *
     * @return string or false if database doesn't exist
     */
    public function getCurrentVersion () {
        $sql = 'SELECT table_name '
              .'FROM information_schema.tables '
              .'WHERE table_name=\'fz_file\''
              .'  or  table_name=\'fz_info\''
              .'  or  table_name=\'Fichiers\'';

        $res = Fz_Db::findAssocBySQL($sql);
        if (count ($res) == 0)
            return false;
        else {
            $version = false;
            foreach ($res as $table) {
                if ($table['table_name'] == 'Fichiers') {
                    return '1.2'; // TODO add more check
                } else if ($table['table_name'] == 'fz_file') {
                    $version = '2.0.0';
                } else if ($table['table_name'] == 'fz_info') {
                    return Fz_Db::getTable('Info')->getDatabaseVersion ();
                }
            }
            return $version;
        }
    }

    /**
     * Return the latest schema version available
     *
     * @return string
     */
    public function getLatestVersion () {
        $migrations = $this->getMigrationScripts ();
        return end ($migrations);
    }

    /**
     * Return a list of every database migration script
     *
     * @return array
     */
    protected function getMigrationScripts () {
        return glob ($this->_dbFilesRootDir.'/migrations/filez-*.sql');
    }

    /**
     * Tells if the database schema is too old to be used with the current sources version
     *
     * @return boolean
     */
    public function isOutdated () {
        return strcmp (FZ_VERSION, $this->getCurrentVersion ()) > 0;
    }

}
