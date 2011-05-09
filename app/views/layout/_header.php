
    <header>
      <h1>
        <?php if (fz_config_get ('looknfeel', 'your_logo', '') != ''): ?>
          <span id="your-logo">
            <img src="<?php echo public_url_for (fz_config_get ('looknfeel', 'your_logo')) ?>"/>
          </span>
        <?php endif ?>
        <span id="filez-header">
          <a href="<?php echo public_url_for ('/') ?>" id="filez-logo">
            <img src="<?php echo public_url_for ('resources/images/filez-logo.png') ?>" title="filez" />
          </a>
          <?php echo __('Share files for a limited time') ?>
        </span>
        <span style="display: block; clear: both;"></span>
      </h1>
      <?php if (array_key_exists ('notification', $flash)): ?>
        <p class="notif ok"><?php echo $flash ['notification'] ?></p>
      <?php endif ?>
      <?php if (array_key_exists ('error', $flash)): ?>
        <p class="notif error"><?php echo $flash ['error'] ?></p>
      <?php endif ?>

      <?php if (isset ($fz_user)): ?>
        <p id="auth-box">
        <?php if ( $fz_user->is_admin ): ?>
          <a href="<?php echo url_for ('/admin') ?>" title="<?php echo __('Administration') ?>"><?php echo __('Administration') ?></a> | 
       <?php endif ?>
          <?php echo $fz_user->email ?> |
          <a id="logout" href="<?php echo url_for ('/logout') ?>" title="<?php echo __('Log out') ?>">&nbsp;</a>
        </p>
      <?php endif ?>
    </header>
