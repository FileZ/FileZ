<h2><?php echo _('Manage users') ?></h2>

<!-- TODO : find a jquery plugin to order and paginate the user list -->

<table id="user_list" class="data">
  <tr>
    <th><?php echo _('Name') ?></th>
    <th><?php echo _('Role') ?></th>
    <th><?php echo _('File count') ?></th>
    <th><?php echo _('Disk usage') ?></th>
    <th><?php echo _('Expired files') ?></th>
    <th><?php echo _('Actions') ?></th>
  </tr>

<?php foreach ($users as $user): ?>
  <tr>
    <td><a href="<?php echo url_for ('/admin/users/'.$user->id) ?>"><?php echo $user ?></a></td>
    <td><?php echo ($user->is_admin) ? _('admin') : '-' ?></td>
    <td><?php echo count ($user->getFiles ()) ?></td>
    <td><?php echo '125Mo' /* TODO */ ?></td>
    <td><?php echo '15' /* TODO */ ?></td>
    <td><a href="<?php echo url_for ('/admin/users/'.$user->id.'/delete') ?>"><?php echo _('Delete') /* TODO */ ?></a></td>
  </tr>
<?php endforeach ?>
</table>
<p><a href="<?php echo url_for ('/admin/users/new') ?>"><?php echo _('Create a new user') ?></a></p>
