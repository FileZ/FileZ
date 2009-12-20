<?php

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

    public function startAction () {
        $jsonData     = array ();
        $uploadDir    = fz_config_get ('app', 'upload_dir');

        $availableFrom  = new Zend_Date ($_POST['start-from'], Zend_Date::DATE_SHORT);
        $availableUntil = clone ($availableFrom);
        $availableUntil->add ((int) $_POST['duration'], Zend_Date::DAY);

        $file = new App_Model_File ();
        $file->file_name        = $_FILES['file']['name'];
        $file->file_size        = $_FILES['file']['size'];
        $file->available_from   = $availableFrom;
        $file->available_until  = $availableUntil;
        // TODO commentaire, auteur, date d'upload ...
        $file->save ();

        // Let's move the file to its final destination
        if (move_uploaded_file ($_FILES['file']['tmp_name'], $uploadDir.'/'.$file->id)) {
            // returned data
            $jsonData['status']      = 'ok';
            $jsonData['status_text'] = 'The file has been successuly uploaded';
            $jsonData['html']        = render_partial ('main/_file_row.php', array ('file' => $file));
        } else {

            // Logging error if needed
            if (in_array ($_FILES['file']['error'], array (UPLOAD_ERR_CANT_WRITE, UPLOAD_ERR_NO_TMP_DIR)))
                fz_log ('upload error ('.$this->uploadErrors [$_FILES['file']['error']].')', FZ_LOG_ERROR);

            // returned data
            $jsonData['status']     = 'error';
            $jsonData['filename']   = $_FILES['file']['name'];
            $jsonData['debug']      = $_REQUEST;
            $jsonData['statusText'] = $_FILES['file']['error'] != 0 ?
                $this->uploadErrors [$_FILES['file']['error']]   :
                'Can\'t move the file to "'.$uploadDir.'"' ;
        }

        
        if (array_key_exists ('is_async', $_REQUEST) && $_REQUEST['is_async']) {
            // The response is embedded inside a textarea to prevent some browsers :
            // quirks : http://www.malsup.com/jquery/form/#file-upload
            // JQuery Form Plugin will handle the response transparently.
            return '<textarea>'.json_encode ($jsonData).'</textarea>';
        }
        else {
            // We can redirect the user to the home page
            // TODO set flash message
            // redirect ('/');
        }
    }

    public function getProgressAction () {
        $upload_id = params ('upload_id');
        if (!$upload_id)
            halt (NOT_FOUND, 'This upload does not exist');
          
        return json ((public function_exists ('apc_fetch') ? apc_fetch ('upload_'.$upload_id) : false));
    }
}

