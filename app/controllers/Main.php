<?php

/**
 * Main controller
 */
class App_Controller_Main extends Fz_Controller {

    public function indexAction () {
        $this->secure ();
        $user = $this->getUser ();
        $freeSpaceLeft = max (0, Fz_Db::getTable('File')->getRemainingSpaceForUser ($user));
        $maxUploadSize = min (
             Fz_Db::getTable('File')->shorthandSizeToBytes (ini_get ('upload_max_filesize')),
             Fz_Db::getTable('File')->shorthandSizeToBytes (ini_get ('post_max_size')),
                $freeSpaceLeft);

        set ('upload_id'   , md5 (uniqid (mt_rand (), true)));
        set ('start_from'  , Zend_Date::now ()->get (Zend_Date::DATE_SHORT));
        set ('refresh_rate', 700);
        set ('files'       , Fz_Db::getTable ('File')
                              ->findByOwnerOrderByUploadDateDesc ($user['id']));
        set ('use_progress_bar', (function_exists ('apc_fetch') && ini_get ('apc.rfc1867')));
        set ('free_space_left', $freeSpaceLeft);
        set ('max_upload_size', $maxUploadSize);
        return html ('main/index.php');
    }
}
