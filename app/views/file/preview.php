

<h2 class="filename">
  <?php echo h($file->file_name) ?> (<?php echo $file->getReadableFileSize () ?>)
</h2>
<section id="preview-file">
  <p>
    <?php echo __('Uploaded by') ?> :
    <?php if (array_key_exists('firstname', $uploader)): ?>
      <?php echo h($uploader['firstname']).' '.h($uploader['lastname']) ?>
    <?php else: ?>
      <?php echo h($uploader['email']) ?>
    <?php endif ?>
  </p>
  <?php if ($file->comment): ?>
    <p><?php echo __('Comments') ?> : <?php echo h($file->comment) ?></p>
  <?php endif ?>
  <?php if ($available): ?>
    <p>
      <?php echo __('Your download will start shortly...') ?>
      <a href="<?php echo $file->getDownloadUrl ()?>/download">
        <?php echo __('If not, click here') ?>
      </a>.
    </p>
    <script type="text/javascript">
      $(document).ready (function() {
        window.location= "<?php echo $file->getDownloadUrl ()?>/download";
      });
    </script>
  <?php endif ?>
</section>
  
