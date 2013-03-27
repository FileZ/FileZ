
<?php if (! empty ($notifs)): ?>
  <div id="install-notifs">
    <ul>
    <?php foreach ($notifs as $n): ?>
      <li><b><?php echo $n ?></b></li>
    <?php endforeach ?>
    </ul>
  </div>
<?php endif ?>

<?php if (! empty ($errors)): ?>
  <div id="install-errors">
    <p>Errors occurred while finishing the installation</p>

    <ul>
    <?php foreach ($errors as $e): ?>
      <li><b><?php echo $e['title'] ?></b><?php echo (array_key_exists ('msg', $e) ? '<br />'.$e['msg'] : '') ?></li>
    <?php endforeach ?>
    </ul>

    <p>You have to correct your configuration in config/filez.ini.</p>
  </div>
<?php endif ?>
<p><a href="<?php echo url_for('/') ?>" class="awesome large blue">Start Filez now</a></p>



