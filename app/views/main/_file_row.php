
<p class="filename">
  <?php // TODO add a copy to clipboard button ?>
  <?php // TODO add $file->comment ?>
  <a href="<?php echo $file->getDownloadUrl () ?>">
    <span class="filename"><?php echo h ( truncate_string ($file->file_name, 40)) ?></span>
    <span class="url"     ><?php echo $file->getDownloadUrl () ?></span>
  </a>
</p>
<p class="file-info">    
    <span class="filesize">(<?php echo $file->getReadableFileSize () ?>)</span>
    <span class="download-counter"><?php echo __r(array('Downloaded %x% time', 'Downloaded %x% times', (int) $file->download_count),
            array ('x' => (int) $file->download_count)) ?>
    </span>
</p>
<p class="availability"><?php echo __r('Available from %from% to <b>%to%</b>', array (
    'from' => ($file->getAvailableFrom  ()->get (Zend_Date::MONTH) ==
               $file->getAvailableUntil ()->get (Zend_Date::MONTH)) ?
               $file->getAvailableFrom ()->toString ('d') : $file->getAvailableFrom ()->toString ('d MMMM'),
    'to' =>  $file->getAvailableUntil ()->toString ('d MMMM'))) // FIXME I18N ?>
</p>
<ul class="actions">
  <li>
    <a href="<?php echo $file->getDownloadUrl () ?>/email"   class="send-by-email">
      <?php echo __('Email') ?>
    </a>
  </li>
  <li>
    <a href="<?php echo $file->getDownloadUrl () ?>/delete"   class="delete">
      <?php echo __('Delete') ?>
    </a>
  </li>
  <li>
    <a href="<?php echo $file->getDownloadUrl () ?>/extend"   class="extend">
      <?php echo __('Extend one more day') ?>
    </a>
  </li>
</ul>

