    <footer>
      <?php if (is_array (option ('debug_msg'))): ?>
        <div class="debug"><h3>Logged messages :</h3>
        <?php foreach (option ('debug_msg') as $msg): ?>
          <pre><?php echo $msg ?></pre>
        <?php endforeach ?>
        </div>
      <?php endif ?>

      <?php if (isset ($user)): ?>
        <p id="disk-usage"><?php echo __r('Using %space% of %quota%', array (
            // TODO this code should not be here
            'space' => '<b>'.bytesToShorthand (Fz_Db::getTable('File')->getTotalDiskSpaceByUser ($user)).'</b>',
            'quota' => fz_config_get('app', 'user_quota'))); ?>.
        </p>
      <?php endif ?>

      <div id="support">
        <?php if (fz_config_get('looknfeel', 'help_url')): ?>
          <a href="<?php echo url_for (fz_config_get('looknfeel', 'help_url')) ?>" class="help"><?php echo __('Find help') ?></a>
        <?php endif; ?>
        <?php if (fz_config_get('looknfeel', 'bug_report_href')): ?>
          <a href="<?php echo fz_config_get('looknfeel', 'bug_report_href') ?>" class="bug"><?php echo __('Report a bug') ?></a>
        <?php endif; ?>
      </div>

      <?php if (fz_config_get('looknfeel', 'show_credit')): ?>
        <a href="http://gpl.univ-avignon.fr">Un logiciel libre de l'Université d'Avignon et des Pays de Vaucluse</a>
      <?php endif ?>
    </footer>