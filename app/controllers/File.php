<?php

/**
 * Controller used to do various actions on files (delete, email, download)
 *
 * TODO vérifier mot de passe s'il y a lieu
 * TODO vérifier si l'utilisateur est passé par download preview
 */
class App_Controller_File extends Fz_Controller {

    /**
     * Display file info and open a download dialog
     */
    public function previewAction () {
        $file = $this->getFile();
        set ('file',      $file);
        set ('available', $file->isAvailable () || $file->isOwner ($this->getUser ()));
        set ('uploader',  $file->getUploader ());
        return html ('file/preview.php');
    }

    /**
     * Send a file
     */
    public function downloadAction () {
        $file = $this->getFile ();
        $available = $file->isAvailable () || $file->isOwner ($this->getUser ());
        set ('available', $available);
        if ($available) {
            $file->download_count = $file->download_count + 1;
            $file->save ();
            return $this->sendFile ($file);
        } else {
            halt (HTTP_FORBIDDEN, __('File is not available for download'));
        }
    }

    /**
     * Allows to download file with filez-1.x urls
     */
    public function downloadFzOneAction () {
        if (! fz_config_get('app', 'filez1_compat'))
            halt (HTTP_FORBIDDEN);
        
        $file = Fz_Db::getTable('File')->findByFzOneHash ($_GET ['ad']);
        if ($file === null) {
            halt (NOT_FOUND, __('There is no file for this code'));
        }
        set ('file',      $file);
        set ('available', $file->isAvailable () || $file->isOwner ($this->getUser ()));
        set ('uploader',  $file->getUploader ());
        return html ('file/preview.php');
    }


    /**
     * Delete a file
     */
    public function confirmDeleteAction () {
        $this->secure ();
        $file = $this->getFile ();
        $user = $this->getUser ();
        $this->checkOwner ($file, $user);
        set ('file', $file);

        return html ('file/confirmDelete.php');
    }
    /**
     * Delete a file
     */
    public function deleteAction () {
        $this->secure ();
        $file = $this->getFile ();
        $user = $this->getUser ();
        $this->checkOwner ($file, $user);
        $file->delete();

        if ($this->isXhrRequest())
            return json (array ('status' => 'success'));
        else {
            flash ('notification', __('File deleted.'));
            redirect_to ('/');
        }
    }

    /**
     * Share a file url by mail (show email form only)
     */
    public function emailFormAction () {
        $this->secure ();
        $user = $this->getUser ();
        $file = $this->getFile ();
        $this->checkOwner ($file, $user);
        set ('file', $file);
        return html ('file/email.php');
    }

    /**
     * Share a file url by mail
     */
    public function emailAction () {
        $this->secure ();
        $user = $this->getUser ();
        $file = $this->getFile ();
        $this->checkOwner ($file, $user);
        set ('file', $file);

        // Send mails
        $user = $this->getUser ();
        $mail = $this->createMail();
        $subject = __r('[FileZ] "%sender%" wants to share a file with you', array (
            'sender' => $user['firstname'].' '.$user['lastname']));
        $msg = __r('email_share_file (%file_name%, %file_url%, %sender%, %msg%)', array(
            'file_name' => $file->file_name,
            'file_url'  => $file->getDownloadUrl(),
            'msg'       => $_POST ['msg'],
            'sender'    => $user['firstname'].' '.$user['lastname'],
        ));
        $mail->setBodyText ($msg);
        $mail->setSubject  ($subject);

        $emailValidator = new Zend_Validate_EmailAddress();
        foreach (explode (',', $_POST['to']) as $email) {
            $email = trim ($email);
            if ($emailValidator->isValid ($email))
                $mail->addBcc ($email);
            else {
                $msg = __r('Email address "%email%" is incorrect, please correct it.',
                    array ('email' => $email));
                return $this->returnError ($msg, 'file/email.php');
            }
        }

        try {
            $mail->send ();
            return $this->returnSuccessOrRedirect ('/');
        }
        catch (Exception $e) {
            $msg = __('An error occured during email submission. Please try again.');
            return $this->returnError ($msg, 'file/email.php');
        }
    }

    // TODO documenter les 2 fonctions suivantes et ? les passer dans la classe controleur

    private function returnError ($msg, $template) {
        if ($this->isXhrRequest ()) {
            return json (array (
                'status' => 'error',
                'statusText' => $msg
            ));
        } else {
            flash_now ('error', $msg);
            return html ($template);
        }
    }
    private function returnSuccessOrRedirect ($url) {
        if ($this->isXhrRequest ()) {
            return json (array ('status' => 'success'));
        } else {
            redirect_to ($url);
        }
    }

    /**
     * Retrieve the requested file from database.
     * If the file isn't found, the action is stopped and a 404 error is returned.
     *
     * @return App_Model_File
     */
    protected function getFile () {
        $file = Fz_Db::getTable('File')->findByHash (params ('file_hash'));
        if ($file === null) {
            halt (NOT_FOUND, __('There is no file for this code'));
        }
        return $file;
    }

    /**
     * Send a file through the standart output
     * @param App_Model_File $file      File to send
     */
    protected function sendFile (App_Model_File $file) {
        $mime = file_mime_content_type ($file->getFileName ());
        header('Content-Type: '.$mime);
        header('Content-Disposition: attachment; filename="'.$file->getFileName ().'"');
        header('Content-Transfer-Encoding: binary');
        header('Expires: 0');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Pragma: public');
        header('Content-Length: '.$file->file_size);
        return file_read ($file->getOnDiskLocation ());
    }

    /**
     * Checks if the user is the owner of the file. Stop the request if not.
     * 
     * @param App_Model_File $file
     * @param array $user
     */
    protected function checkOwner (App_Model_File $file, $user) {        
        if ($file->isOwner ($user))
            return;

        halt (HTTP_UNAUTHORIZED, __('You are not the owner of the file'));
    }


}

?>
