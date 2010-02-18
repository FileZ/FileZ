<?php

/**
 * Main controller
 */
class App_Controller_Main extends Fz_Controller {

    public function indexAction () {
        $this->secure ();
        $user = $this->getUser ();
        set ('upload_id'   , md5 (uniqid (mt_rand (), true)));
        set ('start_from'  , Zend_Date::now ()->get (Zend_Date::DATE_SHORT));
        set ('refresh_rate', 2000);
        set ('files'       , Fz_Db::getTable ('File')
                              ->findByOwnerOrderByUploadDateDesc ($user['id']));
        set ('use_progress_bar', (function_exists ('apc_fetch')
                                 && ini_get ('apc.rfc1867')));
        return html ('main/index.php');
    }
}
