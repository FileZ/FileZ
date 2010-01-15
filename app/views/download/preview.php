

<h2 class="filename">
  <?php echo h($file->file_name) ?> (<?php echo $file->getReadableFileSize () ?>)
</h2>
<section id="preview-file">
  <?php if ($file->comment): ?>
    <p>Commentaire associé au fichier: <?php echo h($file->comment) ?></p>
  <?php endif ?>
  <p>
    Votre téléchargement devrait démarrer d'ici quelques secondes, si ce n'est pas le cas
    <a href="<?php echo $file->getDownloadUrl ()?>/download">cliquez ici</a>.
  </p>
</section>
  
