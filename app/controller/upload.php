<?php

function fz_action_upload_start () {
    $jsonData   = array ();
    $upload_dir  = fz_config_get ('app', 'upload_dir');

    $available_from  = new Zend_Date ($_POST['start-from'], Zend_Date::DATE_SHORT);
    $available_until = clone ($available_from);
    $available_until->add ($_POST['duration'], Zend_Date::DAY);

    // We reserve a file id now to prevent id colision (very small probability, but...)
    $file_object = new fzFile ();
    $file_object->file_name        = $_FILES['file']['name'];
    $file_object->file_size        = $_FILES['file']['size'];
    $file_object->available_from   = $available_from;
    $file_object->available_until  = $available_until;
    $file_object->save ();

    $uploadErrors = array (
        UPLOAD_ERR_OK         => 'There is no error, the file uploaded with success.',
        UPLOAD_ERR_INI_SIZE   => 'The uploaded file exceeds the upload_max_filesize directive in php.ini.',
        UPLOAD_ERR_FORM_SIZE  => 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form.',
        UPLOAD_ERR_PARTIAL    => 'The uploaded file was only partially uploaded.',
        UPLOAD_ERR_NO_FILE    => 'No file was uploaded.',
        UPLOAD_ERR_NO_TMP_DIR => 'Missing a temporary folder.',
        UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk.',
        UPLOAD_ERR_EXTENSION  => 'File upload stopped by extension.',
    );

    // Let's move the file to its final destination
    if (move_uploaded_file ($_FILES['file']['tmp_name'], $upload_dir.'/'.$file_object->id)) {
        // returned data
        $jsonData['status']      = 'ok';
        $jsonData['status_text'] = 'The file has been successuly uploaded';
        $jsonData['file_info']   = print_r ($file_object, true);
    }
    else {
        // deleting previously created file
        $file_object->delete ();

        // returned data
        $jsonData['status']     = 'error';
        $jsonData['statusText'] = $_FILES['file']['error'] != 0 ? $uploadErrors [$_FILES['file']['error']] :
                                  'Can\'t move the file to "'.$upload_dir.'"';
        $jsonData['filename']   = $_FILES['file']['name'];
        $jsonData['debug']      = $_FILES['file'];
    }

    
    if (array_key_exists ('is_xhr', $_REQUEST) && $_REQUEST['is_xhr']) {
        // The response is embedded inside a textarea to prevent some browsers quirks.
        // JQuery Form Plugin will handle the response transparently.
        return fz_helper_action_is_xhr_request () ?
            json ($jsonData) :
            '<textarea>'.json_encode ($jsonData).'</textarea>'; 
    }
    else {
        // We can redirect the user to the home page
        // TODO set flash message
        redirect ('/');
    }
}

function fz_action_upload_get_progress () {
    $upload_id = params ('upload_id');
    if (!$upload_id)
        halt (NOT_FOUND, 'This upload does not exist');
      
    return json ((function_exists ('apc_fetch') ? apc_fetch ('upload_'.$upload_id) : false));
}

