
<!DOCTYPE html>
<html>
  <head>
    <title>FileZ</title>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <link rel="shortcut icon" type="image/x-icon" href="/favicon.ico">

    <link rel="stylesheet" href="<?php echo public_url_for ('resources/css/html5-reset.css') ?>" type="text/css" media="all" />
    <link rel="stylesheet" href="<?php echo public_url_for ('resources/jquery.ui/css/cupertino/jquery-ui-1.7.2.custom.css') ?>" type="text/css" media="all" />
    <link rel="stylesheet" href="<?php echo public_url_for ('resources/css/main.css') ?>" type="text/css" media="all" />
    <?php if (fz_config_get ('looknfeel', 'custom_css', '') != ''): ?>
      <link rel="stylesheet" href="<?php echo public_url_for (fz_config_get ('looknfeel', 'custom_css')) ?>" type="text/css" media="all" />
    <?php endif ?>

    <!--[if lte IE 8]>
    <script type="text/javascript" src="<?php echo public_url_for ('resources/js/html5.js') ?>"></script>
    <![endif]-->
    <script type="text/javascript" src="<?php echo public_url_for ('resources/js/jquery-1.4.2.min.js') ?>"></script>
    <script type="text/javascript" src="<?php echo public_url_for ('resources/js/jquery.form.js') ?>"></script>
    <script type="text/javascript" src="<?php echo public_url_for ('resources/js/jquery.progressbar.min.js') ?>"></script>
    <script type="text/javascript" src="<?php echo public_url_for ('resources/jquery.ui/js/jquery-ui-1.7.2.custom.min.js') ?>"></script>
    <script type="text/javascript" src="<?php echo public_url_for ('resources/jquery.ui/js/i18n/ui.datepicker-'.option ('locale')->getLanguage ().'.js') ?>"></script>
    <script type="text/javascript" src="<?php echo public_url_for ('resources/js/filez.js') ?>"></script>
  </head>
  <body>

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
          <?php echo __('Share files for a limited time.') ?>
        </span>
        <span style="display: block; clear: both;"></span>
      </h1>
      <?php if (array_key_exists ('notification', $flash)): ?>
        <p class="notif ok"><?php echo $flash ['notification'] ?></p>
      <?php endif ?>
      <?php if (array_key_exists ('error', $flash)): ?>
        <p class="notif error"><?php echo $flash ['error'] ?></p>
      <?php endif ?>
    </header>

    <article>
      <?php echo $content ?>
    </article>

    <footer>
      <a href="http://gpl.univ-avignon.f">Un logiciel libre de l'Université d'Avignon et des Pays de Vaucluse</a>
    </footer>

    <div id="modal-background"></div>
  </body>
</html>
