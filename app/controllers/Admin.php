<?php

/**
 * Controller used for administratives tasks
 */
class App_Controller_Admin extends Fz_Controller {

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
            $subject = __r('[FileZ] your file "%file_name%" your file is going to be deleted', array (
                'file_name' => $file->file_name));
            $msg = __r('email_delete_notif (%file_name%, %file_url%, %filez_url%)', array(
                'file_name' => $file->file_name,
                'file_url'  => $file->getDownloadUrl(),
                'filez_url' => url_for('/'),
            ));
            $mail->setBodyText ($msg);
            $mail->setSubject  ($subject);
            $mail->addTo ($file->uploader_email);
            $mail->send ();

            fz_log ('Delete notification sent to '.$file->uploader_email, FZ_LOG_CRON);
        }
        catch (Exception $e) {
            fz_log ('Can\'t send email to '.$file->uploader_email
                   .' file_id:'.$file->id, FZ_LOG_CRON_ERROR);
        }
    }
}