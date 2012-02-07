<h2><?php echo __('Admin dashboard') ?></h2>

<?php echo __r('Manage %NumberOfUsers% users</a> and %NumberOfFiles% files</a>.', array(
    'NumberOfUsers'=>'<a href="'.url_for ('admin/users').'">'.$numberOfUsers,
    'NumberOfFiles'=>'<a href="'.url_for ('admin/files').'">'.$numberOfFiles )) ?> (<?php echo $totalDiskSpace ?>).