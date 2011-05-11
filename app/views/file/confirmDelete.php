
<h2><?php echo __r('Do you really want to delete: %filename%?', array('filename'=>'<span class="filename">'.h($file->file_name))) ?></span></h2>

<form method="post">
  <p style="padding: 2em 0;">
    <input type="submit" value="<?php echo __('Yes, delete that file') ?>" class="delete"/> |
    <a href="#" onclick="javascript:history.go(-1); return false;"><?php echo __('No, go back to previous page') ?></a>
  </p>
</form>
