<?php

/**
 * @file
 * Front to the FileZ application. This file loads Zend, Limonade and define the URL schema.
 * 
 * This file loads Zend, 
 * which autoloads Fz_* classes in lib/ dir and App_Model_* & App_Controller_* classes in app/ dir, 
 * configure and loads Limonade PHP framework, 
 * configure FileZ and dispatch the user according to the Url Schema. 
 *
 * All FileZ code is released under the GNU General Public License.
 * See COPYING and LICENSE in doc/ directory.
 * 
 * @package FileZ
 * -------------
 * @mainpage
 * <center>
 * See also: <a href="http://gpl.univ-avignon.fr/filez">gpl.univ-avignon.fr/FileZ</a> - <a href="https://github.com/UAPV/FileZ">README, issues & wiki on github</a>
 * </center>
 *
 * @htmlonly
 * <style type="text/css">h2{position: relative;left: -15px;font-size: 140%;}</style>
 * @endhtmlonly
 *
 * @section Model–view–controller
 *
 * Filez has been developed around the MVC pattern thanks to the Limonade micro framework.
 *
 * Limonade micro framework provide the glue between the controllers and views : * Routes declarations * Request handler/dispatcher (index.php) * and many action helpers
 * - Domain logic is implemented in 'app/model/DOMAIN_OBJECT.php' files.
 * - Controllers & actions reside in 'app/controller/CONTROLLER_NAME.php' files and contain a set of functions (actions). Fz_Controller
 * - Views are raw php files stored in 'app/view/CONTROLLER_NAME/ACTION_NAME.php' Static files are stored in the 'resource' directory.
 * 
 * See also <a href="https://github.com/UAPV/FileZ/blob/master/doc/README.DEV.markdown">doc/README.DEV.markdown</a>
 * 
 * @section About
 * 
 * - See <a href="http://gpl.univ-avignon.fr">gpl.univ-avignon.fr</a> for more information
 *
 * @section Copyright
 * 
 * Copyright 2010 Université d'Avignon et des Pays de Vaucluse, Arnaud Didry and others.
 * 
 */

define ('FZ_VERSION', '3.0-alpha');

/**
 * Loading Zend for i18n classes and autoloader
 */
set_include_path (get_include_path ()
    .PATH_SEPARATOR.dirname (__FILE__).DIRECTORY_SEPARATOR.'lib'
    .PATH_SEPARATOR.dirname (__FILE__).DIRECTORY_SEPARATOR.'lib'.DIRECTORY_SEPARATOR.'pear'
    .PATH_SEPARATOR.dirname (__FILE__).DIRECTORY_SEPARATOR.'plugins');

require_once 'Zend/Loader/Autoloader.php';
// Autoloading for Fz_* classes in lib/ dir
Zend_Loader_Autoloader::getInstance ()->registerNamespace ('Fz_');
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
    option ('views_dir' , option ('root_dir').DIRECTORY_SEPARATOR.'app'.DIRECTORY_SEPARATOR.'views'.DIRECTORY_SEPARATOR);
    
    // Layout settings
    error_layout ('layout'.DIRECTORY_SEPARATOR.'error.html.php');
    layout       ('layout'.DIRECTORY_SEPARATOR.'default.html.php');

    require_once_dir (option ('lib_dir'));

    // error handling
    set_error_handler     ('fz_php_error_handler', E_ALL ^ E_NOTICE); // Log every error
    set_exception_handler ('fz_exception_handler'); // also handle uncatched excpeptions
}

/**
 * configuring Filez
 */
function before () {

    if (fz_config_get ('app', 'use_url_rewriting'))
      option ('base_uri', option ('base_path'));

    // error handling
    if (fz_config_get('app', 'debug', false)) {
        ini_set ('display_errors', true);
        option ('debug', true);
        option ('env', ENV_DEVELOPMENT);
    } else {
        ini_set ('display_errors', false);
        option ('debug', false);
    }

    // I18N
    Zend_Locale::setDefault (fz_config_get ('app', 'default_locale', 'fr'));
    $currentLocale = new Zend_Locale ('auto');
    $translate     = new Zend_Translate ('gettext', option ('root_dir').DIRECTORY_SEPARATOR.'i18n', $currentLocale,
        array('scan' => Zend_Translate::LOCALE_DIRECTORY));
    option ('translate', $translate);
    option ('locale'   , $currentLocale);
    Zend_Registry::set('Zend_Locale', $currentLocale);

    // Execute DB configuration only if Filez is configured
    if (! option ('installing')) {
        
        // check log dir
        if (! is_writable (fz_config_get ('app', 'log_dir')))
            trigger_error ('Log dir is not writeable "'
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
            if (fz_config_get ('db', 'db_dialect') != "Sqlite") {
                $db->exec ('SET NAMES \'utf8\'');
            }
            option ('db_conn', $db);
        } catch (Exception $e) {
            halt (SERVER_ERROR, 'Can\'t connect to the database');
        }

        // Initialise and save the user factory
        $factoryClass = fz_config_get ('app', 'user_factory_class');
        $userFactory = new $factoryClass ();
        $userFactory->setOptions (fz_config_get ('user_factory_options', null, array ()));
        option ('userFactory', $userFactory);

        // Check the database version and migrate if necessary
        $dbSchema = new Fz_Db_Schema (option ('root_dir').'/config/db');
        if ($dbSchema->isOutdated ()) {
            fz_log ('Migration needed (db_version: '.$dbSchema->getCurrentVersion ().'), executing the scripts...');
            $dbSchema->migrate ();
        }
    }
}


