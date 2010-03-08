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
    option ('session'   , 'filez'); // specific session name
    option ('views_dir' , option ('root_dir').'/app/views/');
    
    // Layout settings
    error_layout ('layout/error.html.php');
    layout       ('layout/default.html.php');

    require_once_dir (option ('lib_dir'));

    // error handling
    set_error_handler     ('fz_php_error_handler', E_ALL ^ E_NOTICE); // Log every error
    set_exception_handler ('fz_exception_handler'); // also handle uncatched excpeptions
}

/**
 * configuring Filez
 */
function before () {
    fz_config_load ();

    if (fz_config_get ('app', 'use_url_rewriting'))
      option ('base_uri', option ('base_path'));

    // error handling
    if (fz_config_get('app', 'debug', false)) {
        ini_set ('display_errors', true);
        option ('debug', true);
        option ('env', ENV_DEVELOPMENT);
    } else {
        ini_set ('display_errors', false);
    }

    // check log dir
    if (! is_writable (fz_config_get ('app', 'log_dir')))
        trigger_error ('Upload dir is not writeable "'
                  .fz_config_get ('app', 'log_dir').'"', E_USER_WARNING);

    // check upload dir
    if (! is_writable (fz_config_get ('app', 'upload_dir')))
        trigger_error ('Upload dir is not writeable "'
                  .fz_config_get ('app', 'upload_dir').'"', E_USER_ERROR);

    // Database configuration
    try {
        $db = new PDO (fz_config_get ('db', 'dsn'), fz_config_get ('db', 'user'),
                                              fz_config_get ('db', 'password'));
        $db->setAttribute (PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $db->exec ('SET NAMES \'utf8\'');
        option ('db_conn', $db);
    } catch (Exception $e) {
        halt (SERVER_ERROR, 'Can\'t connect to the database');
    }

    // I18N
    Zend_Locale::setDefault (fz_config_get ('app', 'default_locale'));
    $currentLocale = new Zend_Locale ('auto');
    $translate     = new Zend_Translate ('gettext', option ('root_dir').'/i18n', $currentLocale,
        array('scan' => Zend_Translate::LOCALE_DIRECTORY));
    option ('translate', $translate);
    option ('locale'   , $currentLocale);

    // Initialise and save the user factory
    $factoryClass = fz_config_get ('app', 'user_factory_class', 'Fz_User_Factory_Ldap');
    $userFactory = new $factoryClass ();
    $userFactory->setOptions (fz_config_get ('user_factory_options', null, array ()));
    option ('userFactory', $userFactory);
}


/**
 * Loading Limonade PHP
 */
require_once 'lib/limonade.php';
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
//fz_dispatch_get  ('/admin'                      ,'Admin'       ,'index');
fz_dispatch_get  ('/admin/checkFiles'           ,'Admin'       ,'checkFiles');

// Install controller
//fz_dispatch_get  ('/install'                    ,'Install'     ,'index'); // TODO

// Authentication controller
fz_dispatch_get  ('/login'                      ,'Auth'        ,'loginForm');
fz_dispatch_post ('/login'                      ,'Auth'        ,'login');
fz_dispatch_get  ('/logout'                     ,'Auth'        ,'logout');

// Filez-1.x url compatibility
fz_dispatch_get  ('/download.php'               ,'File'        ,'downloadFzOne');

// Download controller
fz_dispatch_get  ('/:file_hash'                 ,'File'        ,'preview');
fz_dispatch_get  ('/:file_hash/download'        ,'File'        ,'download');
fz_dispatch_post ('/:file_hash/download'        ,'File'        ,'download'); // with password

// File controller
fz_dispatch_get  ('/:file_hash/email'           ,'File'        ,'emailForm');
fz_dispatch_post ('/:file_hash/email'           ,'File'        ,'email');

fz_dispatch_get  ('/:file_hash/delete'          ,'File'        ,'confirmDelete');
fz_dispatch_post ('/:file_hash/delete'          ,'File'        ,'delete');

fz_dispatch_get  ('/:file_hash/extend'          ,'File'        ,'extend');

run ();

