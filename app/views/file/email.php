
<h2><?php echo __("Send via email:") ?> <span class="filename">(<?php echo h($file->file_name) ?>)</span></h2>

<?php echo partial ('file/_mailForm.php', array ('file' => $file)) ?>
