
<div id="install">


<?php

function config_form_row ($section, $var, $label, $type, $default_values, $choices = null) {?>
  <p>
    <?php if ($type != 'checkbox'): ?>
      <label for="field-<?php echo $section.'-'.$var ?>"><?php echo $label ?></label>
    <?php endif ?>
    <?php if ($type == 'text' || $type == 'password'): ?>
      <input type="<?php echo $type ?>" id="field-<?php echo $section.'-'.$var ?>" name="<?php echo "config[$section][$var]" ?>" value="<?php echo $default_values[$section][$var] ?>"/>
    <?php elseif ($type == 'select'): ?>
      <select id="field-<?php echo $section.'-'.$var ?>" name="<?php echo "config[$section][$var]" ?>">
        <?php foreach ((array) $choices as $value => $text): ?>
          <option value="<?php echo $value ?>" <?php echo ($value == $default_values[$section][$var] ? 'selected="selected"' : '') ?>" ><?php echo $text ?></option>
        <?php endforeach ?>
      </select>
    <?php elseif ($type == 'checkbox'): ?>
      <input type="checkbox" id="field-<?php echo $section.'-'.$var ?>"
             name="<?php echo "config[$section][$var]" ?>"
             value="1"
             <?php echo (((int) $default_values[$section][$var] == 1) ? 'checked="checked"' : '') ?> />
      <label for="field-<?php echo $section.'-'.$var ?>" style="display: inline;"><?php echo $label ?></label>
    <?php endif ?>
  </p>
<?php } ?>


