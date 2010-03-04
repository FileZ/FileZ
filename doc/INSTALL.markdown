

Requirement
===========

* Apache with mod_rewrite 
      a2enmod rewrite && apache2ctl restart

* APC is required if you want to enable the progress bar during file upload. 
  See 'Installing and configuring APC' section.


Installation
============

* Retrieve Filez sources from the svn or an archive and place them in your
  web root dir.

  * from SVN :
        svn export http://subversion.cru.fr/filez/trunk filez_web_root
  * from package
        tar -xvvf filez-2.0.tar.gz;
        cp filez-2.0/* filez_web_root

* Create a new database or reuse a previous one :

  * if you want de migrate the database from a previous installation of filez-1.x :
        mysql -h sql_host -u filez_user -p filez_db_name < config/db.migration-01.sql

  * otherwise create a new one :
        mysql -h sql_host -u filez_user -p filez_db_name < config/db.schema.sql

* Edit Filez config (See next parapgraph)

* Make sure the upload dir & log dir are writeable by the web server

      sudo chown www-data:www-data [upload_dir] [log_dir]

* Edit your vhost. Example '/etc/apache2/sites-available/filez' :

        <VirtualHost *:80>
            ServerAdmin webmaster@localhost
            ServerName  filez-test.univ-avignon.fr
            php_admin_value post_max_size       750M
            php_admin_value upload_max_filesize 750M
            php_admin_value max_execution_time  1200
            php_admin_value upload_tmp_dir "/media/data/tmp"

                DocumentRoot /var/www/filez-test.univ-avignon.fr
                <Directory />
                        Options FollowSymLinks
                        AllowOverride All
                </Directory>
                <Directory /var/www/filez-test.univ-avignon.fr>
                        Options Indexes FollowSymLinks MultiViews
                        AllowOverride All
                        Order allow,deny
                        allow from all
                </Directory>

                ErrorLog  /var/log/apache2/filez-error.log
                CustomLog /var/log/apache2/filez-access.log combined
                LogLevel warn
        </VirtualHost>

  Then activate it :
      a2ensite filez && /etc/init.d/apache2 reload


Configuration details
=====================

The easiest method is to start from a copy of the configuration example :

    cp config/filez.ini.example config/filez.ini

filez.ini.example is documented but you may find additional information in the
next sections, like installing dependencies, etc.


General Configuration
---------------------

The "[app]" section contains common options :

- "use_url_rewriting" (boolean) : Not tested with "false" yet
- "upload_dir" (Absolute path)  : Upload directory (writtable by the web server)
- "log_dir"    (Absolute path)  : Log directory (writtable by the web server)
- "filez1_compat" (boolean)     : Enable the filez-1.x support for downloading
                                  previously uploaded files
- "max_file_lifetime" (integer) : Maximum lifetime of the file on the server
                                  before being delete
- "default_file_lifetime (int)  : Default lifetime
- "min_hash_size" (integer)     : Minimum number of characters in the hash
- "max_hash_size" (integer)     : Maximum number of characters in the hash
- "default_locale" (string)     : Default locale used when Filez can find the
                                  prefered user locale or when there is no
                                  corresponding i18n for the user locale.


Database
--------

    [db]
    dsn      = "mysql:host=your_sql_host;dbname=your_filez_database"
    user     = filez
    password = password

It hasn't been tested yet but you should be able to connect with another
database driver than mysql. Check php doc for the dsn syntax corresponding to
your driver : <http://www.php.net/manual/en/pdo.drivers.php>.


Email
-----

Filez use an smtp server to send notification mails. You have configure it in
the "[email]" section :

    [email]
    from_email=filez@univ-avignon.fr
    from_name=Filez
    host=smtp.univ-avignon.fr
    ; auth=login ; possible values = crammd5, login, plain
    ; port=25
    ; username=user
    ; password=pwd


Configure Security strategy
---------------------------

Filez let you choose between 3 authentication strategies :

- CAS + LDAP
- LDAP only
- Database

In any case, don't forget to describe the user repository schema by following
the paragraph called "User attribute translation".

### CAS Server

Install dependencies :

    apt-get install curl php5-curl

Edit config/filez.ini with the following settings :

    [app]
    auth_handler_class = Fz_Controller_Security_Cas
    
    [auth_options]
    cas_server_host = url.of-your-cas-server.com

You can also add these options : 'cas_server_port' and 'cas_server_path'

Follow the section named "Ldap Identification" to configure the Ldap server.

### Database

You can use an existing database with Filez. You just need to configure database
connection and describe the table containing your users.

Edit config/filez.ini with the following line in the '[app]' section :

    auth_handler_class = Fz_Controller_Security_Internal

If your user table is located on the same database as the filez table, use the
following setting :

    [user_factory_options]
    db_use_global_conf = true

Otherwise you will need to configure another connection :

    [user_factory_options]
    db_server_dsn         = "mysql:host=localhost;dbname=filez"
    db_server_user        = filez
    db_server_password    = filezpwd

Finally describe where and how the username/password are stored :

    [user_factory_options]
    db_table              = user_tabme
    db_password_field     = password_field
    db_username_field     = username_field
    db_password_algorithm = SHA1

"db_password_algorithm" describe the method used to encrypt the password. There
is several possible values that should suit your needs :

- "MD5"
- "SHA1"
- PHP Function name ex: "methodName"
- PHP Static method ex: "ClassName::Method"
- Plain SQL ex: "SHA1(CONCAT(salt, :password))"

If you use a PHP callback, just put the file containing your function under the
'lib/' directory.


### LDAP server

#### Ldap Authentication

Edit config/filez.ini with the following line in the '[app]' section :

    auth_handler_class = Fz_Controller_Security_Internal

#### Ldap Identification

Ldap connection is defined under the '[user_factory_options]' section of
filez.ini. By default, Filez use an anonymous connection, the only mandatory
parameter is 'host'. But you may need some additionnal configuration, a list of
all possible options can be found here :
<http://framework.zend.com/manual/en/zend.ldap.api.html>

Example configuration :

    [user_factory_options]
    host = ldap.univ-avignon.fr
    useSsl = false
    baseDn = "ou=people,dc=univ-avignon,dc=fr"
    bindRequiresDn = true

### User attribute translation

In order to make the application schema agnostic with differents user storage
facilities, each user attributes is translated from its original name to the
application name. The syntax is as follow : application_name = original_name
and must be placed under the "[user_attributes_translation]" section.

These attributes are required by filez :

- firstname
- lastname
- email
- id

For our Ldap repository, the configuration looks like this :

    [user_attributes_translation]
    firstname = givenname
    lastname  = sn
    email     = mail
    id        = uid


Notes
=====

Installing and configuring APC
------------------------------

Install required tools for building APC. On debian :

    apt-get install build-essential php5-dev php-pear apache2-prefork-dev
  
Build and install APC extension :

    pecl install apc

Enable extension and specific option :

    echo "extension = apc.so" >> /etc/php5/apache2/conf.d/apc.ini
    echo "apc.rfc1867 = On"   >> /etc/php5/apache2/conf.d/apc.ini
    apache2ctl restart

RHEL / CentOS with PHP version 5.1
----------------------------------

Follow this howto to enable the "filter_var()" function of PHP :

http://www.cyberciti.biz/faq/rhel-cento-linux-install-php-pecl-filter/
