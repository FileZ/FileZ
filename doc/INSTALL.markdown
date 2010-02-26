

Requirement
===========

* Apache with mod_rewrite 
      a2enmod rewrite && apache2ctl restart

* APC is required if you want to enable the progress bar during file upload. 
  See 'Installing and configuring APC' section.


Installation
============

TODO ceci est une ébauche

* upload ...
* configure your virtual host. See 'Notes::Apache virtual host example'.
* point your browser on the server where you uploaded filez.


Notes
=====

Apache virtual host example
---------------------------


Security strategies
------------------

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
filez.ini. The only mandatory parameter is 'host'. But you may need some
additionnal configuration, a list of all possible options can be found here :
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

This attributes are required by filez :

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

