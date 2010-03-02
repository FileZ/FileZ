<?php

/**
 * Controller used for administratives tasks
 */
class App_Controller_Admin extends Fz_Controller {

    public function checkFilesAction () {
        // Delete files whose lifetime expired
        Fz_Db::getTable('File')->deleteExpiredFiles ();

        // Send mail for files which will be deleted in less than 2 days
        foreach (Fz_Db::getTable('File')->findFilesToBeDeleted () as $file) {
            $file->del_notif_sent = true;
            $file->save ();

            try {
                // Send mails
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
                //$mail->send (); // FIXME
            }
            catch (Exception $e) {
                fz_log ('Can\'t send email for file '.$file->uploader_email
                       .' file_id:'.$file->id, FZ_LOG_ERROR);
            }
        }
    }

}