<form action="" method="POST" class="install">


    <?php if (! empty ($errors)): ?>
      <div id="install-errors">
        <p>We found several errors while checking your configuration :</p>

        <ul>
        <?php foreach ($errors as $e): ?>
          <li><b><?php echo $e['title'] ?></b><?php echo (array_key_exists ('msg', $e) ? '<br />'.$e['msg'] : '') ?></li>
        <?php endforeach ?>
        </ul>

        <div class="help">
          <p>
              You can ignore these errors and save the file anyway. To correct them afterward, just edit the file 'config/filez.ini'.
              <input type="button" value="Yes, I want to ignore errors and configure filez.ini manually." name="ignore_errors" class="awesome ignore_errors" />
              <script type="text/javascript">
                $(document).ready (function () {
                  $('input.ignore_errors').click (function () {
                    $('form.install').append ('<input type="hidden" name="ignore_errors" value="true" />');
                    $('form.install').submit ();
                  });
                });
              </script>
          </p>
        </div>
      </div>
    <?php endif ?>


  <fieldset>
    <legend>General</legend>
    <?php echo config_form_row ('app', 'upload_dir' , 'Upload directory (absolute dir)' , 'text', $config) ?>
    <?php echo config_form_row ('app', 'log_dir'    , 'Log directory (absolute dir)'    , 'text', $config) ?>
    <?php echo config_form_row ('app', 'user_quota' , 'Default user quota'      , 'text', $config) ?>
    <?php echo config_form_row ('app', 'admin_email', 'Filez administor email (used to test the smtp server and in case of fatal errors)' , 'text', $config) ?>
    <table><tr><td>
    <?php echo config_form_row ('app', 'https', 'Use https' , 'select', $config, array (
        'off'        => 'Never',
        'login_only' => 'On login only',
        'always'     => 'Always (not fully implemented yet)',
    )) ?>
    </td><td>
    <?php echo config_form_row ('app', 'progress_monitor', 'Upload progress monitoring library <a href="http://github.com/UAPV/FileZ/blob/master/doc/INSTALL.markdown" target="_blank">[help]</a>' , 'select', $config, array (
        '' => 'None',
        'Fz_UploadMonitor_ProgressUpload' => 'PECL::ProgressUpload',
        'Fz_UploadMonitor_Apc'            => 'APC',
    )) ?>
    </td></tr></table>
  </fieldset>

  <fieldset>
    <legend>Uploaded files properties</legend>
    <table>
      <tr>
        <td><?php echo config_form_row ('app', 'default_file_lifetime'  , 'Default lifetime (days)', 'text', $config) ?></td>
        <td><?php echo config_form_row ('app', 'max_file_lifetime'      , 'Max lifetime (days)'  , 'text', $config) ?></td>
      </tr>
    </table>
    <table>
      <tr>
        <td><?php echo config_form_row ('app', 'max_extend_count'       , 'Number of times a user can extends its file lifetime', 'text', $config) ?></td>
        <td width="50%"></td>
      </tr>
    </table>
    <table>
      <tr>
        <td><?php echo config_form_row ('app', 'min_hash_size'          , 'Minimum size of the file download code', 'text', $config) ?></td>
        <td><?php echo config_form_row ('app', 'max_hash_size'          , 'maximum size of the file download code', 'text', $config) ?></td>
      </tr>
    </table>
  </fieldset>

  <fieldset>
    <legend>Email (SMTP)</legend>
    <table>
      <tr>
        <td><?php echo config_form_row ('email', 'host', 'SMTP Host' , 'text', $config) ?></td>
        <td width="20%"><?php echo config_form_row ('email', 'port', 'Port (optional)', 'text', $config) ?></td>
      </tr>
    </table>
    <table>
      <tr>
        <td><?php echo config_form_row ('email', 'from_email', 'Sender email' , 'text', $config) ?></td>
        <td><?php echo config_form_row ('email', 'from_name',  'Sender name'  , 'text', $config) ?></td>
      </tr>
    </table>
    <?php echo config_form_row ('email', 'auth', 'Authentication method (optional)', 'select', $config, array(
        '' => 'Anonymous',
        'login' => 'Login',
        'plain' => 'Plain',
        'crammd5' => 'CRAM-MD5',
    )) ?>

    <table id="smtp-auth-options" class="options">
      <tr>
        <td><?php echo config_form_row ('email', 'username', 'Username (if authentication)', 'text', $config) ?></td>
        <td><?php echo config_form_row ('email', 'password', 'Password (if authentication)', 'password', $config) ?></td>
      </tr>
    </table>
  </fieldset>

  <fieldset>
    <legend>Filez Database</legend>
    <?php echo config_form_row ('app', 'filez1_compat', 'Migrate Filez 1.x data. WARNING : Old website will not work anymore, you should backup your database before' , 'checkbox', $config) ?>
    <?php echo config_form_row ('db', 'dsn', '<a href="http://www.php.net/manual/en/pdo.drivers.php" target="_blank">DSN</a> to connect to your database' , 'text', $config) ?>
    <table>
      <tr>
        <td><?php echo config_form_row ('db', 'user', 'Database username' , 'text', $config) ?></td>
        <td><?php echo config_form_row ('db', 'password', 'Database password' , 'password', $config) ?></td>
      </tr>
    </table>
    <p class="help">
      You can find DSN syntax help here :
      <a href="http://www.php.net/manual/en/ref.pdo-mysql.connection.php">MySQL 3.x/4.x/5.x</a>,
      <a href="http://www.php.net/manual/en/ref.pdo-pgsql.connection.php">PostgreSQL</a>,
      <a href="http://www.php.net/manual/en/ref.pdo-sqlite.connection.php">SQLite 3 and SQLite 2</a>,
      <a href="http://www.php.net/manual/en/ref.pdo-oci.connection.php">Oracle</a>,
      <a href="http://www.php.net/manual/en/ref.pdo-odbc.connection.php">ODBC v3 (IBM DB2, unixODBC and win32 ODBC)</a>,
      <a href="http://www.php.net/manual/en/ref.pdo-informix.connection.php">IBM Informix Dynamic Server</a>,
      <a href="http://www.php.net/manual/en/ref.pdo-ibm.connection.php">IBM DB2</a>,
      <a href="http://www.php.net/manual/en/ref.pdo-firebird.connection.php">Firebird/Interbase 6</a>,
      <a href="http://www.php.net/manual/en/ref.pdo-4d.connection.php">4D</a>.
    </p>
  </fieldset>

  <fieldset>
    <legend>Authentication</legend>
    <?php echo config_form_row ('app', 'auth_handler_class', 'Authentication method' , 'select', $config, array (
        'Fz_Controller_Security_Internal' => 'Internal (Database or Ldap)',
        'Fz_Controller_Security_Cas' => 'CAS',
    )) ?>
    <div class="options" id="Fz_Controller_Security_Cas">
      <?php echo config_form_row ('auth_options', 'cas_server_host', 'CAS Server host' , 'text', $config) ?>
      <?php echo config_form_row ('auth_options', 'cas_server_path', 'Path where your CAS server will respond ("cas" for example)' , 'text', $config) ?>
      <?php echo config_form_row ('auth_options', 'cas_server_port', 'CAS Server port number' , 'text', $config) ?>
    </div>
  </fieldset>

  <fieldset>
    <legend>Identification</legend>
    <?php echo config_form_row ('app', 'user_factory_class', 'User profile source' , 'select', $config, array (
        'Fz_User_Factory_Ldap' => 'Ldap',
        'Fz_User_Factory_Database' => 'Database',
    )) ?>
    <div class="options" id="Fz_User_Factory_Ldap">
      <h2>Ldap options :</h2>
      <?php echo config_form_row ('user_factory_options', 'baseDn', 'Base DN' , 'text', $config) ?>
      <?php echo config_form_row ('user_factory_options', 'host',   'LDAP Server host' , 'text', $config) ?>
      <?php echo config_form_row ('user_factory_options', 'useSsl', 'Use ssl to bind to the Ldap host' , 'checkbox', $config) ?>
      <?php echo config_form_row ('user_factory_options', 'bindRequiresDn', 'Bind requires DN (check this if your are not using Active Directory)' , 'checkbox', $config) ?>
      <div class="help">
        <p>For other parameters, you will have to add them manually to filez.ini in the "[user_factory_options]" section once you have validated this form.</p>
        <p>Please refer to <a href="http://framework.zend.com/manual/en/zend.ldap.api.html" target="_blank">the Zend Framework documentation</a> for a list of possible options</p>
      </div>
    </div>
    <div class="options" id="Fz_User_Factory_Database">
      <h2>Database options :</h2>
      <h3>User database connection :</h3>
      <?php echo config_form_row ('user_factory_options', 'db_use_global_conf', 'Use the same configuration as Filez' , 'checkbox', $config) ?>
      <script type="text/javascript">
        $(document).ready (function () {
          $('#field-user_factory_options-db_use_global_conf').change (function () {
            if ($(this).attr('checked'))
              $('.options#db_use_global_conf_checked').hide();
            else
              $('.options#db_use_global_conf_checked').show();
          }).trigger ('change');
        });
      </script>
      <div class="options" id="db_use_global_conf_checked">
        <?php echo config_form_row ('user_factory_options', 'db_server_dsn', '<a href="http://www.php.net/manual/en/pdo.drivers.php">DSN</a> to connect to your database' , 'text', $config) ?>
        <?php echo config_form_row ('user_factory_options', 'db_server_user', 'Database username' , 'text', $config) ?>
        <?php echo config_form_row ('user_factory_options', 'db_server_password', 'Database password' , 'password', $config) ?>
      </div>
      <h3>User table schema :</h3>
      <?php echo config_form_row ('user_factory_options', 'db_table', 'Table where username are stored', 'text', $config) ?>
      <?php echo config_form_row ('user_factory_options', 'db_username_field', 'Database column name containing the username', 'text', $config) ?>
      <?php echo config_form_row ('user_factory_options', 'db_password_field', 'Database column name containing the user password', 'text', $config) ?>
      <?php echo config_form_row ('user_factory_options', 'db_password_algorithm', 'Algorithm used to hash the password', 'text', $config) ?>

      <div class="help">
        <p>Possible algorithms are</p>
        <ul>
          <li>"<b>MD5</b>" (unsecure)</li>
          <li>"<b>SHA1</b>" (unsecure)</li>
          <li>PHP Function name ex: "<b>methodName</b>"</li>
          <li>PHP Static method ex: "<b>ClassName::Method</b>"</li>
          <li>Plain SQL ex: "<b>SHA1(CONCAT(salt_column, :password))</b> (default)"</li>
        </ul>
      </div>
    </div>
    <h2>User attributes mapping :</h2>
    <p class="help">
      In order to make the application schema independant with differents user storage
      facilities, each user attributes is mapped from its original name to the
      application name.
    </p>
    <?php echo config_form_row ('user_attributes_translation', 'firstname', 'Firstname field name' , 'text', $config) ?>
    <?php echo config_form_row ('user_attributes_translation', 'lastname',  'Lastname field name' , 'text', $config) ?>
    <?php echo config_form_row ('user_attributes_translation', 'email',     'Email field name' , 'text', $config) ?>
    <?php echo config_form_row ('user_attributes_translation', 'id',        'ID field name' , 'text', $config) ?>
  </fieldset>

  <fieldset>
    <legend>UI Customisation</legend>
    <?php echo config_form_row ('looknfeel', 'your_logo', 'URI (relative to the filez web root) of your organisational logo image (optional)' , 'text', $config) ?>
    <?php echo config_form_row ('looknfeel', 'custom_css', 'URI (relative to the filez web root) of your custom CSS file (optional)' , 'text', $config) ?>
    <?php echo config_form_row ('looknfeel', 'bug_report_href', 'HREF (http:// or mailto:) where the user will be redirected if he want to report a bug' , 'text', $config) ?>
    <?php echo config_form_row ('looknfeel', 'help_url', 'URL containing filez documentation' , 'text', $config) ?>
    <?php echo config_form_row ('looknfeel', 'show_credit', 'Display a link to filez website in page footer' , 'checkbox', $config) ?>
  </fieldset>

  <p class="submit">
    <input type="submit" value="Check configuration and install !" class="awesome large blue"/>
  </p>

