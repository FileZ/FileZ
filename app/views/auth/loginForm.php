

<form action="" method="post" id="login-form">
  <input type="hidden" name="token" value="<?php echo $token; ?>" />
  <p id="username">
    <label><?php echo __('Username') ?></label>
    <input type="text" name="username" class="username" value="<?php echo $username ?>"/>
  </p>
  <p id="password">
    <label><?php echo __('Password') ?></label>
    <input type="password" name="password" class="password" />
  </p>
  <p id="submit-login">
    <input type="submit" class="awesome large blue" value="<?php echo __('Log me in') ?>" />
  </p>
</form>
<script type="text/javascript">
$(document).ready (function () {
  $("input[name='username']").get(0).focus ();
});
</script>
