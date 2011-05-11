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
 * Description of Fz_UploadMonitor_Apc
 *
 * @author Arnaud Didry <arnaud.didry@univ-avignon.fr>
 */
class Fz_UploadMonitor_Apc implements Fz_UploadMonitor {

    public function isInstalled () {
        return function_exists ('apc_fetch') && ini_get ('apc.rfc1867');
    }

    public function getProgress ($uploadId) {
        return apc_fetch ('upload_'.$uploadId);
    }

    public function getUploadIdName () {
        return 'APC_UPLOAD_PROGRESS';
    }
}
