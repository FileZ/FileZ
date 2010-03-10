
<?php

function config_form_row ($section, $var, $label, $type, $default_values, $choices = null) {?>
  <p>
    <?php if ($type != 'checkbox'): ?>
      <label for="field-<?php echo $section.'_'.$var ?>"><?php echo $label ?></label>
    <?php endif ?>
    <?php if ($type == 'text'): ?>
      <input type="text" id="field-<?php echo $section.'_'.$var ?>" name="<?php echo "config[$section][$var]" ?>" value="<?php echo $default_values[$section][$var] ?>"/>
    <?php elseif ($type == 'select'): ?>
      <select id="field-<?php echo $section.'_'.$var ?>" name="<?php echo "config[$section][$var]" ?>">
        <?php foreach ((array) $choices as $value => $text): ?>
          <option value="<?php echo $value ?>" <?php echo ($value == $default_values[$section][$var] ? 'selected="selected"' : '') ?>" ><?php echo $text ?></option>
        <?php endforeach ?>
      </select>
    <?php elseif ($type == 'checkbox'): ?>
      <input type="checkbox" id="field-<?php echo $section.'_'.$var ?>"
             name="<?php echo "config[$section][$var]" ?>"
             <?php echo ($default_values[$section][$var] ? 'checked="checked"' : '') ?> />
      <label for="field-<?php echo $section.'_'.$var ?>" style="display: inline;"><?php echo $label ?></label>
    <?php endif ?>
  </p>
<?}

?>

