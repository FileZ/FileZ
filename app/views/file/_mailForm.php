<form method="POST" class="send-email-form">

  <p>
    <label for="to"><?php echo __('Email addresses (space separated)') ?> :</label>
    <input type="text" class="to" name="to" value="<?php echo params ('to') ?>"/>
  </p>
  <p>
    <label for="msg"><?php echo __('Message (file url will automatically be added)')?>:</label>
    <textarea cols="80" rows="10" name="msg" value="<?php echo params ('msg') ?>"></textarea>
  </p>
  <p class="submit">
    <input type="submit" class="awesome blue large" value="<?php echo __('Send') ?>" />
  </p>

</form>
