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
 * Controller used to do various actions on files (delete, email, download)
 */
class App_Controller_File extends Fz_Controller {

    /**
     * Display file info and open a download dialog
     */
    public function previewAction () {
        $file = $this->getFile();
        $isOwner = $file->isOwner ($this->getUser ());
        set ('file',            $file);
        set ('isOwner',         $isOwner);
        set ('available',       $file->isAvailable () || $isOwner);
        set ('checkPassword',   !(empty ($file->password) || $isOwner));
        set ('uploader',        $file->getUploader ());
        return html ('file/preview.php');
    }

    /**
     * Download a file
     */
    public function downloadAction () {
        $file = $this->getFile ();
        $this->checkFileAuthorizations ($file);

        $file->download_count = $file->download_count + 1;
        $file->save ();

        fz_log ('downloading '.$file->getFileName ());

        return $this->sendFile ($file);
    }


    /**
     * View an image
     */
    public function viewAction () {
        $file = $this->getFile ();
        $this->checkFileAuthorizations ($file);

        $file->download_count = $file->download_count + 1;
        $file->save ();
        
        fz_log ('viewing '.$file->getFileName ());

        return $this->sendFile ($file, $file->isImage () ? false : true);
    }

    /**
     * Extend lifetime of a file
     */
    public function extendAction () {
        $file = $this->getFile ();

        $result = array ();
        if ($file->extends_count < fz_config_get ('app', 'max_extend_count')) {
            $file->extendLifetime ();
            $file->save ();
            fz_log ('extending life of '.$file->getFileName ());

            $result ['status']     = 'success';
            $result ['statusText'] = __('Lifetime extended');
            $result ['html']       = partial ('main/_file_row.php', array ('file' => $file));
        } else {
            fz_log ('error extending life of '.$file->getFileName ());

            $result ['status']     = 'error';
            $result ['statusText'] = __r('You can\'t extend a file lifetime more than %x% times',
                                    array ('x' => fz_config_get ('app', 'max_extend_count')));
        }

        if ($this->isXhrRequest()) {
            return json ($result);
        }
        else {
            flash (($result ['status'] == 'success' ? 'notification' : 'error'),
                    $result ['statusText']);
            redirect_to ('/');
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
        flash('back_to', flash_now('back_to'));
        $this->secure ();
        $file = $this->getFile ();
        $user = $this->getUser ();
        if (! $user->is_admin) $this->checkOwner ($file, $user);
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
        if (! $user->is_admin) $this->checkOwner ($file, $user);
        $file->delete();
        fz_log ($user.' deleting '.$file->getFileName ());

        if ($this->isXhrRequest())
            return json (array ('status' => 'success'));
        else {
            flash ('notification', __('File deleted.'));
            redirect_to ( flash_now('back_to') );
        }
    }

    /**
     * Share a file url
     */
    public function shareAction () {
        $this->secure ();
        $user = $this->getUser ();
        $file = $this->getFile ();
        $this->checkOwner ($file, $user);
        set ('sharing_destinations', fz_config_get('app', 'sharing_destinations'));
        set ('downloadUrl', $file->getDownloadUrl ());
        return html ('file/_share_link.php');
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
            'sender' => $user));
        $msg = __r('email_share_file (%file_name%, %file_url%, %sender%, %msg%)', array(
            'file_name' => $file->file_name,
            'file_url'  => $file->getDownloadUrl(),
            'msg'       => $_POST ['msg'],
            'sender'    => $user,
        ));
        $mail->setBodyText ($msg);
        $mail->setSubject  ($subject);
        $mail->setReplyTo  ($user->email, $user);
        $mail->clearFrom();
        $mail->setFrom     ($user->email, $user);

        $emailValidator = new Zend_Validate_EmailAddress();
        $to = str_replace(',',' ',$_POST['to']);
        $to = str_replace(';',' ',$to);
        foreach (explode (' ', $to) as $email) {
            $email = trim ($email);
            if (empty ($email))
                continue;
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
            fz_log ('Error while sending email', FZ_LOG_ERROR, $e);
            $msg = __('An error occurred during email submission, probably too many emails. Please try again.');
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
    		flash ('notification', __('Successfully sent.'));
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
     * Check if the client is authorized to download the file
     *
     * @param File $file
     */
    protected function checkFileAuthorizations ($file) {
        if (! $file->isOwner ($this->getUser ())) {
            if (! $file->isAvailable ()) {
                halt (HTTP_FORBIDDEN, __('File is not available for download'));
            } else if (! empty ($file->password)
                    && ! $file->checkPassword ($_POST['password'])) {
                flash ('error', __('Incorrect password'));
                redirect ('/'.$file->getHash());
            }
        }
    }

    /**
     * Send a file through the standart output
     * @param App_Model_File $file      File to send
     */
    protected function sendFile (App_Model_File $file, $forceDownload = true) {
        $mime = $file->getMimetype();
        header('Content-Type: '.$mime);
        header('Content-Transfer-Encoding: binary');
        header('Expires: 0');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Pragma: public');
        header('Content-Length: '.$file->file_size);

        if ($forceDownload)
            header('Content-Disposition: attachment; filename="'.
                iconv ("UTF-8", "ISO-8859-1", $file->getFileName ()).'"');

        return file_read ($file->getOnDiskLocation ());
    }

    /**
     * Checks if the user is the owner of the file. Stop the request if not.
     * 
     * @param App_Model_File $file
     * @param App_Model_User $user
     */
    protected function checkOwner (App_Model_File $file, $user) {        
        if ($file->isOwner ($user))
            return;

        halt (HTTP_UNAUTHORIZED, __('You are not the owner of the file'));
    }


}

?>
