<?php 

/**
 * Loading Zend for i18n classes and autoloader
 */
set_include_path (get_include_path ()
    .PATH_SEPARATOR.dirname (__FILE__).DIRECTORY_SEPARATOR.'lib'
    .PATH_SEPARATOR.dirname (__FILE__).DIRECTORY_SEPARATOR.'lib'.DIRECTORY_SEPARATOR.'pear'
    .PATH_SEPARATOR.dirname (__FILE__).DIRECTORY_SEPARATOR.'plugins');

require_once 'Zend/Loader/Autoloader.php';
// Autoloading for Fz_* classes in lib/ dir
Zend_Loader_Autoloader::getInstance ()->registerNamespace ('Fz_'); ;
// Autoloading for App_Model_* & App_Controller_* classes in app/ dir
//(automagicaly added to Zend autoloaders)
$autoloader = new Zend_Application_Module_Autoloader (array (
    'namespace' => 'App',
    'basePath'  => 'app',
));
$autoloader->addResourceTypes (array ('controller' => array (
    'namespace' => 'Controller',
    'path'      => 'controllers',
)));

/**
 * Configuration of the limonade framework. Automatically called by run()
 */
function configure() {
    option ('session'           , 'filez'); // specific session name
    option ('views_dir'         , option ('root_dir').'/app/views/');
    option ('upload_dir'        , option ('root_dir').'/uploaded_files/');

    require_once_dir (option ('lib_dir'));

    $fz_conf = fz_config_load (); // loading filez.ini
    if ($fz_conf ['app']['use_url_rewriting'])
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
    option ('locale', new Zend_Locale ('auto'));

    // Layout settings
    error_layout ('layout/error.html.php');
    layout       ('layout/default.html.php');
}


/**
 * Loading Limonade PHP
 */
require_once 'lib/limonade.php';
error_reporting(E_ALL);
ini_set ('display_errors',TRUE);

require_once 'lib/fz_limonade.php';


//                                              //             // 
// Url Schema                                   // Controller  // Action
//                                              //             // 
// ---------------------------------------------------------------------------
// Main controller
fz_dispatch ('/'                                ,'Main'        ,'index');

// Upload controller
fz_dispatch_post ('/upload'                     ,'Upload'      ,'start');
fz_dispatch_get  ('/upload/progress/:upload_id' ,'Upload'      ,'getProgress');

// Backend controller
fz_dispatch_get  ('/admin'                      ,'Admin'       ,'index');

// Install controller
fz_dispatch_get  ('/install'                    ,'Install'     ,'index');

// Download controller
fz_dispatch_get ('/:file_hash'                  ,'Download'    ,'preview');
fz_dispatch_get ('/:file_hash/download'         ,'Download'    ,'start');

// File controller
fz_dispatch_get ('/:file_hash/email'            ,'File'        ,'email');
fz_dispatch_get ('/:file_hash/delete'           ,'File'        ,'delete');

// Filez-1.x url compatibility
fz_dispatch_get ('/download.php'                ,'Download'    ,'startFzOne');

run ();

