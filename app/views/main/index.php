
<h2 class="new-file"><?php echo __('Upload a new file') ?></h2>
<section class="new-file fz-modal">
  <form method="POST" enctype="multipart/form-data" action="<?php echo url_for ('upload') ?>" id="upload-form">
  <div id="file">
    <label for="file"><?php echo __('File') ?> :</label>
    <div id="input-file">
      <input type="file" id="file-input" name="file" value="" alt="<?php echo __('File') ?>" />
    </div>
  </div>
  <div id="lifetime">
    <label for="lifetime"><?php echo __('Lifetime') ?> :</label>
    <select id="select-lifetime" name="lifetime" alt="<?php echo __('Select a lifetime') ?>">
      <?php $default = fz_config_get ('app', 'default_file_lifetime', 10);
            $max     = fz_config_get ('app', 'max_file_lifetime',     20);
            for ($i = 1; $i <= $max; ++$i  ): ?>
        <option value=<?php echo "\"$i\"".($i == $default ? ' selected="selected" ' : '') ?>>
          <?php echo str_replace ('%n%', $i, __p('%n% day', '%n% days', $i)) ?>
        </option>
      <?php endfor ?>
    </select>
  </div>
  <div id="start-from">
    <label for="start-from"><?php echo __('Starts from') ?> :</label>
    <input type="text" id="input-start-from" name="start-from" value="<?php echo $start_from ?>" alt="<?php echo __('Select a starting date') ?>" />
  </div>
  <div id="comment">
    <label for="comment"><?php echo __('Comments') ?> :</label>
    <input type="text" id="input-comment" name="comment" value="" alt="<?php echo __('Add a comment (optional)') ?>" maxlength="200" />
  </div>
  <div id="upload">
    <input type="submit" id="start-upload" name="upload" class="awesome blue large" value="&raquo; <?php echo __('Upload') ?>" />
    <div id="upload-loading"  style="display: none;"></div>
    <div id="upload-progress" style="display: none;"></div>
  </div>
  </form>
</section>

<h2 id="uploaded-files-title"><?php echo __('Uploaded files') ?></h2>
<section id="uploaded-files">
  <ul id="files">
    <?php $odd = true; foreach ($files as $file): ?>
      <li class="file <?php echo $odd ? 'odd' : 'even'; $odd = ! $odd ?>" id="<?php echo 'file-'.$file->getHash() ?>">
        <?php echo partial ('main/_file_row.php', array ('file' => $file)) ?> 
      </li>
    <?php endforeach ?>
  </ul>
</section>

<div id="email-modal" class="fz-modal" style="display: none;">
  <?php echo partial ('file/_mailForm.php') ?>
</div>


<script type="text/javascript">
    $(document).ready (function () {
      $('#input-start-from').datepicker ({minDate: new Date()});
      $('#upload-form').initFilez ({
        fileList:         'ul#files',
        progressBox:      '#upload-progress',
        loadingBox:       '#upload-loading',
        progressBar: {
          enable:        <?php echo ($use_progress_bar ? 'true':'false') ?>,
          barImage:     '<?php echo public_url_for ('resources/images/progressbg_green.gif') ?>',
          boxImage:     '<?php echo public_url_for ('resources/images/progressbar.gif') ?>',
          refreshRate:   <?php echo $refresh_rate ?>,
          progressUrl:  '<?php echo url_for ('upload/progress/') ?>'
        },
        messages: {
          confirmDelete: <?php echo  json_encode (__('Are you sure to delete this file ?')) ?>,
          unknownError: <?php echo  json_encode (__('Unknown error')) ?>,
          unknownErrorHappened: <?php echo  json_encode (__('An unknown error hapenned while uploading the file')) ?>,
          cancel: <?php echo  json_encode (__('Cancel')) ?>
        }

      });

      // Modal box generic configuration
      $(".fz-modal").dialog({
        bgiframe: true,
        autoOpen: false,
        resizable: false,
        width: '560px',
        modal: true
      });

      // Set title for each modal
      $('section.new-file').dialog ('option', 'title', <?php echo json_encode(__('Upload a new file')) ?>);
      $('#email-modal').dialog ('option', 'title', <?php echo json_encode(__('Send by email')) ?>);

      // Replace upload form with on big button, and open a modal box on click
      $('h2.new-file').wrapInner ($('<a href="#" class="awesome large"></a>'));
      $('h2.new-file a').click (function (e) {
        $('section.new-file').dialog ('open');
        e.preventDefault();
      });
    });
</script>

