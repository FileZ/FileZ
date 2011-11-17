<?php
/**
 * Copyright 2011 Matthieu Patou <mat@matws.net>
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

class App_Model_DbTable_FileSqlite extends App_Model_DbTable_File {
    /**
     * Return all file owned by $uid which are available (not deleted)
     *
     * @param string $uid
     * @return array of App_Model_File
     */
    public function findByOwnerOrderByUploadDateDesc ($uid) {
        $sql = 'SELECT * FROM '.$this->getTableName ()
              .' WHERE uploader_uid=:uid '
              .' AND  available_until >= CURRENT_TIMESTAMP'
              .' ORDER BY created_at DESC';
        return $this->findBySql ($sql, array (':uid' => $uid));
    }

    /**
     * Delete files whose lifetime expired
     */
    public function deleteExpiredFiles () {
        $select = 'SELECT * FROM '.$this->getTableName ();
        $where  = ' WHERE available_until<CURRENT_TIMESTAMP';
        foreach ($this->findBySql ($select.$where) as $file) {
            if ($file->deleteFromDisk () === true) {
                fz_log ('Deleted file "'.$file->getOnDiskLocation ().'"',
                        FZ_LOG_CRON);
            } else {
                fz_log ('Failed deleting file "'.$file->getOnDiskLocation ().'"',
                        FZ_LOG_CRON_ERROR);
            }
        }
        option ('db_conn')->exec ('DELETE FROM '.$this->getTableName ().$where);
    }

    /**
     * Return files which will be deleted within X days and where uploader wants
     * to be notified but hasn't been yet
     * 
     * @param integer   $days   Number of days before being deleted
     * @return App_Model_File
     */
    public function findFilesToBeDeleted ($days = 2) {
        $sql = 'SELECT * FROM '.$this->getTableName ()
              .' WHERE available_until BETWEEN CURRENT_TIMESTAMP'
              .'AND DATE_ADD(\'now\',\'+'.$days.' day\') '
              .'AND del_notif_sent=0 AND notify_uploader=1';

        return $this->findBySql ($sql);
    }

    /**
     * Return disk space used by someone
     *
     * @param array     $user   User data
     * @return float            Size in bytes
     */
    public function getTotalDiskSpaceByUser ($user) {
        $result = option ('db_conn')
            ->prepare ('SELECT sum(file_size) FROM `'
                .$this->getTableName ()
                .'` WHERE uploader_email = ?'
                .' AND  available_until >= CURRENT_TIMESTAMP');
        $result->execute (array ($user['email']));
        return (float) $result->fetchColumn ();
    }
}
