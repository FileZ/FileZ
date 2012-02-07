<?php

/**
 * @file
 * Short description.
 * 
 * Long description.
 * 
 * @package FileZ
 */

/**
 * Controller used for administratives tasks
 */
class App_Controller_Install extends Fz_Controller {


    /**
     *
     */
    public function prepareAction () {

        $checks = array ();

        // mod_rewrite
        if ($this->checkModRewrite() !== true)
            $checks ['mod_rewrite'] = "<p><span class=\"required\">Mod rewrite is required by Filez</span> ".
                "but wasn't found on your system.</p>".
                "<p>To enable it, run the following command as root :</p>".
                "<pre>a2enmod rewrite && apache2ctl restart</pre>";

        // php.ini settings
        $checks ['upload_max_filesize'] =
            '<p>php.ini value of "upload_max_filesize" is set to "'.ini_get ('upload_max_filesize')."\" </p>".
            "<p>To change it, edit your php.ini or add the following line in your apache virtual host configuration or '.htaccess' file :</p>".
            "<pre>php_admin_value upload_max_filesize 750M</pre>";

        // php.ini settings
        $checks ['post_max_size'] =
            '<p>php.ini value of "post_max_size" is set to "'.ini_get ('post_max_size')."\" </p>".
            "<p>To change it, edit your php.ini or add the following line in your apache virtual host configuration or '.htaccess' file :</p>".
            "<pre>php_admin_value post_max_size 750M</pre>";

        // php.ini settings
        $checks ['max_execution_time'] =
            '<p>php.ini value of "max_execution_time" is set to "'.ini_get ('max_execution_time')."\" </p>".
            "<p>To change it, edit your php.ini or add the following line in your apache virtual host configuration or '.htaccess' file :</p>".
            "<pre>php_admin_value max_execution_time 1200</pre>";

        // php.ini settings
        $checks ['upload_tmp_dir'] =
            '<p>php.ini value of "upload_tmp_dir" is set to "'.ini_get ('upload_tmp_dir')."\" </p>".
            "<p>You should check if there is enough place on the device</p>".
            "<p>To change it, edit your php.ini or add the following line in your apache virtual host configuration or '.htaccess' file :</p>".
            '<pre>php_admin_value upload_tmp_dir "/media/data/tmp"</pre>';

        set ('checks', $checks);
        return html ('install/prerequisites.php');
    }

    /**
     *
     */
    public function configureAction () {

        $config = fz_config_get();

        //
        $locales_choices = array();
        foreach (glob (option ('root_dir').'/i18n/*', GLOB_ONLYDIR) as $lc)
            $locales_choices [basename ($lc)] = basename ($lc);

        $errors = array ();
        $notifs = array ();

        // If request is post, check for errors
        if (request_is_post()) {

            // prevent unchecked input from being transformed to true when merging config
            $_POST['config']['looknfeel']['show_credit'] = (
                array_key_exists ('show_credit', $_POST['config']['looknfeel']) ? 1 : 0);
            $config = merge_config($_POST['config'], $config);

            // checking rights
            $this->checkRights ($errors, $config);

            // Checking database connection
            $this->checkDatabaseConf ($errors, $config);

            // If Upload monitoring lib is selected check if it's installed
            if ($config['app']['progress_monitor'] != '') {
                $progressMonitor = $config['app']['progress_monitor'];
                $progressMonitor = new $progressMonitor ();
                if (! $progressMonitor->isInstalled ())
                    $errors [] = array (
                        'title' => 'Your system is not configured for '.get_class ($progressMonitor),
                        'msg'   => 'Read <a href="http://github.com/UAPV/FileZ/blob/master/doc/INSTALL.markdown" target="_blank">the INSTALL file</a> for help'
                    );
            }

            // Is CAS authentication, check requirements
            if ($config['app']['auth_handler_class'] == 'Fz_Controller_Security_Cas'
                && ! function_exists ('curl_init'))
                $errors [] = array (
                    'title' => 'PHP extension "cURL" is required for CAS authentication but is not installed',
                    'msg'   => 'Use php5-curl on debian to install it');

            // Checking User factory connection
            if ($config['app']['user_factory_class'] == 'Fz_User_Factory_Ldap')
                $this->checkUserFactoryLdapConf ($errors, $config);
	    // do not check user factory if database.
            //elseif ($config['app']['user_factory_class'] == 'Fz_User_Factory_Database')
            //    $this->checkUserFactoryDatabaseConf ($errors, $config);

            // Checking email
            $this->checkEmailConf ($errors, $config);

            // If no errors or if the user ignored them, save the config and create
            // the database
            if (empty ($errors) || array_key_exists ('ignore_errors', $_POST)) {

                //$errors = array (); // Reset errors.

                // Try to save the file or display it
                $configFile = option ('root_dir').DIRECTORY_SEPARATOR.'config'.DIRECTORY_SEPARATOR.'filez.ini';
                if (! fz_config_save ($config, $configFile)) {
                    $errors [] = array (
                        'title' => 'Can\'t save filez.ini.',
                        'msg'   => 'Put the following code in the file "'
                                    .$configFile.'" :<textarea cols="60" rows="50">'
                                     .fz_serialize_ini_array ($config, true)
                                  .'</textarea>'
                    );
                } else {
                    $notifs [] = 'Created file "'.$configFile.'"';
                }

                try {
                    $this->initDatabase();
                    $notifs [] = 'Database configured.<br/><br/>
A default admin account has been created. Login ("<tt>admin</tt>" / "<tt>filez</tt>") and choose a new password.';
                } catch (Exception $e) {
                    $errors [] = array (
                        'title' => 'Can\'t initialize the database ('.$e->getMessage ().')',
                        'msg'   => 'Check your database configuration in config/filez.ini and re-run the SQL script "'.
                                    $initDbScript.'".'
                    );
                }

                set ('errors', $errors);
                set ('notifs', $notifs);
                return html ('install/finished.php');
            }

            if (! empty ($errors))
                set ('errors', $errors);
        }

        set ('config', $config);
        set ('locales_choices', $locales_choices);
        return html ('install/index.php');
    }

