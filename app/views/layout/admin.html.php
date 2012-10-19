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
    <link rel="stylesheet" href="<?php echo public_url_for ('resources/css/admin.css') ?>" type="text/css" media="all" />
    <?php if (fz_config_get ('looknfeel', 'custom_css', '') != ''): ?>
      <link rel="stylesheet" href="<?php echo public_url_for (fz_config_get ('looknfeel', 'custom_css')) ?>" type="text/css" media="all" />
    <?php endif ?>

    <!--[if lte IE 8]>
    <script type="text/javascript" src="<?php echo public_url_for ('resources/js/html5.js') ?>"></script>
    <![endif]-->
    <script type="text/javascript" src="<?php echo public_url_for ('resources/js/jquery-1.4.2.min.js') ?>"></script>
    <script type="text/javascript" src="<?php echo public_url_for ('resources/jquery.ui/js/jquery-ui-1.7.2.custom.min.js') ?>"></script>
    <script type="text/javascript" src="<?php echo public_url_for ('resources/js/qtip/jquery.qtip.pack.js') ?>"></script>
    <?php if (option ('locale')->getLanguage () != 'en'): ?>
      <script type="text/javascript" src="<?php echo public_url_for ('resources/jquery.ui/js/i18n/ui.datepicker-'.option ('locale')->getLanguage ().'.js') ?>"></script>
    <?php endif ?>
    <script type="text/javascript" src="<?php echo public_url_for ('resources/jquery.tablesorter/jquery.tablesorter.min.js') ?>"></script>
    <script type="text/javascript" src="<?php echo public_url_for ('resources/jquery.tablesorter/addons/pager/jquery.tablesorter.page.js') ?>"></script>
  </head>
  <body id="admin">

    <?php echo partial ('layout/_header.php', (isset ($fz_user) ? array('fz_user' => $fz_user) : array())); ?>

    <div id="content">

      <nav>
        <ul>
          <li><a href="<?php echo url_for ('admin') ?>"><?php echo __('Dashboard') ?></a></li>
          <li><a href="<?php echo url_for ('admin/users') ?>"><?php echo __('Users') ?></a></li>
          <li><a href="<?php echo url_for ('admin/files') ?>"><?php echo __('Files') ?></a></li>
          <li><a href="<?php echo url_for ('admin/config') ?>"><?php echo __('Settings') ?></a></li>
        </ul>
      </nav>
      <article>
        <?php echo $content ?>
      </article>

      <div class="clearboth"></div>
    </div>

    <?php echo partial ('layout/_footer.php', (isset ($fz_user) ? array('fz_user' => $fz_user) : array())); ?>

    <div id="modal-background"></div>

    <script type="text/javascript">
      // small snippet to select an item in the menu
      $(document).ready (function () {
        $('nav a').each (function () {
            console.log (document.location.href.indexOf ($(this).attr ('href')));
          if (document.location.href.indexOf ($(this).attr ('href')) != -1) {
            $('nav .selected').removeClass ('selected');
            $(this).addClass ('selected');
          }
        });
        // call the tablesorter plugin 
	    $("table").tablesorter({ 
          // sort on the first column and third column, order asc 
          sortList: [[0,0], [1,0]],
          widgets: ['zebra']
        })
        .tablesorterPager({container: $("#pager")}); 
      });
    </script>
  </body>
</html>
