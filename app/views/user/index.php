<h2><?php echo _('Manage users') ?></h2>

<!-- TODO : find a jquery plugin to order and paginate the user list -->

<table id="user_list" class="data">
  <tr>
    <th><?php echo _('Name') ?></th>
    <th><?php echo _('File count') ?></th>
    <th><?php echo _('Disk usage') ?></th>
    <th><?php echo _('Expired files') ?></th>
  </tr>

<?php foreach ($users as $user): ?>
  <tr>
    <td><a href="<?php echo url_for ('/admin/users/'.$user->id) ?>"><?php echo $user ?></a></td>
    <td><?php echo count ($user->getFiles ()) ?></td>
    <td><?php echo '125Mo' /* TODO */ ?></td>
    <td><?php echo '15' /* TODO */ ?></td>
  </tr>
<?php endforeach ?>
</table>
