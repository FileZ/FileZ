<?php

/**
 * Controller used for administratives tasks
 */
class App_Controller_Install extends Fz_Controller {


    /**
     *
     */
    public function indexAction () {
        return $this->configFormAction();
    }

    /**
     *
     */
    public function configFormAction () {

        $config = fz_config_get();

        //
        $locales_choices = array();
        foreach (glob (option ('root_dir').'/i18n/*', GLOB_ONLYDIR) as $lc)
            $locales_choices [basename ($lc)] = basename ($lc);

        $errors = array ();
        $notifs = array ();

        // If request is post, check for errors
        if (request_is_post()) {

            $config = merge_config($_POST['config'], $config);

            // checking rights
            $this->checkRights (&$errors, &$config);

            // Checking database connection
            $this->checkDatabaseConf ($errors, $config);

            // Checking User factory connection
            if ($config['app']['user_factory_class'] == 'Fz_User_Factory_Ldap')
                $this->checkUserFactoryLdapConf ($errors, $config);
            elseif ($config['app']['user_factory_class'] == 'Fz_User_Factory_Database')
                  $this->checkUserFactoryDatabaseConf ($errors, $config);

            // Checking email
            $this->checkEmailConf ($errors, $config);

            // If no errors or if the user ignored them, save the config and create
            // the database
            if (empty ($errors) || array_key_exists ('ignore_errors', $_POST)) {

                //$errors = array (); // Reset errors.

                // Try to save the file or display it
                $configFile = option ('root_dir').'/config/filez.ini';
                if (! fz_config_save ($config, $file)) {
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

                // Check if database exists and create it
                if (! $this->databaseExists()) {
                    $initDbScript = option ('root_dir').'/config/db/schema.sql';
                } else {
                    // TODO Migrate Database
                    // get Db version, run migration script
                }
                if (! empty ($initDbScript)) {
                    try {
                        if (option ('db_conn') === null)
                            throw new Exception ('Database connection not found.');

                        $sql = file_get_contents ($initDbScript, FILE_TEXT);
                        if ($sql === false)
                            throw new Exception ('Database script not found "'.$initDbScript.'"');

                        option ('db_conn')->exec ($sql);

                        $notifs [] = 'Database configured ';
                    } catch (Exception $e) {
                        $errors [] = array (
                            'title' => 'Can\'t initialize the database ('.$e->getMessage ().')',
                            'msg'   => 'Check your database configuration in config/filez.ini and re-run the SQL script "'.
                                        $initDbScript.'".'
                        );
                    }
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
     *
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
     *
     */
    public function checkUserFactoryDatabaseConf (&$errors, &$config) {
        $oldDb = option ('db_conn'); // save filez db connection
        try {
            if (array_key_exists ('db_use_global_conf', $config['user_factory_options'])) {
                $db = new PDO ($config['user_factory_options']['db_server_dsn'],
                               $config['user_factory_options']['db_server_user'],
                               $config['user_factory_options']['db_server_password']);

                $db->setAttribute (PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                $db->exec ('SET NAMES \'utf8\'');
                option ('db_conn', $db);
            }
        } catch (Exception $e) {
            $errors [] = array (
                'title' => 'Can\'t connect to the user database',
                'msg'   => $e->getMessage ()
            );
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

        option ('db_conn', $oldDb); // save filez db connection
    }

    /**
     *
     */
    public function checkUserFactoryLdapConf (&$errors, &$config) {
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
     *
     */
    public function databaseExists () {
        $sql = 'SELECT * '
              .'FROM information_schema.tables '
              .'WHERE table_name=\'fz_file\' ';

        $res = Fz_Db::findAssocBySQL($sql);
        return (count ($res) > 0);
    }
}