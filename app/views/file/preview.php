

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
  <p>
      <?php echo __('Availability') ?> :
      <?php echo __r('between %available_from% and %available_until%', array (
          'available_from'  => $file->getAvailableFrom()->toString  (Zend_Date::DATE_LONG),
          'available_until' => $file->getAvailableUntil()->toString (Zend_Date::DATE_LONG),
      )) ?>
  </p>
  <?php if ($file->comment): ?>
    <p><?php echo __('Comments') ?> : <?php echo h($file->comment) ?></p>
  <?php endif ?>
  <p>
  <?php if ($available): ?>
      <?php if (! $checkPassword): ?>
        <?php echo __('Your download will start shortly...') ?>
        <a href="<?php echo $file->getDownloadUrl ()?>/download">
          <?php echo __('If not, click here') ?>
        </a>.
      <script type="text/javascript">
        function startDownload () {window.location= "<?php echo $file->getDownloadUrl ()?>/download";}
        $(document).ready (function() {
          setTimeout ('startDownload()', 1000); // Give chrome some time to finish downloading images on the page
        });
      </script>
    <?php else: ?>
      <form action="<?php echo $file->getDownloadUrl ()?>/download" method="POST">
        <label for="password">
          <?php echo __('You need a password to download this file') ?>
        </label>
        <input type="password" name="password" id="password" class="password"/>
        <input type="submit" value="<?php echo __('Download') ?>" />
      </form>
    <?php endif ?>
  <?php else: ?>
    <?php echo __('The file is not available yet.') ?>
  <?php endif ?>
  </p>
</section>
  
