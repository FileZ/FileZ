<?php 

/**
 * Loading Zend for i18n classes and autoloader
 */
$base = PATH_SEPARATOR.dirname (__FILE__).PATH_SEPARATOR;
set_include_path (get_include_path ().$base.'lib');

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
 * Loading Limonade PHP
 */
require_once 'lib/limonade.php';

/**
 * Configuration of the limonade framework. Automatically called.
 */
function configure()
{
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


// Main controller
dispatch ('/'                               , array ('App_Controller_Main'     ,'indexAction'));

// Upload controller
dispatch_post ('/upload'                    , array ('App_Controller_Upload'   ,'startAction'));
dispatch_get  ('/upload/progress/:upload_id', array ('App_Controller_Upload'   ,'get_progressAction'));

// Backend controller
dispatch_get  ('/admin'                     , array ('App_Controller_Admin'    ,'indexAction'));

// Install controller
dispatch_get  ('/install'                   , array ('App_Controller_Install'  ,'indexAction'));

// Download controller
dispatch_get ('/:file_hash'                 , array ('App_Controller_Download' ,'previewAction'));
dispatch_get ('/:file_hash/download'        , array ('App_Controller_Download' ,'startAction'));

// File controller
dispatch_get ('/:file_hash/email'           , array ('App_Controller_File'     ,'emailAction'));
dispatch_get ('/:file_hash/delete'          , array ('App_Controller_File'     ,'deleteAction'));

// Filez-1.x url compatibility. à tester
dispatch_get ('/download.php'               , array ('App_Controller_Download' ,'startFzOneAction'));

run ();

