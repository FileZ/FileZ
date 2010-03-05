<?php

/**
 * Main controller
 */
class App_Controller_Main extends Fz_Controller {

    public function indexAction () {
        $this->secure ();
        $user = $this->getUser ();
        $t = Fz_Db::getTable('File')->bytesToShorthand (100);
        $t = Fz_Db::getTable('File')->bytesToShorthand (100000);
        $t = Fz_Db::getTable('File')->bytesToShorthand (100000000);
        $t = Fz_Db::getTable('File')->bytesToShorthand (100000000000);
        $freeSpaceLeft = Fz_Db::getTable('File')->getRemainingSpaceForUser ($user);
        $freeSpaceLeftShorthand = Fz_Db::getTable('File')->bytesToShorthand ($freeSpaceLeft);
        set ('upload_id'   , md5 (uniqid (mt_rand (), true)));
        set ('start_from'  , Zend_Date::now ()->get (Zend_Date::DATE_SHORT));
        set ('refresh_rate', 700);
        set ('files'       , Fz_Db::getTable ('File')
                              ->findByOwnerOrderByUploadDateDesc ($user['id']));
        set ('use_progress_bar', (function_exists ('apc_fetch')
                                 && ini_get ('apc.rfc1867')));
        set ('free_space_left', $freeSpaceLeftShorthand);
        set ('max_upload_size', Fz_Db::getTable('File')->bytesToShorthand (min (
             Fz_Db::getTable('File')->shorthandSizeToBytes (ini_get ('upload_max_filesize')),
             Fz_Db::getTable('File')->shorthandSizeToBytes (ini_get ('post_max_size')),
                $freeSpaceLeft)));
        set ('max_upload_size', ini_get ('upload_max_filesize'));
        return html ('main/index.php');
    }
}
