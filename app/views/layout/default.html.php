
<!DOCTYPE html>
<html>
  <head>
    <title>FileZ</title>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />

    <link rel="stylesheet" href="<?php echo public_url_for ('resources/css/html5-reset.css') ?>" type="text/css" media="all" />
    <link rel="stylesheet" href="<?php echo public_url_for ('resources/jquery.ui/css/cupertino/jquery-ui-1.7.2.custom.css') ?>" type="text/css" media="all" />
    <link rel="stylesheet" href="<?php echo public_url_for ('resources/css/main.css') ?>" type="text/css" media="all" />

    <script type="text/javascript" src="<?php echo public_url_for ('resources/js/jquery-1.3.2.min.js') ?>"></script>
    <script type="text/javascript" src="<?php echo public_url_for ('resources/js/jquery.form.js') ?>"></script>
    <script type="text/javascript" src="<?php echo public_url_for ('resources/js/jquery.progressbar.min.js') ?>"></script>
    <script type="text/javascript" src="<?php echo public_url_for ('resources/jquery.ui/js/jquery-ui-1.7.2.custom.min.js') ?>"></script>
    <script type="text/javascript" src="<?php echo public_url_for ('resources/jquery.ui/js/i18n/ui.datepicker-'.option ('locale')->getLanguage ().'.js') // FIXME ?>"></script>
    <script type="text/javascript" src="<?php echo public_url_for ('resources/js/filez.js') ?>"></script>

    <script type="text/javascript">
      $(document).ready (function () {
      });
    </script>
  </head>
  <body>

    <header>
      <h1><img src="<?php echo public_url_for ('resources/images/filez-logo.png') ?>" title="filez" /></h1>
      <p>Cette application vous permet de déposer des fichiers pour une durée limitée.</p>
    </header>

    <article>

      <?php echo $content ?>

    </article>

    <footer>
      Université d'Avignon et des Pays de Vaucluse
    </footer>

  </body>
</html>
