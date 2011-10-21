<h2><?php echo __('FileZ settings') ?></h2>

<?php foreach ($config as $category=>$settings): ?>
	  <h3><?php echo $category; ?></h3>
<?php foreach ($settings as $set=> $value): ?>
	    <li><?php echo $set." = ".$value; ?></li>
<?php endforeach ?>
<?php endforeach ?>
