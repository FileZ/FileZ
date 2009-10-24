<?php 

// Loading Limonade
require_once 'lib/limonade.php';

// Loading Zend for i18n classes
set_include_path (get_include_path ().PATH_SEPARATOR.dirname (__FILE__).'/lib');
require_once 'Zend/Locale.php';
require_once 'Zend/Date.php';

function configure() // Called by limonade
{
    $root_dir = dirname (app_file());
    option ('session'           , 'filez'); // enable with a specific session name
    option ('base_uri'          , option ('base_path')); // because we use url_rewriting
    option ('views_dir'         , option ('root_dir').'/app/view/');
    option ('controllers_dir'   , option ('root_dir').'/app/controller/');
    option ('models_dir'        , option ('root_dir').'/app/model/');
    option ('upload_dir'        , option ('root_dir').'/uploaded_files/');

    // Database configuration // TODO utiliser le fichier de conf
    $db = new PDO('mysql:host=localhost;dbname=filez', 'filez', 'filez');
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