<form action="" method="POST" class="install">

  <div class="help">
    <p>This form will help you to configure filez by generating the file "<i>config/filez.ini</i>".</p>

    <p>This form has not been tested in a potential phase of the moon bug trigger browser (IE for example). <b>PLEASE USE A DECENT BROWSER TO AVOID BUGs</b>.</p>
  </div>

  <fieldset>
    <legend>General</legend>
    <?php echo config_form_row ('app', 'upload_dir'             , 'Upload directory (absolute dir)' , 'text', $config) ?>
    <?php echo config_form_row ('app', 'log_dir'                , 'Log directory (absolute dir)'    , 'text', $config) ?>
    <?php echo config_form_row ('app', 'user_quota'             , 'Default user quota'      , 'text', $config) ?>
  </fieldset>

  <fieldset>
    <legend>Uploaded files properties</legend>
    <?php echo config_form_row ('app', 'max_file_lifetime'      , 'Max lifetime'  , 'text', $config) ?>
    <?php echo config_form_row ('app', 'default_file_lifetime'  , 'Default lifetime', 'text', $config) ?>
    <?php echo config_form_row ('app', 'max_extend_count'       , 'Number of times a user can extends its file lifetime', 'text', $config) ?>
    <?php echo config_form_row ('app', 'min_hash_size'          , 'Minimum size of the file download code', 'text', $config) ?>
    <?php echo config_form_row ('app', 'max_hash_size'          , 'maximum size of the file download code', 'text', $config) ?>
  </fieldset>
    
  <fieldset>
    <legend>Filez Database</legend>
    <?php echo config_form_row ('app', 'dsn', '<a href="http://www.php.net/manual/en/pdo.drivers.php">DSN</a> to connect to your database' , 'text', $config) ?>
    <?php echo config_form_row ('app', 'user', 'Database username' , 'text', $config) ?>
    <?php echo config_form_row ('app', 'password', 'Database password' , 'text', $config) ?>
  </fieldset>

  <fieldset>
    <legend>Authentication</legend>
    <?php echo config_form_row ('app', 'auth_handler_class', 'Authentication method' , 'select', $config, array (
        'Fz_Controller_Security_Internal' => 'Internal (Database or Ldap)',
        'Fz_Controller_Security_Cas' => 'CAS',
    )) ?>
    <div class="options" id="Fz_Controller_Security_Cas">
      <?php echo config_form_row ('auth_options', 'cas_server_host', 'CAS Server host' , 'text', $config) ?>
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
      <?php echo config_form_row ('user_factory_options', 'bindRequiresDn', 'Bind requires DN' , 'checkbox', $config) ?>
      <p class="help">
        For other parameters, you will have to add them manually to filez.ini once you have validated this form.
        Please refer to <a href="http://framework.zend.com/manual/en/zend.ldap.api.html" target="_blank">the Zend Framework documentation</a> for a list of possible options
      </p>
    </div>
    <div class="options" id="Fz_User_Factory_Database">
      <h2>Database options :</h2>
      <?php echo config_form_row ('user_factory_options', 'db_use_global_conf', 'Use the same configuration as Filez' , 'checkbox', $config) ?>
      <div class="options" id="db_use_global_conf_checked">
        <?php echo config_form_row ('user_factory_options', 'db_server_dsn', '<a href="http://www.php.net/manual/en/pdo.drivers.php">DSN</a> to connect to your database' , 'text', $config) ?>
        <?php echo config_form_row ('user_factory_options', 'db_server_user', 'Database username' , 'text', $config) ?>
        <?php echo config_form_row ('user_factory_options', 'db_server_password', 'Database password' , 'text', $config) ?>
      </div>
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
          <li>Plain SQL ex: "<b>password=SHA1(CONCAT(salt_column, :password))</b>"</li>
        </ul>
      </div>
    </div>
    <h2>User attributes :</h2>
    <p class="help">
      In order to make the application schema agnostic with differents user storage
      facilities, each user attributes is translated from its original name to the
      application name.
    </p>
    <?php echo config_form_row ('user_attributes_translation', 'firstname', 'firstname' , 'text', $config) ?>
    <?php echo config_form_row ('user_attributes_translation', 'lastname',  'lastname' , 'text', $config) ?>
    <?php echo config_form_row ('user_attributes_translation', 'email',     'email' , 'text', $config) ?>
    <?php echo config_form_row ('user_attributes_translation', 'id',        'id' , 'text', $config) ?>
  </fieldset>

  <fieldset>
    <legend>Email (SMTP)</legend>
    <?php echo config_form_row ('email', 'host', 'SMTP Host' , 'text', $config) ?>
    <?php echo config_form_row ('email', 'from_email', 'Sender email' , 'text', $config) ?>
    <?php echo config_form_row ('email', 'from_name',  'Sender name'  , 'text', $config) ?>
    <?php echo config_form_row ('email', 'port',       'Port (optional)', 'text', $config) ?>
    <?php echo config_form_row ('email', 'auth',       'Authentication method (optional)', 'select', $config, array(
        '' => 'Anonymous',
        'login' => 'Login',
        'plain' => 'Plain',
        'crammd5' => 'CRAM-MD5',
    )) ?>
    <?php echo config_form_row ('email', 'username', 'Username (if authentication)', 'text', $config) ?>
    <?php echo config_form_row ('email', 'password', 'Port (if authentication)', 'text', $config) ?>
  </fieldset>

  <fieldset>
    <legend>UI Customisation</legend>
    <?php echo config_form_row ('looknfeel', 'your_logo', 'URI (relative to the filez web root) of your organisational logo image (optional)' , 'text', $config) ?>
    <?php echo config_form_row ('looknfeel', 'custom_css', 'URI (relative to the filez web root) of your custom CSS file (optional)' , 'text', $config) ?>
  </fieldset>

  <p class="submit">
    <input type="submit" value="Install !" class="awesome large"/>
  </p>

</form>

<script type="text/javascript">

  $.fn.autoShowOptions = function () {
    $(this).each (function () {
      // Hide others conditional options
      $('.options', $(this).closest ('fieldset')).hide ();

      // Show conditional options for the selected item
      if (this.nodeName == 'SELECT')
        $('.options#'+$(this).val()).show ();
      else if (this.nodeName == 'INPUT' && $(this).attr('checked')) // checkbox
        $('.options[id=\''+$(this).attr('name')+'_checked\']').show (); // FIXME TODO
    });
  };

  $('select, checkbox').autoShowOptions ();

  $(document).ready (function () {
    $('select, checkbox').change (function () { $(this).autoShowOptions (); })

    // TODO on submit delete hidden box to remove non required fields from the form data
  });

</script>