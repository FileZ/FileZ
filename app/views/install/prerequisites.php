<div id="install">

<h1>Prerequisites</h1>

<div class="help">

    <?php foreach ($checks as $key => $msg): ?>
      <div class="check-item">
        <h2><?php echo $key ?> :</h2>
        <?php echo $msg ?>
      </div>
    <?php endforeach ?>

    <div class="warning">
        <p>If you changed something to your apache or system conf, dont forget to reload apache :</p>
        <pre>/etc/init.d/apache2 restart</pre>
    </div>

</div>


<div class="help">
    <p>The next form will help you to configure filez by generating the file "<i>config/filez.ini</i>".</p>
    <p>If there is an error 404, Apache rewrite module isn't configured correctly. <a href="https://github.com/FileZ/FileZ/wiki/FAQ">Read the FAQ</a>.</p>
    <!--[if lte IE 8]>
    <p>This form has not been tested in a potential phase of the moon bug trigger browser (IE for example). <b>PLEASE USE A DECENT BROWSER TO AVOID BUGs</b>.</p>
    <![endif]-->
</div>

<p class="submit">
    <a href="<?php echo url_for ('/') ?>" class="awesome large blue">Check system config again </a> or <a href="<?php echo url_for ('/configure') ?>" class="awesome large blue">continue</a>
</p>


</div>

