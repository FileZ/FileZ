<?php

function fz_action_upload_start () {
    $jsonData   = array ();

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

    if (move_uploaded_file ($_FILES['file']['tmp_name'], option ('upload_dir').'/'.$file_object->id)) {
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
        $jsonData['statusText'] = print_r ($_FILES, true);
        $jsonData['filename']   = $_FILES['file']['name'];
        $jsonData['debug']      = $_FILES['file'];
    }

    // The response is embedded inside a textarea to prevent some browsers quirks.
    // JQuery Form Plugin will handle the response transparently.
    return fz_helper_action_is_xhr_request () ?
        json ($jsonData) :
        '<textarea>'.json_encode ($jsonData).'</textarea>'; 
}

function fz_action_upload_get_progress () {
    $upload_id = params ('upload_id');
    if (!$upload_id)
        halt (NOT_FOUND, 'This upload does not exist');
      
    return json ((function_exists ('apc_fetch') ? apc_fetch ('upload_'.$upload_id) : false));
}

