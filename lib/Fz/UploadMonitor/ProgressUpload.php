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
 * Description of Fz_UploadMonitor_ProgressUpload
 *
 * @author Arnaud Didry <arnaud.didry@univ-avignon.fr>
 */
class Fz_UploadMonitor_ProgressUpload implements Fz_UploadMonitor {

    function isInstalled () {
        return function_exists ('uploadprogress_get_info');
    }

    function getProgress ($uploadId) {
        $progress = uploadprogress_get_info ($uploadId);
        
        return (!is_array ($progress) ?
            null :
            array ('total'   => $progress ['bytes_total'],
                   'current' => $progress ['bytes_uploaded']));
    }

    public function getUploadIdName () {
        return 'UPLOAD_IDENTIFIER';
    }
}
