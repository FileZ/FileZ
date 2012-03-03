<?php

/**
 * @file
 * This file is the main controller, it secures and print the main page
 * 
 * This file define the main controller that is called while visiting FileZ main page.
 * The controller checks the user, set some parameters 
 * and return the main/index.php view.
 * 
 * @package FileZ
 */

/**
 * Main controller
 */
class App_Controller_Main extends Fz_Controller {

    public function indexAction () {
	// Display the send_us_a_file.html page if the "Send us a file" feature is on and the user is not logged in. 
	if ( fz_config_get ('app', 'send_us_a_file_feature') && false == $this->getUser () ) {
                set ('start_from'       , Zend_Date::now ()->get (Zend_Date::DATE_SHORT));
                $maxUploadSize = min (
                  Fz_Db::getTable('File')->shorthandSizeToBytes (ini_get ('upload_max_filesize')),
                  Fz_Db::getTable('File')->shorthandSizeToBytes (ini_get ('post_max_size')) );
                set ('max_upload_size'   , $maxUploadSize);
	        return html ('send_us_a_file.html');
	}

        $this->secure ();
        $user = $this->getUser ();
        $freeSpaceLeft = max (0, Fz_Db::getTable('File')->getRemainingSpaceForUser ($user));
        $maxUploadSize = min (
             Fz_Db::getTable('File')->shorthandSizeToBytes (ini_get ('upload_max_filesize')),
             Fz_Db::getTable('File')->shorthandSizeToBytes (ini_get ('post_max_size')),
                $freeSpaceLeft);

        $progressMonitor = fz_config_get ('app', 'progress_monitor');
        $progressMonitor = new $progressMonitor ();
        
        set ('upload_id'            , md5 (uniqid (mt_rand (), true)));
        set ('start_from'           , Zend_Date::now ()->get (Zend_Date::DATE_SHORT));
        set ('refresh_rate'         , 1200);
        set ('files'                , Fz_Db::getTable ('File')
                                        ->findByOwnerOrderByUploadDateDesc ($user));
        set ('use_progress_bar'     , $progressMonitor->isInstalled ());
        set ('upload_id_name'       , $progressMonitor->getUploadIdName ());
        set ('free_space_left'      , $freeSpaceLeft);
        set ('max_upload_size'      , $maxUploadSize);
        set ('sharing_destinations' , fz_config_get ('app', 'sharing_destinations', array()));
        set ('disk_usage'           , array (
            'space' => '<b id="disk-usage-value">'.bytesToShorthand (Fz_Db::getTable('File')->getTotalDiskSpaceByUser ($user)).'</b>',
            'quota' => fz_config_get('app', 'user_quota')));
        return html ('main/index.php');
    }
}
