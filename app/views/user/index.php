<h2><?php echo _('Manage users') ?></h2>

<!-- TODO : find a jquery plugin to order and paginate the user list -->

<p><a href="<?php echo url_for ('/admin/users/new') ?>" class="awesome"><?php echo _('Create a new user') ?></a></p>

<table id="user_list" class="data">
  <tr>
    <th><?php echo _('Name') ?></th>
    <th><?php echo _('Role') ?></th>
    <th><?php echo _('File count') ?></th>
    <th><?php echo _('Disk usage') ?></th>
    <th><?php echo _('Expired files') ?></th>
    <th><?php echo _('Actions') ?></th>
  </tr>

<?php foreach ($users as $user_item): ?>
  <tr>
    <td><a href="<?php echo url_for ('/admin/users/'.$user_item->id) ?>"><?php echo $user_item." (".$user_item->username.")" ?></a></td>
    <td><?php echo ($user_item->is_admin) ? _('admin') : '-' ?></td>
    <td><?php echo count ($user_item->getFiles ()) ?></td>
    <td><?php echo '125Mo' /* TODO */ ?></td>
    <td><?php echo '15' /* TODO */ ?></td>
    <?php if ( $fz_user->id != $user_item->id ) : ?>
      <td>
        <a onclick="javascript:return confirm ('<?php echo __r('Are you sure you want to delete the user "%displayname%" (%username%)', array ('displayname' => $user_item, 'username' => $user_item->username)) ?>')"
           href="<?php echo url_for ('/admin/users/'.$user_item->id.'/delete') ?>">
          <?php echo _('Delete') ?>
        </a>
      </td>
    <?php else : ?>
      <td></td>
    <?php endif ?>
  </tr>
<?php endforeach ?>
</table>

