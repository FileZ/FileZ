<?php

/**
 * Controller used to upload files and monitor progression
 */
class App_Controller_Upload extends Fz_Controller {

    /**
     * Action called when uploading a file
     * @return string   json if request is made async or html otherwise
     */
    public function startAction () {
        $this->secure ();
        fz_log ($_SERVER["REMOTE_ADDR"].' uploading');
        $response = array (); // returned data

        if (array_key_exists ('file', $_FILES))
            $file = $this->saveFile ($_POST, $_FILES ['file']);

        // check if request exceed php.ini post_max_size
        if ($_SERVER ['CONTENT_LENGTH'] > $this->shorthandSizeToBytes (
                                                   ini_get ('post_max_size'))) {
            $response ['status'] = 'error';
            $response ['statusText'] =
                 __('An error occured while uploading the file.').' '
                .__('Details').' : '. $this->explainError (UPLOAD_ERR_INI_SIZE)
                .' : ('.ini_get ('upload_max_filesize').')';
            
            fz_log ('upload error (POST request > post_max_size)', FZ_LOG_ERROR);
        }
        // Let's move the file to its final destination
        else if ($_FILES ['file']['error'] === UPLOAD_ERR_OK) {

            $userDiskSpace = $_FILES['file']['size']
               + Fz_Db::getTable('File')->getTotalDiskSpaceByUser (); // TODO
            $maxDiskSpacePerUser = fz_config_get ('app', 'user_quota');

            if ($userDiskSpace > $this->shorthandSizeToBytes ($maxDiskSpacePerUser)) {
                $response ['status'] = 'error';
                $response ['statusText'] = __('You exceeded your disk space quota ('
                                                     .$maxDiskSpacePerUser.')');
            }
            else if ($file->moveUploadedFile ($_FILES['file'])) {

                $response ['status']     = 'success';
                $response ['statusText'] = __('The file was successfuly uploaded');
                $response ['html']       = partial ('main/_file_row.php', array ('file' => $file));

                try {
                    $this->sendFileUploadedMail ($file);
                }
                catch (Exception $e) {
                    fz_log ('Can\'t send email "File Uploaded"', FZ_LOG_ERROR);
                }
            }

        } else { // Errors happened while moving the uploaded file
            $file->delete ();

            $response ['status']     = 'error';
            $response ['statusText'] = __('An error occured while uploading the file.');
            switch ($_FILES ['file']['error']) {
                case UPLOAD_ERR_NO_TMP_DIR:
                case UPLOAD_ERR_CANT_WRITE:
                    fz_log ('upload error ('. // Logging error if needed
                        $this->explainError ($_FILES ['file']['error']).')', FZ_LOG_ERROR);
                    break;

                // These errors come from the client side, let him know what's wrong
                case UPLOAD_ERR_INI_SIZE:
                case UPLOAD_ERR_FORM_SIZE:
                    $response ['statusText'] .= ' '.__('Details').' : '
                        .$this->explainError ($_FILES['file']['error'])
                        .' : ('.ini_get ('upload_max_filesize').')';
                    break;
                case UPLOAD_ERR_PARTIAL:
                case UPLOAD_ERR_NO_FILE:
                    $response ['statusText'] .= ' '.__('Details').' : '
                        .$this->explainError ($_FILES ['file']['error']);
            }                
        }

        if (array_key_exists ('is-async', $_GET) && $_GET ['is-async']) {
            // The response is embedded inside a textarea to prevent some browsers :
            // quirks : http://www.malsup.com/jquery/form/#file-upload
            // JQuery Form Plugin will handle the response transparently.
            return html("<textarea>\n".json_encode ($response)."\n</textarea>",'');
        }
        else {
            flash ('notification', $response ['statusText']);
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
        $file->comment          = substr ($comment, 0, 199);
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
        $subject = __r('[FileZ] "%file_name%" uploaded successfuly',
            array('file_name' => $file->file_name));
        $msg = __r('email_upload_success (%file_name%, %file_url%, %filez_url%, %available_from%, %available_until%)',
            array('file_name' => $file->file_name,
                  'available_from'  => $file->getAvailableFrom()->toString  (Zend_Date::DATE_LONG),
                  'available_until' => $file->getAvailableUntil()->toString (Zend_Date::DATE_LONG),
                  'file_url'  => $file->getDownloadUrl(),
                  'filez_url' => 'http://'.$_SERVER["SERVER_NAME"].url_for ('/')));
                                // TODO use https if needed        

        $mail = $this->createMail();
        $mail->setBodyText ($msg);
        $mail->setSubject  ($subject);
        $mail->addTo ($user ['email'], $user['firstname'].' '.$user['lastname']);
        $mail->send ();
    }

    /**
     * Return localised description of an upload error code
     *
     * @param integer $errorCode
     * @return string
     */
    private function explainError ($errorCode) {
        switch ($errorCode) {
            case UPLOAD_ERR_OK         : return __('There is no error, the file uploaded with success.');
            case UPLOAD_ERR_INI_SIZE   : 
            case UPLOAD_ERR_FORM_SIZE  : return __('The uploaded file exceeds the max file size.');
            case UPLOAD_ERR_PARTIAL    : return __('The uploaded file was only partially uploaded.');
            case UPLOAD_ERR_NO_FILE    : return __('No file was uploaded.');
            case UPLOAD_ERR_NO_TMP_DIR : return __('Missing a temporary folder.');
            case UPLOAD_ERR_CANT_WRITE : return __('Failed to write file to disk.');
            case UPLOAD_ERR_EXTENSION  : return __('File upload stopped by extension.');
            default : return __('Unknown error');
        }
    }

    /**
     * Transform a size in the shorthand format ('K', 'M', 'G') to bytes
     *
     * @param   string      $size
     * @return  integer
     */
    private function shorthandSizeToBytes ($size) {
        $size = ;
        $size = str_replace (' ', '', $size);
        $size = str_replace (array ('K'  , 'M'     , 'G'        ),
                             array ('000', '000000', '000000000'), $size);
        return intval ($size);
    }
}