</form>

<script type="text/javascript" src="<?php echo public_url_for ('resources/jquery.validate/js/jquery.validate.pack.js') ?>"></script>
<script type="text/javascript" src="<?php echo public_url_for ('resources/jquery.validate/js/additional-methods.js') ?>"></script>
<script type="text/javascript" src="<?php echo public_url_for ('resources/jquery.validate/js/localisation/messages_'.option ('locale')->getLanguage ().'.js') ?>"></script>
<script type="text/javascript">

  $.fn.autoShowOptions = function () {
    $(this).each (function () {
      // Hide others conditional options
      $('.options', $(this).closest ('fieldset')).hide ();

      // Show conditional options for the selected item
      if (this.nodeName == 'SELECT')
        $('.options[id=\''+$(this).val()+'\']')
          .show ()
          .find ('input, select').trigger ('change'); // Reload sub options
      else if (this.nodeName == 'INPUT' && $(this).attr('checked')) // checkbox
        $('.options[id=\''+$(this).attr('name')+'_checked\']').show (); // FIXME TODO
    });
  };

  $(document).ready (function () {
      
    $("select, checkbox").not ('#field-email-auth').change (function () { $(this).autoShowOptions (); });

    // Specific treatment
    $('#field-email-auth').change (function (e) {
      if ($(this).val () == '')
        $('#smtp-auth-options').hide();
      else
        $('#smtp-auth-options').show();
    });
    
    $("select, checkbox").trigger ('change');

    jQuery.validator.addMethod("memsize", function(value, element) {
      return this.optional(element) || /^\d+[KMG]?$/.test(value);
    }, "A positive number followed by 'K', 'M', or 'G' please ");

    var rules = {
        //'config[app][use_url_rewriting]': {},
        'config[app][admin_email]': {
            email: true,
            required: true
        },
        'config[app][upload_dir]': {
            nowhitespace: true,
            required: true
        },
        'config[app][log_dir]': {
            nowhitespace: true,
            required: true
        },
        'config[app][filez1_compat]': {},
        'config[app][max_file_lifetime]': {
            number: true,
            min: 1
        },
        'config[app][default_file_lifetime]': {
            number: true,
            min: 1
        },
        'config[app][max_extend_count]': {
            number: true,
            min: 0
        },
        'config[app][min_hash_size]': {
            number: true,
            min: 0
        },
        'config[app][max_hash_size]': {
            number: true,
            min: 0
        },
        'config[app][default_locale]': 'nowhitespace',
        'config[app][auth_handler_class]': 'nowhitespace',
        'config[app][user_factory_class]': 'nowhitespace',
        'config[app][user_quota]': {
            required: true,
            memsize: true
        },
        'config[looknfeel][your_logo]': 'nowhitespace',
        'config[looknfeel][custom_css]': 'nowhitespace',

        'config[db][dsn]': {
            required: true,
            nowhitespace: true
        },
        'config[db][user]': 'required',
        'config[db][password]': 'required',

        'config[email][from_email]': {
            required: true,
            email: true
        },
        'config[email][from_name]': 'required',
        'config[email][host]': {
            required: true,
            nowhitespace: true
        },
        'config[email][port]': 'number',
        'config[email][username]': {},
        'config[email][password]': {},

        'config[auth_options][cas_server_host]': {
            required: 'option[value=\'Fz_Controller_Security_Cas\']:selected',
            nowhitespace: true
        },
        'config[auth_options][cas_server_port]': 'number',
        'config[auth_options][cas_server_path]': 'nowhitespace',

        'config[user_factory_options][host]': {
            required: 'option[value=\'Fz_User_Factory_Ldap\']:selected',
            nowhitespace: true
        },
        //'config[user_factory_options][useSsl]': {},
        'config[user_factory_options][baseDn]': {
            nowhitespace: true
        },
        //'config[user_factory_options][bindRequiresDn]': {},

        'config[user_factory_options][db_use_global_conf]': {},
        'config[user_factory_options][db_server_dsn]': {
            required: '#field-user_factory_options-db_use_global_conf:unchecked:visible',
            nowhitespace: true
        },
        'config[user_factory_options][db_server_user]': {
            required: '#field-user_factory_options-db_use_global_conf:unchecked:visible',
            nowhitespace: true
        },
        'config[user_factory_options][db_server_password]': {
            required: '#field-user_factory_options-db_use_global_conf:unchecked:visible',
            nowhitespace: true
        },
        'config[user_factory_options][db_table]': {
            required: 'option[value=\'Fz_User_Factory_Database\']:selected',
            nowhitespace: true
        },
        'config[user_factory_options][db_password_field]': {
            required: 'option[value=\'Fz_User_Factory_Database\']:selected',
            nowhitespace: true
        },
        'config[user_factory_options][db_username_field]': {
            required: 'option[value=\'Fz_User_Factory_Database\']:selected',
            nowhitespace: true
        },

        'config[user_attributes_translation][firstname]': {
            required: true,
            nowhitespace: true
        },
        'config[user_attributes_translation][lastname]': {
            required: true,
            nowhitespace: true
        },
        'config[user_attributes_translation][email]': {
            required: true,
            nowhitespace: true
        },
        'config[user_attributes_translation][id]': {
            required: true,
            nowhitespace: true
        }
    };
    $.each (rules, function (key, value) {
        if (value == 'required' || value.required)
            $('label[for=\''+$('[name=\''+key+'\']').attr('id')+'\']').append (' <span class="required">(required)</span>');
    });
    $('form.install').validate ({
        'rules': rules,
        // on submit delete hidden box to remove non required fields from the form data
        submitHandler: function(form) {
            $('form.install .options:hidden').remove();
            form.submit();
        },
        debug: true
    });

   
  });

</script>

</div>
