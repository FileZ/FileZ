<h2><?php echo $user ?><?php if ($EditUserRight): ?>
  <a class="awesome" href="<?php echo url_for ('/admin/users/'.$user->id.'/edit') ?>">
   <?php echo __('Edit') ?>
  </a>
<?php endif ?></h2>

<p><b><?php echo __('Email:') ?></b> <?php echo h($user->email) ?></p>
<p><b><?php echo __('Account created:') ?></b> <?php echo h($user->created_at) ?></p>
<p><b><?php echo __('Administrator:') ?></b> <?php echo $user->is_admin ? __('yes') : __('no') ?></p>

<table id="user_files" class="data" class="tablesorter">
<thead>
  <tr>
    <th><?php echo __('Name') ?></th>
    <th><?php echo __('Availability') ?></th>
    <th><?php echo __('Size') ?></th>
    <th><?php echo __('DL count') ?></th>
    <th><?php echo __('Actions') ?></th>
  </tr>
</thead>

<tbody>
<?php foreach ($user->getFiles () as $file): ?>
  <tr>
    <td><a href="<?php echo $file->getDownloadUrl () ?>"><?php echo h($file->file_name) ?></a></td>
    <td><?php echo __r('from %from% to %to%', array (
      'from' => ($file->getAvailableFrom  ()->get (Zend_Date::MONTH) ==
                 $file->getAvailableUntil ()->get (Zend_Date::MONTH)) ?
                 $file->getAvailableFrom ()->toString ('d') : $file->getAvailableFrom ()->toString ('d MMMM'),
      'to' =>  '<b>'.$file->getAvailableUntil ()->toString ('d MMMM').'</b>')) // FIXME I18N ?>
    </td>
    <td><?php echo h($file->getReadableFileSize ()) ?></td>
    <td><?php echo (int) $file->download_count ?></td>
    <td><a href="<?php echo $file->getDownloadUrl () . '/delete' ?>"><?php echo __('Delete') ?></a></td>
  </tr>
<?php endforeach ?>
</tbody>
</table>
