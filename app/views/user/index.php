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

<?php foreach ($users as $user_item): ?>
  <tr>
    <td><a href="<?php echo url_for ('/admin/users/'.$user_item->id) ?>"><?php echo $user_item." (".$user_item->username.")" ?></a></td>
    <td><?php echo ($user_item->is_admin) ? _('admin') : '-' ?></td>
    <td><?php echo count ($user_item->getFiles ()) ?></td>
    <td><?php echo '125Mo' /* TODO */ ?></td>
    <td><?php echo '15' /* TODO */ ?></td>
<?php if ( $user->id != $user_item->id ) : ?>
    <td><a onclick="javascript:warning('<?php echo ($user_item->id."','".$user_item->firstname."','".$user_item->lastname."','".$user_item->username) ?>')" href="#"><?php echo _('Delete') /* TODO */ ?></a></td>
<?php else : ?>
    <td></td>
<?php endif ?>
  </tr>
<?php endforeach ?>
</table>
<p><a href="<?php echo url_for ('/admin/users/new') ?>"><?php echo _('Create a new user') ?></a></p>
<script type="text/javascript">
function warning(Id,Firstname,Lastname,Username) 
{
  var answer = confirm ("Are you sure you want to delete the user "+Firstname+" "+Lastname+" ("+Username+")?")
  if (answer) 
  window.location="<?php echo url_for ('/admin/users/')?>/"+Id+"/delete"
  else
  window.location="<?php echo url_for ('/admin/users/') ?>"
}
</script>
