<?php
/**
 * Copyright 2010  Université d'Avignon et des Pays de Vaucluse 
 * email: gpl@univ-avignon.fr
 *
 * This file is part of Filez.
 *
 * Filez is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Filez is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Filez.  If not, see <http://www.gnu.org/licenses/>.
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

        // PHP module APC with apc.rfc1867
        if (! $this->checkApcInstalled() || ! $this->checkApcConfigured())
            $checks ['apc'] =
                '<p>APC PHP extension is not installed or misconfigured. '.
                "APC is only required if you want to display a progress bar during upload.</p>".
                "<p>To install it (the debian way) run the following commands as root :</p>".
                "<pre>apt-get install php-apc\n".
                "echo \"apc.rfc1867 = On\"   >> /etc/php5/apache2/conf.d/apc.ini\n".
                "apache2ctl restart</pre>";

        // php.ini settings
        $checks ['upload_max_filesize'] =
            '<p>php.ini value of "upload_max_filesize" is set to "'.ini_get ('upload_max_filesize')."\" </p>".
            "<p>To change it, edit your php.ini or add the following line in your apache virtual host configuration :</p>".
            "<pre>php_admin_value upload_max_filesize 750M</pre>";

        // php.ini settings
        $checks ['post_max_size'] =
            '<p>php.ini value of "post_max_size" is set to "'.ini_get ('post_max_size')."\" </p>".
            "<p>To change it, edit your php.ini or add the following line in your apache virtual host configuration :</p>".
            "<pre>php_admin_value post_max_size 750M</pre>";

        // php.ini settings
        $checks ['max_execution_time'] =
            '<p>php.ini value of "max_execution_time" is set to "'.ini_get ('max_execution_time')."\" </p>".
            "<p>To change it, edit your php.ini or add the following line in your apache virtual host configuration :</p>".
            "<pre>php_admin_value max_execution_time 1200</pre>";

        // php.ini settings
        $checks ['upload_tmp_dir'] =
            '<p>php.ini value of "upload_tmp_dir" is set to "'.ini_get ('upload_tmp_dir')."\" </p>".
            "<p>You should check if there is enough place on the device</p>".
            "<p>To change it, edit your php.ini or add the following line in your apache virtual host configuration :</p>".
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

            $config = merge_config($_POST['config'], $config);

            // checking rights
            $this->checkRights ($errors, $config);

            // Checking database connection
            $this->checkDatabaseConf ($errors, $config);

            // Is CAS authentication, check requirements
            if ($config['app']['auth_handler_class'] == 'Fz_Controller_Security_Cas'
                && ! function_exists ('curl_init'))
                $errors [] = array (
                    'title' => 'PHP extension "cURL" is required for CAS authentication but is not installed',
                    'msg'   => 'Use php5-curl on debian to install it');

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

                try {
                    if ($this->initDatabase())
                        $notifs [] = 'Database configured ';
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
     * Tells if filez table 'fz_file' (or 'Fichiers' if Fz1) exists on the
     * configured connection
     *
     * @return boolean
     */
    public function databaseExists () {
        $sql = 'SELECT table_name '
              .'FROM information_schema.tables '
              .'WHERE table_name=\'fz_file\''
              .'  or  table_name=\'fz_info\''
              .'  or  table_name=\'Fichiers\'';

        $res = Fz_Db::findAssocBySQL($sql);
        if (count ($res) == 0)
            return false;
        else {
            $version = false;
            foreach ($res as $table) {
                if ($table['table_name'] == 'Fichiers') {
                    return '1.2'; // TODO add more check
                } else if ($table['table_name'] == 'fz_file') {
                    $version = '2.0.0';
                } else if ($table['table_name'] == 'fz_info') {
                    $res = Fz_Db::findAssocBySQL(
                        'SELECT `value` FROM `fz_info` WHERE `key`=\'db_version\'');
                    if (! empty ($res))
                        return $res [0]['value'];
                }
            }
            return $version;
        }
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

    /**
     * Tells if APC is installed
     *
     * @return boolean
     */
    public function checkApcInstalled ()
    {
        return (bool) ini_get ('apc.enabled');
    }


    /**
     * Tells if APC is installed
     *
     * @return boolean
     */
    public function checkApcConfigured ()
    {
        return (bool) ini_get ('apc.rfc1867');
    }


    public function getDatabaseInitScript () {
        $sql = '';
        // Check if database exists
        if (($version = $this->databaseExists()) !== false) {
            $pattern = '/filez-(([0-9]*)\.([0-9]*)\.([0-9]*)-([0-9]*))\.sql$/';
            foreach (glob (option ('root_dir').'/config/db/migrations/filez-*.sql') as $file) {
                $matches = array ();
                if (preg_match ($pattern, $file, $matches) === 1) {
                    if (strcmp ($matches [1], $version) > 0)
                        $sql .= file_get_contents ($file);
                }
            }
        } else {
            $sql = file_get_contents (option ('root_dir').'/config/db/schema.sql');
        }
         return $sql;
    }

    public function initDatabase () {
        if (option ('db_conn') === null)
            throw new Exception ('Database connection not found.');

        $sql = $this->getDatabaseInitScript ();

        if (! empty ($sql)) {
            if ($sql === false)
                throw new Exception ('Database script not found "'.$initDbScript.'"');

            option ('db_conn')->exec ($sql);

            return true;
        }
        return false;
    }
}
