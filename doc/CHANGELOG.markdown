
Filez-2.1.0

* Added the possibility to force filez fqdn fixes#24
* Improved image preview
* Added MIME support
* Fixed config 
* Fixed the max length of a hash to 10
* Added german translation
* Redesigned file list
* Added other share destination (FB, twitter) optional
* Added German translation (thanks to @spiorf)
* Added Italian translation (thanks to @tini)
* Added optional Antivirus check (thanks to @daserzw)
* Added admin backend (thanks to @almaghi)
* Added automatic database update

Filez-2.0.2

* better handling of non ascii char in filename when downloading (thanks to C. Durocher)
* security fix when filez is installed in a subdirectory (thanks to C. Durocher)

Filez-2.0.1

* Added spannish translation
* Documented configuration with an Onracle Ldap Serveur
* Fixed shortened file URL when sending emails (thanks to Cédric Corazza)
* Fixed missing smtp credentials on the install form

Filez-2.0.0

* Fixed some IE7 rendering bug
* Ldap user factory now allow to retrieve accounts from baseDn subtrees
* Fixed PHP-5.3 incompatibilities
* Added "show_credit", "help_url" and "bug_report_href" in the looknfeel section of filez.ini
* Email are now sent under the name of the connected user
* Removed debug output when application isn't in debug mode
* https support on login form
* Fixed Install bug when authenticating against a database
* Fixed install instructions
* Added french user documentation
* Fixed user quota computation
* Fixed multivalued profile attributes 
* Fixed strange open short tag sitting in the code, resulting in 'syntax error, unexpected $end' 
* Fixed issue#1 : Wrong file size conversion from shorthand to bytes
* Fixed issue#2 : Added php_admin_value in .htaccess
* Fixed issue#3 : Update disk usage value after upload 
* Fixed issue#9 : Upload progress monitor can now be configured 
* Updated limonade-php

Filez-2.0.0-BETA2

* Added 'password' field to fz_file table. You need to run 'db.migration-filez-2.0.0-1.sql'
* Added '[app]::debug' var in filez.ini (Shows errors & logged messages)
* Added '[app]::user_quota' var in filez.ini
* Upload form :
  * Added email notifications checkbox
  * Added password checkbox and input
* Added default configuration. User is still able to override it in filez.ini
  but all parameters are not required now.
* Added Auto Installer/Updater when filez.ini is not found
* Fixed email bug


