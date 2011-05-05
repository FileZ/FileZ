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
 * General Controller used for administratives tasks
 */
class App_Controller_Admin extends Fz_Controller {

    public function init () {
        layout ('layout'.DIRECTORY_SEPARATOR.'admin.html.php');
    }

    public function indexAction () {
        $this->secure ('admin');
        set ('numberOfUsers', Fz_Db::getTable ('User')->getNumberOfUsers() );
        set ('numberOfFiles', Fz_Db::getTable ('File')->getNumberOfFiles() );
        return html ('admin/index.php');
    }

    /**
     * Action called to manage files
     * List files, display stats.
     */
    public function filesAction () {
        $this->secure ('admin');        
        set ('files', Fz_Db::getTable ('File')->findAll ()); // TODO paginat
        return html ('file/index.php');
        //TODO
    }
    /**
     * Action called to manage the config
     * List the config settings.
     */
    public function configAction () {
        $this->secure ('admin');
        return html ('admin/index.php');
        //TODO
    }

    /**
     * Action called to clean expired files and send mail to those who will be
     * in the next 2 days. This action is meant to be called from a cron script.
     * It should not respond any output except PHP execution errors. Everything
     * else is logged in 'filez-cron.log' and 'filez-cron-errors.log' files in
     * the configured log directory.
     */
    public function checkFilesAction () {
        // Delete files whose lifetime expired
        Fz_Db::getTable('File')->deleteExpiredFiles ();

        // Send mail for files which will be deleted in less than 2 days
        $days = fz_config_get('cron', 'days_before_expiration_mail');
        foreach (Fz_Db::getTable('File')->findFilesToBeDeleted ($days) as $file) {
            // TODO improve the SQL command to retrieve uploader email at the same time
            //      to reduce the # of request made by notifyDeletionByEmail 
            if ($file->notify_uploader) {
                $file->del_notif_sent = true;
                $file->save ();
                $this->notifyDeletionByEmail ($file);
            }
        }
    }

    /**
     * Notify the owner of the file passed as parameter that its file is going
     * to be deleted
     *
     * @param App_Model_File $file
     */
    private function notifyDeletionByEmail (App_Model_File $file) {
        try {
            $mail = $this->createMail();
            $user = $file->getUploader ();
            $subject = __r('[FileZ] Your file "%file_name%" is going to be deleted', array (
                'file_name' => $file->file_name));
            $msg = __r('email_delete_notif (%file_name%, %file_url%, %filez_url%, %available_until%)', array(
                'file_name'       => $file->file_name,
                'file_url'        => $file->getDownloadUrl(),
                'filez_url'       => url_for('/'),
                'available_until' => $file->getAvailableUntil()->toString (Zend_Date::DATE_FULL),
            ));
            $mail->setBodyText ($msg);
            $mail->setSubject  ($subject);
            $mail->addTo ($user->email);
            $mail->send ();

            fz_log ('Delete notification sent to '.$user->email, FZ_LOG_CRON);
        }
        catch (Exception $e) {
            fz_log ('Can\'t send email to '.$user->email
                .' file_id:'.$file->id, FZ_LOG_CRON_ERROR);
        }
    }
}
