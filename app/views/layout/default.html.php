
<!DOCTYPE html>
<html>
  <head>
    <title>FileZ</title>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />

    <link rel="stylesheet" href="<?php echo public_url_for ('resources/css/html5-reset.css') ?>" type="text/css" media="all" />
    <link rel="stylesheet" href="<?php echo public_url_for ('resources/jquery.ui/css/cupertino/jquery-ui-1.7.2.custom.css') ?>" type="text/css" media="all" />
    <link rel="stylesheet" href="<?php echo public_url_for ('resources/css/main.css') ?>" type="text/css" media="all" />

    <!--[if lte IE 8]>
    <script type="text/javascript" src="<?php echo public_url_for ('resources/js/html5.js') ?>"></script>
    <![endif]-->
    <script type="text/javascript" src="<?php echo public_url_for ('resources/js/jquery-1.3.2.js') ?>"></script>
    <script type="text/javascript" src="<?php echo public_url_for ('resources/js/jquery.form.js') ?>"></script>
    <script type="text/javascript" src="<?php echo public_url_for ('resources/js/jquery.qtip-1.0.0-rc3.min.js') ?>"></script>
    <script type="text/javascript" src="<?php echo public_url_for ('resources/js/jquery.progressbar.min.js') ?>"></script>
    <script type="text/javascript" src="<?php echo public_url_for ('resources/jquery.ui/js/jquery-ui-1.7.2.custom.min.js') ?>"></script>
    <script type="text/javascript" src="<?php echo public_url_for ('resources/jquery.ui/js/i18n/ui.datepicker-'.option ('locale')->getLanguage ().'.js') // FIXME ?>"></script>
    <script type="text/javascript" src="<?php echo public_url_for ('resources/js/filez.js') ?>"></script>

  </head>
  <body>

    <header>
      <h1>
        <a href="<?php echo public_url_for('/') ?>">
          <img src="<?php echo public_url_for ('resources/images/filez-logo.png') ?>" title="filez" />
        </a>
      </h1>
      <p>Cette application vous permet de distribuer des fichiers pour une durée limitée.</p>

      <?php if (array_key_exists ('notification', $flash)): ?>
        <p class="notif ok"><?php echo $flash ['notification'] ?></p>
      <?php endif ?>
    </header>

    <article>

      <?php echo $content ?>

    </article>

    <footer>
      Université d'Avignon et des Pays de Vaucluse
    </footer>

  </body>
</html>
