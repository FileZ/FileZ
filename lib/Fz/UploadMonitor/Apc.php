<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Apc
 *
 * @author arno
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
?>
