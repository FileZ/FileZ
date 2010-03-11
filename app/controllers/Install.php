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

        // If request is post, check for errors
        if (request_is_post()) {

            $config = merge_config($_POST['config'], $config);
            $db = null;

            // checking rights
            if (! is_writable($config['app']['upload_dir']))
                $errors [] = array (
                    'title' => 'Upload directory is not writeable by the web server',
                );

            if (! is_writable($config['app']['log_dir']))
                $errors [] = array (
                    'title' => 'Logs directory is not writeable by the web server',
                );

            // Checking database connection
            try {
                $db = new PDO ($config['db']['dsn'],
                               $config['db']['user'],
                               $config['db']['password']);
            } catch (Exception $e) {
                $errors [] = array (
                    'title' => 'Can\'t connect to the database',
                    'msg'   => $e->getMessage ()
                );
            }

            // Checking User factory connection
            if ($config['app']['user_factory_class'] == 'Fz_User_Factory_Ldap') {
                try {
                    $ldap = new Zend_Ldap ($config['user_factory_options']);
                    $ldap->bind();
                } catch (Exception $e) {
                    $errors [] = array (
                        'title' => 'Can\'t connect to the ldap server',
                        'msg'   => $e->getMessage ()
                    );
                }
            } elseif ($config['app']['user_factory_class'] == 'Fz_User_Factory_Database'
                  && array_key_exists ('db_use_global_conf', $config['user_factory_options'])) {

                try {
                    $db = new PDO ($config['user_factory_options']['db_server_dsn'],
                                   $config['user_factory_options']['db_server_user'],
                                   $config['user_factory_options']['db_server_password']);

                    $db->setAttribute (PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                    $db->exec ('SET NAMES \'utf8\'');
                    option ('db_conn', $db);

                    try {
                        $sql = 'SELECT * FROM '.$config['user_factory_options']['db_table']
                           .' WHERE '.$config['user_factory_options']['db_username_field'].' LIKE \'%\''
                           .' AND   '.$config['user_factory_options']['db_password_field'].' LIKE \'%\'';
                        try {
                            $result = Fz_Db::findObjectBySQL ($sql, PDO::FETCH_ASSOC);
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
                } catch (Exception $e) {
                    $errors [] = array (
                        'title' => 'Can\'t connect to the user database',
                        'msg'   => $e->getMessage ()
                    );
                }
            }

            // Checking email
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

            if (empty ($errors))
                flash_now ('notif', 'Configuration seems OK !');
            else
                set ('errors', $errors);
        }

        set ('config', $config);
        set ('locales_choices', $locales_choices);
        return html ('install/index.php');
    }
}