
<!DOCTYPE html>
<html>
  <head>
    <title>FileZ</title>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <link rel="shortcut icon" type="image/x-icon" href="/favicon.ico" />
    <link rel="stylesheet" href="<?php echo public_url_for ('resources/css/html5-reset.css') ?>" type="text/css" media="all" />
    <link rel="stylesheet" href="<?php echo public_url_for ('resources/css/main.css') ?>" type="text/css" media="all" />

    <?php if (fz_config_get ('looknfeel', 'custom_css', '') != ''): ?>
      <link rel="stylesheet" href="<?php echo public_url_for (fz_config_get ('looknfeel', 'custom_css')) ?>" type="text/css" media="all" />
    <?php endif ?>

    <!--[if lte IE 8]>
    <script type="text/javascript" src="<?php echo public_url_for ('resources/js/html5.js') ?>"></script>
    <![endif]-->

  </head>
  <body>

    <?php echo partial ('layout/_header.php', (isset ($fz_user) ? array('fz_user' => $fz_user) : array())); ?>

    <article>
      <div class="notif error">
        <?php echo $content ?>
      </div>
    </article>

    <?php echo partial ('layout/_footer.php', (isset ($fz_user) ? array('fz_user' => $fz_user) : array())); ?>

  </body>
</html>
