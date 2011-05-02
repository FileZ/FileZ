<h2><?php echo $user ?></h2>

<p><b><?php echo _('Email') ?> :</b> <?php echo $user->email ?></p>
<p><b><?php echo _('Account created') ?> :</b> <?php echo $user->created_at ?></p>
<p><b><?php echo _('Administrator ?') ?> :</b> <?php echo $user->is_admin ? _('yes') : _('no') ?></p>

<table id="user_files" class="data">
  <tr>
    <th><?php echo _('Name') ?></th>
    <th><?php echo _('Availability') ?></th>
    <th><?php echo _('Size') ?></th>
    <th><?php echo _('DL count') ?></th>
    <th><?php echo _('Actions') ?></th>
  </tr>

<?php foreach ($user->getFiles () as $file): ?>
  <tr>
    <td><a href="<?php echo $file->getDownloadUrl () ?>"><?php echo $file->file_name ?></a></td>
    <td><?php echo __r('from %from% to %to%', array (
      'from' => ($file->getAvailableFrom  ()->get (Zend_Date::MONTH) ==
                 $file->getAvailableUntil ()->get (Zend_Date::MONTH)) ?
                 $file->getAvailableFrom ()->toString ('d') : $file->getAvailableFrom ()->toString ('d MMMM'),
      'to' =>  '<b>'.$file->getAvailableUntil ()->toString ('d MMMM').'</b>')) // FIXME I18N ?>
    </td>
    <td><?php echo $file->getReadableFileSize () ?></td>
    <td><?php echo (int) $file->download_count ?></td>
    <td><a href="<?php echo url_for ('TODO') ?>"><?php echo _('Delete') /* TODO */ ?></a></td>
  </tr>
<?php endforeach ?>
</table>
