
General architecture
===============================================================================

Filez has been developed around the MVC pattern thanks to the Limonade
micro framework.

Limonade micro framework provide the glue between the controllers and views :
 * Routes declarations
 * Request handler/dispatcher (index.php) 
 * and many action helpers

Domain Logic
------------

Domain logic is implemented in 'app/model/DOMAIN_OBJECT.php' files.

Controllers & actions
---------------------

Controllers reside in 'app/controller/CONTROLLER_NAME.php' files and contain
a set of functions (actions).

Views
-----

Views are raw php files stored in 'app/view/CONTROLLER_NAME/ACTION_NAME.php'
Static files are stored in the 'resource' directory.

Other
-----

Tools and libraries reside in 'lib/'.

All of the Limonade Framework reside in the 'lib/Limonade.php' file.

Filez use a stripped down version of the Zend Framework containing only
i18n, date, mail, class loader, validation and ldap classes ('lib/Zend').




Anatomy of a file upload
===============================================================================




Security Management
===============================================================================





Internationalisation
===============================================================================





Coding standards
===============================================================================





Versionning
===============================================================================





