<?php 

// Loading Limonade
require_once 'lib/limonade.php';

// Loading Zend for i18n classes
set_include_path (get_include_path ().PATH_SEPARATOR.dirname (__FILE__).'/lib');
require_once 'Zend/Locale.php';
require_once 'Zend/Date.php';


/**
 * Configuration of the limonade framework. Automatically called.
 */
function configure()
{
    option ('session'           , 'filez'); // specific session name
    option ('views_dir'         , option ('root_dir').'/app/view/');
    option ('controllers_dir'   , option ('root_dir').'/app/controller/');
    option ('models_dir'        , option ('root_dir').'/app/model/');
    option ('upload_dir'        , option ('root_dir').'/uploaded_files/');

    require_once_dir (option ('lib_dir'));
    require_once_dir (option ('models_dir'));

    $fz_conf = fz_config_load (); // loading filez.ini
    if ($fz_conf['app']['use_url_rewriting'])
      option ('base_uri'          , option ('base_path'));

    // Database configuration
    $db = new PDO ($fz_conf['db']['dsn'], $fz_conf['db']['user'],
                                          $fz_conf['db']['password']);

    // TODO gérer les erreurs de connexion
    //$db->setAttribute (PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $db->setAttribute (PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    option ('db_conn', $db);

    // We save the locale for later use
    Zend_Locale::setDefault ('fr');
    option ('locale', new Zend_Locale('auto'));

    // Layout settings
    error_layout ('layout/error.html.php');
    layout       ('layout/default.html.php');
}


// Main controller
dispatch ('/'                               , 'fz_action_main_index');

// Upload controller
dispatch_post ('/upload'                    , 'fz_action_upload_start');
dispatch_get  ('/upload/progress/:upload_id', 'fz_action_upload_get_progress');

// Backend controller
dispatch_get  ('/admin'                     , 'fz_action_admin_index');

// Install controller
dispatch_get  ('/install'                   , 'fz_action_install_index');

// Download controller
dispatch_get ('/:file_hash'                 , 'fz_action_download_preview');
dispatch_get ('/:file_hash/download'        , 'fz_action_download_start');

run ();

