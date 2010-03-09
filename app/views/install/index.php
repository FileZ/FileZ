
<?php

function config_form_row ($section, $var, $label, $type, $default_values) {?>
  <p>
    <label for=""><?php echo $label ?></label>
    <?php if ($type == 'text'): ?>
      <input type="text" id="" name="<?php echo "config[$section][$var]" ?>" value="<?php echo $default_values[$section][$var] ?>"/>
    <?php endif ?>
  </p>
<?}

?>

<form action="" method="POST">

  <fieldset>
    <legend>General</legend>
    <?php echo config_form_row ('app', 'upload_dir'             , 'Upload directory'        , 'text', $config) ?>
    <?php echo config_form_row ('app', 'log_dir'                , 'Log directory'           , 'text', $config) ?>
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
    <legend>Localisation</legend>
    <?php echo config_form_row ('app', 'default_locale'         , 'Locale by default if we can\'t find it from the user agent' , 'text', $config) ?>
  </fieldset>

  <fieldset>
    <legend>Authentication</legend>
  </fieldset>

  <fieldset>
    <legend>Identification</legend>
  </fieldset>

  <input type="submit" value="Save" />

</form>
