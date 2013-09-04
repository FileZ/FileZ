<!DOCTYPE html>
<html>
  <head>
    <title>FileZ</title>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <link rel="shortcut icon" type="image/x-icon" href="/favicon.ico" />

    <link rel="stylesheet" href="<?php echo public_url_for ('resources/css/html5-reset.css') ?>" type="text/css" media="all" />
    <link rel="stylesheet" href="<?php echo public_url_for ('resources/jquery.ui/css/cupertino/jquery-ui-1.7.2.custom.css') ?>" type="text/css" media="all" />
    <link rel="stylesheet" href="<?php echo public_url_for ('resources/js/qtip/jquery.qtip.min.css') ?>" type="text/css" media="all" />
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
    <script type="text/javascript" src="<?php echo public_url_for ('resources/js/qtip/jquery.qtip.pack.js') ?>"></script>
    <?php if (option ('locale')->getLanguage () != 'en'): ?>
      <script type="text/javascript" src="<?php echo public_url_for ('resources/jquery.ui/js/i18n/ui.datepicker-'.option ('locale')->getLanguage ().'.js') ?>"></script>
    <?php endif ?>
    <script type="text/javascript" src="<?php echo public_url_for ('resources/jquery.validate/js/jquery.validate.min.js') ?>"></script>
    <script type="text/javascript" src="<?php echo public_url_for ('resources/js/zeroclipboard/ZeroClipboard.js') ?>"></script>
    <script type="text/javascript" src="<?php echo public_url_for ('resources/js/filez.js') ?>"></script>
    <script>
      function checkPortal() {
        if (top.location != self.document.location) {
          // if filez is displayed through a web portal hide logos and logout box
          document.getElementById('your-logo').style.display = 'none';
          document.getElementById('filez-logo').style.display = 'none';
          document.getElementById('auth-box').style.display = 'none';
        }
      }
    </script>
  </head>
  <body onLoad="checkPortal();">

    <?php echo partial ('layout/_header.php', (isset ($fz_user) ? array('fz_user' => $fz_user) : array())); ?>

    <article>
      <?php echo $content ?>
    </article>

    <?php echo partial ('layout/_footer.php', (isset ($fz_user) ? array('fz_user' => $fz_user) : array())); ?>

    <div id="modal-background"></div>

  </body>
</html>