/**
 * Loading Limonade PHP
 */
require_once 'lib/limonade.php';
require_once 'lib/fz_limonade.php';
require_once 'lib/fz_config.php';

// Check config presence, if not, force the user to the install controller
if (fz_config_load (dirname(__FILE__).DIRECTORY_SEPARATOR.'config'.DIRECTORY_SEPARATOR) === false) {
    option ('installing', true);
    fz_dispatch_get  ('/configure', 'Install', 'configure');
    fz_dispatch_post ('/configure', 'Install', 'configure');
    fz_dispatch_get  ('/*',         'Install', 'prepare');
    run ();
    exit;
}


//                                              //             // 
// Url Schema                                   // Controller  // Action
//                                              //             // 
// -----------------------------------------------------------------------------
// Main controller
fz_dispatch ('/'                                ,'Main'        ,'index');

// Upload controller
fz_dispatch_post ('/upload'                     ,'Upload'      ,'start');
fz_dispatch_get  ('/upload/progress/:upload_id' ,'Upload'      ,'getProgress');

// Backend controller
fz_dispatch_get  ('/admin'                      ,'Admin'       ,'index');

// Backend::Users
fz_dispatch_get  ('/admin/users'                ,'User'        ,'index');
fz_dispatch_get  ('/admin/users/new'            ,'User'        ,'create');
fz_dispatch_post ('/admin/users/new'            ,'User'        ,'postnew');
fz_dispatch_get  ('/admin/users/:id'            ,'User'        ,'show');
fz_dispatch_get  ('/admin/users/:id/delete'     ,'User'        ,'delete');
fz_dispatch_get  ('/admin/users/:id/edit'       ,'User'        ,'edit');
fz_dispatch_post ('/admin/users/:id/edit'       ,'User'        ,'update');

// Backend::Files
fz_dispatch_get  ('/admin/files'                ,'Admin'       ,'files');
fz_dispatch_get  ('/admin/config'               ,'Admin'       ,'config');
fz_dispatch_get  ('/admin/invitations'          ,'Admin'       ,'invitations');

// Backend::CRON
fz_dispatch_get  ('/admin/checkFiles'           ,'Admin'       ,'checkFiles');

// Authentication controller
fz_dispatch_get  ('/login'                      ,'Auth'        ,'loginForm');
fz_dispatch_post ('/login'                      ,'Auth'        ,'login');
fz_dispatch_get  ('/logout'                     ,'Auth'        ,'logout');

// Filez-1.x url compatibility
fz_dispatch_get  ('/download.php'               ,'File'        ,'downloadFzOne');

// User documentation
fz_dispatch_get  ('/help'                       ,'Help'        ,'index');
fz_dispatch_get  ('/help/:page'                 ,'Help'        ,'showPage');

// Download controller
fz_dispatch_get  ('/:file_hash'                 ,'File'        ,'preview');
fz_dispatch_get  ('/:file_hash/view'            ,'File'        ,'view');
fz_dispatch_get  ('/:file_hash/download'        ,'File'        ,'download');
fz_dispatch_post ('/:file_hash/download'        ,'File'        ,'download'); // with password

// File controller
fz_dispatch_get  ('/:file_hash/email'           ,'File'        ,'emailForm');
fz_dispatch_post ('/:file_hash/email'           ,'File'        ,'email');

fz_dispatch_get  ('/:file_hash/delete'          ,'File'        ,'confirmDelete');
fz_dispatch_post ('/:file_hash/delete'          ,'File'        ,'delete');

fz_dispatch_get  ('/:file_hash/extend'          ,'File'        ,'extend');

fz_dispatch_get  ('/:file_hash/invite'          ,'File'        ,'invite');
fz_dispatch_get  ('/:file_hash/:invitation_hash','File'        ,'invited');

run ();