    /**
     * Check if we can send an email to the administrator
     */
    public function checkEmailConf (&$errors, &$config) {
        try {
            $config['email']['name'] = 'filez';
            $transport = new Zend_Mail_Transport_Smtp ($config ['email']['host'], $config['email']);
            $mail = new Zend_Mail ('utf-8');
            $mail->setFrom ($config ['email']['from_email'], $config ['email']['from_name']);
            $mail->addTo($config ['app']['admin_email']);
            $mail->setSubject('[Filez] Check One Two...');
            $mail->setBodyText("Hello, I'm just checking email configuration for Filez.");
            $mail->send ($transport);
        } catch (Exception $e) {
            $errors [] = array (
                'title' => 'Can\'t use SMTP server',
                'msg'   => $e->getMessage ()
            );
        }
    }

    /**
     * Check if we can connect to the database user factory
     *
     */
    public function checkUserFactoryDatabaseConf (&$errors, &$config) {
        $oldDb = option ('db_conn'); // save filez db connection

        if (! array_key_exists ('db_use_global_conf', $config['user_factory_options']) ||
            $config['user_factory_options']['db_use_global_conf'] == false) {
            try {
                $db = new PDO ($config['user_factory_options']['db_server_dsn'],
                               $config['user_factory_options']['db_server_user'],
                               $config['user_factory_options']['db_server_password']);

                $db->setAttribute (PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                $db->exec ('SET NAMES \'utf8\'');
                option ('db_conn', $db);
            } catch (Exception $e) {
                $errors [] = array (
                    'title' => 'Can\'t connect to the user database',
                    'msg'   => $e->getMessage ()
                );
            }
        }

        try {
            $sql = 'SELECT * FROM '.$config['user_factory_options']['db_table']
               .' WHERE '.$config['user_factory_options']['db_username_field'].' LIKE \'%\''
               .' AND   '.$config['user_factory_options']['db_password_field'].' LIKE \'%\'';
            try {
                $result = Fz_Db::findAssocBySQL ($sql);
            } catch (Exception $e) {
                $errors [] = array (
                    'title' => 'Can\'t fetch data from the user table',
                    'msg'   => $e->getMessage ()
                );
            }
        } catch (Exception $e) {
            $errors [] = array (
                'title' => 'Can\'t find the user table',
                'msg'   => $e->getMessage ()
            );
        }

        option ('db_conn', $oldDb); // restore filez db connection
    }

    /**
     * Check if we can connect to the ldap user factory
     *
     */
    public function checkUserFactoryLdapConf (&$errors, &$config) {
        if (! function_exists ('ldap_connect')) {
            $errors [] = array (
                'title' => 'PHP LDAP extension is not installed.',
                'msg'   => 'Use php5-ldap package on debian'
            );
            return;
        }
        try {

            $ldap = new Zend_Ldap ($config['user_factory_options']);
            $ldap->bind();
        } catch (Exception $e) {
            $errors [] = array (
                'title' => 'Can\'t connect to the ldap server',
                'msg'   => $e->getMessage ()
            );
        }
    }

    /**
     * Check if we can connect to the configured database
     *
     */
    public function checkDatabaseConf (&$errors, &$config) {
        try {
            $db = new PDO ($config['db']['dsn'],
                           $config['db']['user'],
                           $config['db']['password']);
            
            $db->setAttribute (PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $db->exec ('SET NAMES \'utf8\'');
            option ('db_conn', $db); // Store for later use
        } catch (Exception $e) {
            $errors [] = array (
                'title' => 'Can\'t connect to the database',
                'msg'   => $e->getMessage ()
            );
        }
    }

    /**
     * Checks if the upload dir and the log dir are writeable
     *
     */
    public function checkRights (&$errors, &$config) {
        if (! is_writable($config['app']['upload_dir']))
            $errors [] = array (
                'title' => 'Upload directory is not writeable by the web server',
            );

        if (! is_writable($config['app']['log_dir']))
            $errors [] = array (
                'title' => 'Logs directory is not writeable by the web server',
            );
    }

    /**
     * Tells if a rewrite module is enable on the server
     *
     * @return boolean or null if we can't find a hint
     */
    public function checkModRewrite ()
    {
        if (function_exists ('apache_get_modules'))
            return in_array ('mod_rewrite', apache_get_modules ());
        else if (getenv ('HTTP_MOD_REWRITE') == 'On')
            return true;
        else
            return null;
    }

    public function initDatabase () {
        if (option ('db_conn') === null)
            throw new Exception ('Database connection not found.');

        $schema = new Fz_Db_Schema (option ('root_dir').'/config/db');
        $schema->migrate ();
    }
}
