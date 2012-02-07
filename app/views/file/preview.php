

<h2 class="filename preview">
  <img src="<?php echo get_mimetype_icon_url ($file->getMimetype (), 48) ?>" class="mimetype" />
  <?php echo h($file->file_name) ?> (<?php echo $file->getReadableFileSize () ?>)
</h2>
<section id="preview-file">

  <?php if ($available && ! $checkPassword && $file->isImage ()): ?>
    <p id="preview-image">
      <a href="<?php echo $file->getDownloadUrl ()?>/view">
        <img src="<?php echo $file->getDownloadUrl ()?>/view" class="preview-image" width="617px"/>
      </a>
    </p>
  <?php endif ?>

  <p id="availability">
    <?php echo __r('Available from %available_from% to %available_until%', array (
        'available_from'  => $file->getAvailableFrom()->toString  (Zend_Date::DATE_LONG),
        'available_until' => '<b>'.$file->getAvailableUntil()->toString (Zend_Date::DATE_LONG).'</b>',
    )) ?>
  </p>

  <p id="owner">
    <?php echo __('Uploaded by:') ?> <b><?php echo h($uploader) ?></b>
  </p>

  <?php if ($file->comment): ?>
    <p id="comment"><b><?php echo __('Comments:') ?></b> <?php echo h($file->comment) ?></p>
  <?php endif ?>

  <?php if ($available): ?>
    <?php if (! $checkPassword): ?>

      <?php if ($file->isImage ()): ?>
        <p id="download" class="image">
          <a href="<?php echo $file->getDownloadUrl ()?>/download" class="awesome blue">
            <?php echo __('Download') ?>
          </a>
        </p>
      <?php else: ?>
        <p id="download">
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
        </p>
      <?php endif ?>

    <?php else: // this file need a password ?>

      <form action="<?php echo $file->getDownloadUrl ()?>/download" method="POST" id="download">
        <label for="password">
          <?php echo __('You need a password to download this file') ?>
        </label>
        <input type="password" name="password" id="password" class="password" size="4"/>
        <input type="submit" value="<?php echo __('Download') ?>" class="awesome blue" />
      </form>
    <?php endif ?>
  <?php else: ?>
    <?php echo __('The file is not available yet') ?>
  <?php endif ?>
</section>
  
