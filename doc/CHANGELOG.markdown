

Filez-2.0.0-BETA2 (WIP)
* Added 'password' field to fz_file table. You need to run 'db.migration-filez-2.0.0-1.sql'
* Added '[app]::debug' var in filez.ini (Shows errors & logged messages)
* Added '[app]::user_quota' var in filez.ini
* Upload form :
  * Added email notifications checkbox
  * Added password checkbox and input
* Added default configuration. User is still able to override it in filez.ini
  but all parameters are not required now.
* Added Auto Installer when filez.ini is not found

