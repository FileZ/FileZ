<?php

class App_Controller_Main extends Fz_Controller {

    public function indexAction () {
        set ('upload_id',        md5 (uniqid (mt_rand (), true)));
        set ('start_from',       Zend_Date::now ()->get (Zend_Date::DATE_SHORT));
        set ('files',            Fz_Db::getTable ('File')->findAll ()); // TODO findByOwner...
        set ('use_async_upload', (function_exists ('apc_fetch') && ini_get ('apc.rfc1867')));
        return html ('main/index.php');
    }
}
