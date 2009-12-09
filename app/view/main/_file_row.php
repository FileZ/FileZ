
<p class="filename">
  <?php // TODO add a copy to clipboard button ?>
  <a href="http://<?php echo $file->getDownloadUrl () ?>">
    <span class="filename"><?php echo h($file->file_name) ?></span>
    <span class="url"     ><?php echo $file->getDownloadUrl () ?></span>
  </a>
</p>
<p class="download-counter">Téléchargé <?php echo $file->download_count ?> fois</p>
<p class="availability">disponible du 
  <?php if ($file->getAvailableFrom  ()->get (Zend_Date::MONTH) ==
            $file->getAvailableUntil ()->get (Zend_Date::MONTH)): ?>
    <?php echo $file->getAvailableFrom ()->toString ('d')?>
  <?php else: ?>
    <?php echo $file->getAvailableFrom ()->toString ('d MMMM') // TODO i18n ?>
  <?php endif ?>
  au <b><?php echo $file->getAvailableUntil ()->toString ('d MMMM') // TODO i18n ?></b>
</p>
<ul class="actions">
  <li><a href="#" class="send-by-email">Envoyer par email</a></li> 
  <li><a href="#" class="delete">Supprimer</a></li> 
  <li><a href="#" class="extend">Rendre disponible un jour de plus</a></li>
</ul>

