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

define ('UPLOAD_ERR_QUOTA_EXCEEDED', 99);
define('UPLOAD_ERR_VIRUS_FOUND', 100);
define('UPLOAD_ERR_ANTIVIRUS', 101);

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
        fz_log ('uploading');
        fz_log ('uploading', FZ_LOG_DEBUG, $_FILES);
        $response = array (); // returned data

        // check if request exceed php.ini post_max_size
        if ($_SERVER ['CONTENT_LENGTH'] > $this->shorthandSizeToBytes (
                                                   ini_get ('post_max_size'))) {
            fz_log ('upload error (POST request > post_max_size)', FZ_LOG_ERROR);
            return $this->onFileUploadError (UPLOAD_ERR_INI_SIZE);
        }
        else if ($_FILES ['file']['error'] === UPLOAD_ERR_OK) {
            if ($this->checkQuota ($_FILES ['file'])) // Check user quota first
                return $this->onFileUploadError (UPLOAD_ERR_QUOTA_EXCEEDED);

            // We check if the file contains a virus before and must be stopped
            $fileFirstStep = $_FILES ['file']['tmp_name'];
            try {
    	      if ($this->checkVirus ($fileFirstStep))
              {
    	        return $this->onFileUploadError (UPLOAD_ERR_VIRUS_FOUND);
              }
    	    } catch (Exception $e) {
    	      fz_log ($e, FZ_LOG_ERROR);
    	      return $this->onFileUploadError (UPLOAD_ERR_ANTIVIRUS);
    	    }

            // Still no error ? we can move the file to its final destination
            $file = $this->saveFile ($_POST, $_FILES ['file']);
            if ($file !== null) {
                $this->sendFileUploadedMail ($file);
                return $this->onFileUploadSuccess ($file);

            } else { // Errors happened while saving or moving the uploaded file
                return $this->onFileUploadError ();
            }
        } else { // Errors happened during file upload
            return $this->onFileUploadError ($_FILES ['file']['error']);
        }
    }

    /**
     * Action called from the javascript to request file upload progress
     * @return string (json)
     */
    public function getProgressAction () {
        $this->secure ();

        $uploadId = params ('upload_id');
        if (! $uploadId)
            halt (HTTP_BAD_REQUEST, 'A file id must be specified');

        $progressMonitor = fz_config_get ('app', 'progress_monitor');
        $progressMonitor = new $progressMonitor ();

        if (! $progressMonitor->isInstalled ())
            halt (HTTP_NOT_IMPLEMENTED, 'Your system is not configured for'.get_class ($progressMonitor));
            
        $progress = $progressMonitor->getProgress ($uploadId);

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

        $user = $this->getUser ();

        // Storing values
        $file = new App_Model_File ();
        $file->setFileInfo      ($uploadedFile);
        $file->setUploader      ($user);
        $file->setCreatedAt     (new Zend_Date ());
        $file->comment          = substr ($comment, 0, 199);
        $file->setAvailableFrom ($availableFrom);
        $file->setAvailableUntil($availableUntil);
        $file->notify_uploader  = isset ($post['email-notifications']);
        if (! empty ($post ['password']))
            $file->setPassword  ($post ['password']);

        try {
            $file->save ();

            if ($file->moveUploadedFile ($uploadedFile)) {
                fz_log ('Saved "'.$file->file_name.'"['.$file->id.'] uploaded by '.$user['email']);
                return $file;
            }
            else {
                $file->delete ();
                return null;
            }
        } catch (Exception $e) {
            fz_log ('Can\'t save file "'.$uploadedFile['name'].'" uploaded by '.$user['email'], FZ_LOG_ERROR);
            fz_log ($e, FZ_LOG_ERROR);
            return null;
        }
    }

    /**
     * Notify the user by email that its file has been uploaded
     *
     * @param App_Model_File $file
     */
    private function sendFileUploadedMail (App_Model_File $file) {
        if (! $file->notify_uploader)
            return;

        $user = $this->getUser ();
        $subject = __r('[FileZ] "%file_name%" uploaded successfuly',
            array('file_name' => $file->file_name));
        $msg = __r('email_upload_success (%file_name%, %file_url%, %filez_url%, %available_from%, %available_until%)',
            array('file_name' => $file->file_name,
                  'available_from'  => $file->getAvailableFrom()->toString  (Zend_Date::DATE_LONG),
                  'available_until' => $file->getAvailableUntil()->toString (Zend_Date::DATE_LONG),
                  'file_url'  => $file->getDownloadUrl(),
                  'filez_url' => fz_url_for ('/', (fz_config_get ('app', 'https') == 'always'))
            )
        );

        $mail = $this->createMail();
        $mail->setBodyText ($msg);
        $mail->setSubject  ($subject);
        $mail->addTo ($user ['email'], $user['firstname'].' '.$user['lastname']);

        try {
            $mail->send ();
        }
        catch (Exception $e) {
            fz_log ('Can\'t send email "File Uploaded" : '.$e, FZ_LOG_ERROR);
        }
    }

    /**
     * Transform a size in the shorthand format ('K', 'M', 'G') to bytes
     *
     * @param   string      $size
     * @return  integer
     */
    private function shorthandSizeToBytes ($size) {
        $size = str_replace (' ', '', $size);
        switch(strtolower($size[strlen($size)-1])) {
            case 'g': $size *= 1024;
            case 'm': $size *= 1024;
            case 'k': $size *= 1024;
        }
        return floatval ($size);
    }

    /**
     * Check if the user will exceed its quota if if he upload the file $file
     *
     * @param array $file   File element from $_FILES
     * @return boolean      true if he will exceed, false else
     */
    private function checkQuota ($file) {
        $fileSize = $_FILES['file']['size'];
        $freeSpace = Fz_Db::getTable('File')->getRemainingSpaceForUser ($this->getUser());
        return ($fileSize > $freeSpace);
    }

    /**
     * Check if the file is infected thanks a software called clamav.
     *
     * @throws Exception Error during scan of the file
     * @param  $file  File we want to check
     * @return int    0 if everything is ok
     */
    private function checkVirus($file) {
       $cmd = "clamscan -i --no-summary --remove";
       exec($cmd." ".$file, $output, $return_value);

        if ($return_value === 1) {
        	fz_log ('VIRUS FOUND file id '.$file.', antivirus message: "'.implode ($output).'"', FZ_LOG_ERROR);
        	return 1;
       }

        if ($return_value === 2) {
    	    throw new Exception('Antivirus reported an error.');
       }
       return 0;
    }
    /**
     * Return data to the browser with the correct response type (json or html).
     * If the request comes from an iframe (with the is-async GET parameter,
     * the response is embedded inside a textarea to prevent some browsers :
     * quirks (http://www.malsup.com/jquery/form/#file-upload) JQuery Form
     * Plugin will handle the response transparently.
     * 
     * @param array $data
     */
    private function returnData ($data) {
        if (array_key_exists ('is-async', $_GET) && $_GET ['is-async']) {
            return html("<textarea>\n".json_encode ($data)."\n</textarea>",'');
        }
        else {
            flash ('notification', $data ['statusText']);
            redirect_to ('/');
        }
    }

    /**
     * Function called on file upload success, a default message is returned
     * to the user.
     *
     * @param App_Model_File $file
     */
    private function onFileUploadSuccess (App_Model_File $file) {
        $user                    = $this->getUser();
        $response ['status']     = 'success';
        $response ['statusText'] = __('The file was successfuly uploaded');
        $response ['html']       = partial ('main/_file_row.php', array ('file' => $file));
        $response ['disk_usage'] = bytesToShorthand (max (0,
                     Fz_Db::getTable('File')->getTotalDiskSpaceByUser ($user)));
        return $this->returnData ($response);
    }

    /**
     * Function called on file upload error. A message corresponding to the error
     * code passed as parameter is return to the user. Error codes come from
     * $_FILES['userfile']['error'] plus a custom error code called
     * 'UPLOAD_ERR_QUOTA_EXCEEDED'
     *
     * @param integer $errorCode
     */
    private function onFileUploadError ($errorCode = null) {
        $response ['status']     = 'error';
        $response ['statusText'] = __('An error occured while uploading the file.').' ';

        if ($errorCode === null)
            return $this->returnData ($response);

        switch ($errorCode) {
            case UPLOAD_ERR_NO_TMP_DIR:
                fz_log ('upload error (Missing a temporary folder)', FZ_LOG_ERROR);
                break;
            case UPLOAD_ERR_CANT_WRITE:
                fz_log ('upload error (Failed to write file to disk)', FZ_LOG_ERROR);
                break;

            // These errors come from the client side, let him know what's wrong
            case UPLOAD_ERR_INI_SIZE:
            case UPLOAD_ERR_FORM_SIZE:
                $response ['statusText'] .=
                    __('The uploaded file exceeds the max file size.')
                    .' : ('.ini_get ('upload_max_filesize').')';
                break;
            case UPLOAD_ERR_PARTIAL:
                $response ['statusText'] .=
                     __('The uploaded file was only partially uploaded.');
                break;
            case UPLOAD_ERR_NO_FILE:
                $response ['statusText'] .=
                     __('No file was uploaded.');
                break;
            case UPLOAD_ERR_QUOTA_EXCEEDED:
                $response ['statusText'] .= __r('You exceeded your disk space quota (%space%).',
                    array ('space' => fz_config_get ('app', 'user_quota')));

            // a virus has been found
            case UPLOAD_ERR_VIRUS_FOUND:
                $response ['statusText'] .=
                     __('A virus was found in the file.');
                break;
            case UPLOAD_ERR_ANTIVIRUS:
                $response ['statusText'] .=
                     __('An error occurred during a scan. Please contact the system administrator.');
                break;
        }
        return $this->returnData ($response);
    }
}

