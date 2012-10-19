<h2><?php echo __('Manage users') ?></h2>

<?php if ($EditUserRight): ?>
<p><a href="<?php echo url_for ('/admin/users/new') ?>" class="awesome"><?php echo __('Create a new user') ?></a></p>
<?php endif ?>
<table id="user_list" class="data" class="tablesorter">
<thead>
  <tr>
    <th><?php echo __('Name') ?></th>
    <th><?php echo __('Username') ?></th>
    <th><?php echo __('Role') ?></th>
    <th><?php echo __('File count') ?></th>
    <th><?php echo __('Disk usage') ?></th>
    <!--<th><?php echo __('Expired files') ?></th>-->
    <?php if ($EditUserRight): ?><th><?php echo __('Actions') ?></th><?php endif ?>
  </tr>
</thead>

<tbody>
<?php foreach ($users as $user_item): ?>
  <tr>
    <td><a href="<?php echo url_for ('/admin/users/'.$user_item->id) ?>"><?php echo h($user_item) ?></a></td>
    <td><a href="<?php echo url_for ('/admin/users/'.$user_item->id) ?>"><?php echo h($user_item->username) ?></a></td>
    <td><?php echo ($user_item->is_admin) ? __('admin') : '-' ?></td>
    <td><?php if (0<count ($user_item->getFiles ())): ?>
      <a href="<?php echo url_for ('/admin/users/'.$user_item->id) ?>">
        <?php echo count ($user_item->getFiles ()) ?>
      </a>
    <?php else: ?>
      0
    <?php endif ?>
    </td>
    <td><?php echo $diskUsage[$user_item->id] ?></td>
    <!--<td><?php echo 'todo'/* TODO */ ?></td>-->
    <?php if ($EditUserRight): ?><td>
      <a href="<?php echo url_for ('/admin/users/'.$user_item->id.'/edit') ?>">
         <?php echo __('Edit') ?>
      </a>
    <?php if ( $fz_user->id != $user_item->id ) : // prevents self-deleting ?>
        <a onclick='javascript:return confirm (<?php echo json_encode( __r('Are you sure you want to delete the user "%displayname%" (%username%)', array ('displayname' => $user_item, 'username' => $user_item->username))) ?>)'
           href="<?php echo url_for ('/admin/users/'.$user_item->id.'/delete') ?>">
          <?php echo __('Delete') ?>
        </a>
    <?php endif ?>
    </td><?php endif /*EditUserRight*/?>
  </tr>
<?php endforeach ?>
</tbody>
</table>
<div id="pager" class="pager">
	<form>
		<img src="<?php echo public_url_for ('resources/jquery.tablesorter/addons/pager/icons/first.png'); ?>" class="first"/>
		<img src="<?php echo public_url_for ('ressources/jquery.tablesorter/addons/pager/icons/prev.png'); ?>" class="prev"/>
		<input type="text" class="pagedisplay"/>
		<img src="<?php echo public_url_for ('ressources/jquery.tablesorter/addons/pager/icons/next.png'); ?>" class="next"/>
		<img src="<?php echo public_url_for ('ressources/jquery.tablesorter/addons/pager/icons/last.png'); ?>" class="last"/>
		<select class="pagesize">
			<option selected="selected"  value="10">10</option>
			<option value="20">20</option>
			<option value="30">30</option>
			<option  value="40">40</option>
		</select>
	</form>
</div>
