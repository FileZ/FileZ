<?php

/**
 * Controller used to do various actions on files (delete, email, download)
 *
 *
 * TODO vérifier si l'utilisateur n'est pas un robot (imposer un délais d'attente exponentiel lorsque qu'il y a plus de 3 requêtes en moins de 5 secondes)
 * TODO vérifier mot de passe s'il y a lieu
 * TODO vérifier si l'utilisateur est passé par download preview
 */
class App_Controller_File extends Fz_Controller {

    /**
     * Display file info and open a download dialog
     */
    public function previewAction () {
        $file = $this->getFile();
        // TODO vérifier $file->available_until & available_from !!!
        set ('file', $file);
        return html ('file/preview.php');
    }

    /**
     * Send a file
     */
    public function downloadAction () {
        $file = $this->getFile ();
        // TODO vérifier $file->available_until & available_from !!!
        $file->download_count = $file->download_count + 1;
        $file->save ();
        return $this->sendFile ($file);
    }

    /**
     * Allows to download file with filez-1.x urls
     */
    public function downloadFzOneAction () {
        // TODO vérifier $file->available_until & available_from !!!
        $file_hash = $_GET ['ad'];
        // TODO
        // $file = ...
        return $this->sendFile ($file);
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
        // Delete the file on disk or tag it as "waiting for deletion"

        if ($this->isXhrRequest())
            return json (array ('status' => 'success'));
        else {
            flash ('notification', 'Le fichier a été supprimé.'); // TODO i18n
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
        $subject = '[FileZ] Dépôt du fichier "%file_name%"';   // TODO i18n
        $subject = str_replace ('%file_name%', $file->file_name, $subject);
        $msg = 'email_share_file (%file_name%, %file_url%, %sender%, %msg%)'; // TODO i18n
        $msg = str_replace ('%file_name%', $file->file_name, $msg);
        $msg = str_replace ('%file_url%' , $file->getDownloadUrl(), $msg);
        $msg = str_replace ('%msg%'      , $_POST ['msg'], $msg);
        $msg = str_replace ('%sender%'   , $user['firstname'].' '.$user['lastname'], $msg);
        $mail->setBodyText ($msg);
        $mail->setSubject  ($subject);

        $emailValidator = new Zend_Validate_EmailAddress();
        foreach (explode (',', $_POST['to']) as $email) {
            $email = trim ($email);
            if ($emailValidator->isValid ($email))
                $mail->addBcc ($email);
            else {
                $msg = 'L\'adresse email "%email%" est invalide, veuillez la corriger'; // TODO i18n
                return $this->returnError (str_replace ('%email%', $email, $msg), 'file/email.php');
            }
        }

        try {
            $mail->send ();
            return $this->returnSuccessOrRedirect ('/');
        }
        catch (Exception $e) {
            $msg = 'Une erreur s\'est produit pendant l\'envoi du mail, veuillez réessayer.'; // TODO i18n
            return $this->returnError ($msg, 'file/email.php');
        }
    }

    // TODO documenter les fonctions suivantes et ? les passer dans la classe controleur

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
        if ($file === null)
            halt (NOT_FOUND, 'There is no file for this hash code');
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
        return file_read (fz_config_get ('app', 'upload_dir').'/'.$file->getId ());
    }

    /**
     * Checks if the user is the owner of the file. Stop the request if not.
     * 
     * @param App_Model_File $file
     * @param array $user
     */
    protected function checkOwner (App_Model_File $file, $user) {        
        if ($file->uploader_email == $user ['email'] // check for invited users
         || $file->uploader_uid   == $user ['id']) // or registered users
            return;

        halt (HTTP_UNAUTHORIZED, 'You are not the owner of the file');
    }
}

?>
