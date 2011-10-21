<h2><?php echo __('FileZ settings') ?></h2>

<?php foreach ($config as $category=>$settings): ?>
<?php if ($category!="db"): ?>
	  <h3><?php echo $category; ?></h3>
<?php foreach ($settings as $set=> $value): ?>
<?php if (is_array($value)) $value = implode(", ", $value) ?>
	    <li><?php echo $set." = ".$value; ?></li>
<?php endforeach ?>
	    <br>
<?php endif ?>
<?php endforeach ?>
