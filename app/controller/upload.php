<?php

function fz_action_upload_start () {
    $json_data     = array ();
    $upload_dir    = fz_config_get ('app', 'upload_dir');
    $upload_errors = array (
        UPLOAD_ERR_OK         => 'There is no error, the file uploaded with success.',
        UPLOAD_ERR_INI_SIZE   => 'The uploaded file exceeds the upload_max_filesize directive in php.ini.',
        UPLOAD_ERR_FORM_SIZE  => 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form.',
        UPLOAD_ERR_PARTIAL    => 'The uploaded file was only partially uploaded.',
        UPLOAD_ERR_NO_FILE    => 'No file was uploaded.',
        UPLOAD_ERR_NO_TMP_DIR => 'Missing a temporary folder.',
        UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk.',
        UPLOAD_ERR_EXTENSION  => 'File upload stopped by extension.',
    );

    $available_from  = new Zend_Date ($_POST['start-from'], Zend_Date::DATE_SHORT);
    $available_until = clone ($available_from);
    $available_until->add ((int) $_POST['duration'], Zend_Date::DAY);

    $file_object = new fzFile ();
    $file_object->file_name        = $_FILES['file']['name'];
    $file_object->file_size        = $_FILES['file']['size'];
    $file_object->available_from   = $available_from;
    $file_object->available_until  = $available_until;
    // TODO commentaire, auteur, date d'upload ...
    $file_object->save ();

    // Let's move the file to its final destination
    if (move_uploaded_file ($_FILES['file']['tmp_name'], $upload_dir.'/'.$file_object->id)) {
        // returned data
        $json_data['status']      = 'ok';
        $json_data['status_text'] = 'The file has been successuly uploaded';
        $json_data['html']        = render_partial ('main/_file_row.php', array ('file' => $file_object));
    } else {

        // Logging error if needed
        if (in_array ($_FILES['file']['error'], array (UPLOAD_ERR_CANT_WRITE, UPLOAD_ERR_NO_TMP_DIR)))
            fz_log ('upload error ('.$upload_errors [$_FILES['file']['error']].')', FZ_LOG_ERROR);

        // returned data
        $json_data['status']     = 'error';
        $json_data['filename']   = $_FILES['file']['name'];
        $json_data['debug']      = $_REQUEST;
        $json_data['statusText'] = $_FILES['file']['error'] != 0 ?  
            $upload_errors [$_FILES['file']['error']]   :
            'Can\'t move the file to "'.$upload_dir.'"' ;
    }

    
    if (array_key_exists ('is_async', $_REQUEST) && $_REQUEST['is_async']) {
        // The response is embedded inside a textarea to prevent some browsers :
        // quirks : http://www.malsup.com/jquery/form/#file-upload
        // JQuery Form Plugin will handle the response transparently.
        return '<textarea>'.json_encode ($json_data).'</textarea>'; 
    }
    else {
        // We can redirect the user to the home page
        // TODO set flash message
        // redirect ('/');
    }
}

function fz_action_upload_get_progress () {
    $upload_id = params ('upload_id');
    if (!$upload_id)
        halt (NOT_FOUND, 'This upload does not exist');
      
    return json ((function_exists ('apc_fetch') ? apc_fetch ('upload_'.$upload_id) : false));
}

