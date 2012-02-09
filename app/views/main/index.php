
<h2 class="new-file"><?php echo __('Upload a new file') ?></h2>
<section class="new-file fz-modal">
  <form method="POST" enctype="multipart/form-data" action="<?php echo url_for ('upload') ?>" id="upload-form">
  <input type="hidden" name="MAX_FILE_SIZE" value="<?php echo $max_upload_size ?>" />
  <div id="file">
    <label for="file-input"><?php echo __r('File (Max size: %size%)', array ('size' => bytesToShorthand ($max_upload_size))) ?> :</label>
    <div id="input-file">
      <input type="file" id="file-input" name="file" value="" alt="<?php echo __('File') ?>" />
    </div>
  </div>
  <div id="lifetime">
    <label for="select-lifetime"><?php echo __('Lifetime') ?> :</label>
    <select id="select-lifetime" name="lifetime" alt="<?php echo __('Select a lifetime') ?>">
      <?php $default = fz_config_get ('app', 'default_file_lifetime', 10);
            $max     = fz_config_get ('app', 'max_file_lifetime',     20);
            for ($i = 1; $i <= $max; ++$i  ): ?>
        <option value=<?php echo "\"$i\"".($i == $default ? ' selected="selected" ' : '') ?>>
          <?php echo str_replace ('%n%', $i, ($i > 1 ? __('%n% days') : __('%n% day'))) // FIXME ugly fix for handling plural ?>
        </option>
      <?php endfor ?>
    </select>
  </div>
  <div id="start-from">
    <label for="input-start-from"><?php echo __('Starts from') ?> :</label>
    <input type="text" id="input-start-from" name="start-from" value="<?php echo $start_from ?>" alt="<?php echo __('Select a starting date') ?>" />
  </div>
  <div id="comment">
    <label for="input-comment"><?php echo __('Comments') ?> :</label>
    <input type="text" id="input-comment" name="comment" value="" alt="<?php echo __('Add a comment (optional)') ?>" maxlength="200" />
  </div>
  <ul id="options">
    <li id="option-email-notifications">
      <input type="checkbox" name="email-notifications" id="email-notifications" checked="checked"/>
      <label for="email-notifications" title="<?php echo __('Send me email notifications when the file is uploaded and before it will be deleted') ?>">
        <?php echo __('Send me email notifications') ?>
      </label>
    </li>
    <li id="option-use-password">
      <input type="checkbox" name="use-password" id="use-password"/>
      <label for="use-password" title="<?php echo __('Ask a password to people who will download your file') ?>">
        <?php echo __('Use a password to download') ?>
      </label>
      <input type="password" id="input-password" name="password" class="password" autocomplete="off" size="5"/>
    </li>
  </ul>
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

<div id="share-modal" class="fz-modal" style="display: none;">
    <p class="instruction"><?php echo __('Give this link to the person you want to share this file with') ?></p>
    <p id="share-link"><a href=""></a></p>
    <p class="instruction"><?php echo __('or share using') ?> :</p>
    <ul id="share-destinations">
        <?php if (in_array ('email', $sharing_destinations)): ?>
          <li class="email"   ><a href="" data-url="%url%/email"><?php echo __('your email') ?></a></li>
        <?php endif; ?>
        <?php if (in_array ('facebook', $sharing_destinations)): ?>
        <li class="facebook"><a href="" target="_blank" data-url="http://www.facebook.com/sharer.php?u=%url%&t=%filename%"><?php echo __('Facebook') ?></a></li>
        <?php endif; ?>
        <?php if (in_array ('twitter', $sharing_destinations)): ?>
        <li class="twitter" ><a href="" target="_blank" data-url="http://twitter.com/home?status=%filename% %url%"><?php echo __('Twitter') ?></a></li>
        <?php endif; ?>
    </ul>
    <div class="cleartboth"></div>
</div>

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
        maxFileSize:      <?php echo $max_upload_size ?>,
        progressBar: {
          enable:        <?php echo ($use_progress_bar ? 'true':'false') ?>,
          upload_id_name: '<?php echo $upload_id_name ?>',
          barImage:     '<?php echo public_url_for ('resources/images/progressbg_green.gif') ?>',
          boxImage:     '<?php echo public_url_for ('resources/images/progressbar.gif') ?>',
          refreshRate:   <?php echo $refresh_rate ?>,
          progressUrl:  '<?php echo url_for ('upload/progress/') ?>'
        },
        messages: {
          confirmDelete: <?php echo  json_encode (__('Are you sure to delete this file ?')) ?>,
          unknownError: <?php echo  json_encode (__('Unknown error')) ?>,
          unknownErrorHappened: <?php echo  json_encode (__('An unknown error hapenned while uploading the file')) ?>,
          cancel: <?php echo  json_encode (__('Cancel')) ?>,
          emailMessage: <?php echo  json_encode (__('You can download the file I uploaded here')) ?>
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

      // Replace upload form with one big button, and open a modal box on click
      $('h2.new-file').wrapInner ($('<a href="#" class="awesome large"></a>'));
      $('h2.new-file a').click (function (e) {
        $('section.new-file').dialog ('open');
        e.preventDefault();
      });
      
      // Show password box on checkbox click
      $('input.password').hide();
      $('#use-password, #option-use-password label').click (function () { // IE quirk fix
        if ($('#use-password').attr ('checked')) {
            $('input.password').show().focus();
        } else {
            $('input.password').val('').hide();
        }

      });
    });
</script>

