  <form method="POST" enctype="multipart/form-data" action="<?php echo url_for ('visitor') ?>" id="upload-form">
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
  <div id="start-from" style="display:none">
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
