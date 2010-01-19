<?php

/**
 * Controller used to upload files and monitor progression
 */
class App_Controller_Upload extends Fz_Controller {

    protected $uploadErrors = array (
        UPLOAD_ERR_OK         => 'There is no error, the file uploaded with success.',
        UPLOAD_ERR_INI_SIZE   => 'The uploaded file exceeds the upload_max_filesize directive in php.ini.',
        UPLOAD_ERR_FORM_SIZE  => 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form.',
        UPLOAD_ERR_PARTIAL    => 'The uploaded file was only partially uploaded.',
        UPLOAD_ERR_NO_FILE    => 'No file was uploaded.',
        UPLOAD_ERR_NO_TMP_DIR => 'Missing a temporary folder.',
        UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk.',
        UPLOAD_ERR_EXTENSION  => 'File upload stopped by extension.',
    );

    /**
     * Action called when uploading a file
     * @return string   json if request is made async or html otherwise
     */
    public function startAction () {
        $this->secure ();
        $jsonData = array (); // returned data
        $file     = $this->saveFile ($_POST, $_FILES);
        // Let's move the file to its final destination
        if (move_uploaded_file ($_FILES['file']['tmp_name'],
                fz_config_get ('app', 'upload_dir').'/'.$file->id)) {

            $jsonData['status']      = 'ok';
            $jsonData['statusText']  = 'The file has been successfuly uploaded';
            $jsonData['html']        = partial ('main/_file_row.php', array ('file' => $file));

            // Notify the uploader by mail
            $user = $this->getUser ();
            $subject = '[FileZ] Dépôt du fichier "%file_name%"'; // TODO i18n
            $subject = str_replace ('%file_name%', $file->file_name, $subject);
            $msg = 'email_upload_success (%file_name%, %file_url%, %filez_url%)'; // TODO i18n
            $msg = str_replace ('%file_name%', $file->file_name, $msg);
            $msg = str_replace ('%file_url%' , $file->getDownloadUrl(), $msg);
            $msg = str_replace ('%filez_url%', 'http://'.$_SERVER["SERVER_NAME"]
                                                          .url_for ('/'), $msg);
            $mail = $this->createMail();
            $mail->setBodyText ($msg);
            $mail->setSubject  ($subject);
            $mail->addTo ($user ['email'], $user['firstname'].' '.$user['lastname']);
            $mail->send ();

        } else { // Errors happened while moving the uploaded file
            $file->delete ();
            // Logging error if needed
            if ($_FILES['file']['error'] == UPLOAD_ERR_CANT_WRITE ||
                $_FILES['file']['error'] == UPLOAD_ERR_NO_TMP_DIR)
                fz_log ('upload error ('.$this->uploadErrors [$_FILES['file']['error']].')', FZ_LOG_ERROR);

            // returned data
            $jsonData['status']     = 'error';
            $jsonData['filename']   = $_FILES['file']['name'];
            $jsonData['statusText'] = $this->uploadErrors [$_FILES['file']['error']];
        }

        if (array_key_exists ('is_async', $_REQUEST) && $_REQUEST['is_async']) {
            // The response is embedded inside a textarea to prevent some browsers :
            // quirks : http://www.malsup.com/jquery/form/#file-upload
            // JQuery Form Plugin will handle the response transparently.
            return html("<textarea>\n".json_encode ($jsonData)."\n</textarea>",'');
        }
        else {
            flash ('notification', 'Votre fichier a été envoyé.'); // TODO i18n
            redirect_to ('/');
        }
    }

    /**
     * Action called from the javascript to request file upload progress
     * @return string (json)
     */
    public function getProgressAction () {
        $this->secure ();
        $upload_id = params ('upload_id');
        if (!$upload_id)
            halt (NOT_FOUND, 'This upload does not exist');
          
        return json (function_exists ('apc_fetch') ? apc_fetch ('upload_'.$upload_id) : false);
    }

    /**
     * @param array $post       =~ $_POST
     * @param array $files      =~ $_FILES
     * @return App_Model_File
     */
    private function saveFile ($post, $files) {
        $availableFrom  = new Zend_Date ($post ['start-from'], Zend_Date::DATE_SHORT);
        $availableUntil = clone ($availableFrom);
        $availableUntil->add ((int) $post ['duration'], Zend_Date::DAY);

        $user = $this->getUser ();

        $file = new App_Model_File ();
        $file->file_name        = $files ['file']['name'];
        $file->file_size        = $files ['file']['size'];
        $file->available_from   = $availableFrom;
        $file->available_until  = $availableUntil;
        $file->comment          = $post ['comment'];
        $file->created_at       = new Zend_Date ();
        $file->uploader_uid     = $user ['id'];
        $file->uploader_email   = $user ['id'];
        $file->save ();

        return $file;
    }
}

