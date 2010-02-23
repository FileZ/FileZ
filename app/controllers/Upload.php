<?php

/**
 * Controller used to upload files and monitor progression
 */
class App_Controller_Upload extends Fz_Controller {

    /**
     * Descriptions of possibles upload errors easily understandable by the end user.
     * @var array
     */
    protected $uploadErrors = array (
        UPLOAD_ERR_OK         => 'There is no error, the file uploaded with success.',
        UPLOAD_ERR_INI_SIZE   => 'The uploaded file exceeds the max file size.',
        UPLOAD_ERR_FORM_SIZE  => 'The uploaded file exceeds the max file size.',
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
        $file     = $this->saveFile ($_POST, $_FILES['file']);
        $tmpName  = $_FILES['file']['tmp_name'];
        $filename = fz_config_get ('app', 'upload_dir').'/'.$file->id;

        // Let's move the file to its final destination
        if ($_FILES['file']['error'] === UPLOAD_ERR_OK && is_uploaded_file($tmpName) &&
            move_uploaded_file ($tmpName, $filename)) {

            $jsonData['status']     = 'success';
            $jsonData['statusText'] = 'The file has been successfuly uploaded';
            $jsonData['html']       = partial ('main/_file_row.php', array ('file' => $file));

            $this->sendFileUploadedMail ($file);

        } else { // Errors happened while moving the uploaded file
            $file->delete ();

            $jsonData['status']     = 'error';
            $jsonData['statusText'] = __('An error occured while uploading the file.');

            switch ($_FILES['file']['error']) {
                case UPLOAD_ERR_NO_TMP_DIR:
                case UPLOAD_ERR_CANT_WRITE:
                    fz_log ('upload error ('. // Logging error if needed
                        $this->uploadErrors [$_FILES['file']['error']].')', FZ_LOG_ERROR);
                    break;

                // These errors come from the client side, let him know what's wrong
                case UPLOAD_ERR_INI_SIZE:
                case UPLOAD_ERR_FORM_SIZE:
                case UPLOAD_ERR_PARTIAL:
                case UPLOAD_ERR_NO_FILE:
                    $jsonData['statusText'] .= ' '.__('Details').' : '
                        . __($this->uploadErrors [$_FILES['file']['error']]);
            }                
        }

        if (array_key_exists ('is-async', $_POST) && $_POST['is-async']) {
            // The response is embedded inside a textarea to prevent some browsers :
            // quirks : http://www.malsup.com/jquery/form/#file-upload
            // JQuery Form Plugin will handle the response transparently.
            return html("<textarea>\n".json_encode ($jsonData)."\n</textarea>",'');
        }
        else {
            flash ('notification', __('Votre fichier a été envoyé.'));
            redirect_to ('/');
        }
    }

    /**
     * Action called from the javascript to request file upload progress
     * @return string (json)
     */
    public function getProgressAction () {
        $this->secure ();

        if (! function_exists ('apc_fetch'))
             halt (HTTP_NOT_IMPLEMENTED, 'APC not installed');

        $upload_id = params ('upload_id');
        if (! $upload_id)
            halt (HTTP_BAD_REQUEST, 'A file id must be specified');

        $progress = apc_fetch ('upload_'.$upload_id);
        if (! is_array ($progress))
            halt (NOT_FOUND);

        return json ($progress);
    }

    /**
     * Create a new File object from posted values and store it into the database.
     *
     * @param array $post       ~= $_POST
     * @param array $files      ~= $_FILES
     * @return App_Model_File
     */
    private function saveFile ($post, $uploadedFile) {
        // Computing default values
        $comment = array_key_exists ('comment',  $post) ? $post['comment'] : '';

        // Validating lifetime
        $lifetime = fz_config_get ('app', 'default_file_lifetime', 10);
        if (array_key_exists ('lifetime', $post) && is_numeric ($post['lifetime'])) {
            $lifetime = intval ($post['lifetime']);
            $maxLifetime = intval (fz_config_get ('app', 'max_file_lifetime', 20));
            if ($lifetime > $maxLifetime)
                $lifetime = $maxLifetime;
        }

        $availableFrom  = array_key_exists ('start-from', $post) ? $post['start-from'] : null;
        $availableFrom  = new Zend_Date ($availableFrom, Zend_Date::DATE_SHORT);
        $availableUntil = clone ($availableFrom);
        $availableUntil->add ($lifetime, Zend_Date::DAY);

        // Storing values
        $file = new App_Model_File ();
        $file->setFileInfo      ($uploadedFile);
        $file->setUploader      ($this->getUser ());
        $file->created_at       = new Zend_Date ();
        $file->comment          = $comment;
        $file->available_from   = $availableFrom;
        $file->available_until  = $availableUntil;
        $file->save ();

        return $file;
    }

    /**
     * Notify the user by email that its file has been uploaded
     *
     * @param App_Model_File $file
     */
    private function sendFileUploadedMail ($file) {
        $user = $this->getUser ();
        $subject = __('[FileZ] Dépôt du fichier "%file_name%"');
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
    }

}

