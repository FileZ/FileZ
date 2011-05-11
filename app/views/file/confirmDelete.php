
<h2><?php echo __r('Confirmez vous la suppression du fichier : %filename% ?', array('filename'=>'<span class="filename">'.h($file->file_name))) ?></span></h2>

<form method="post">
  <p style="padding: 2em 0;">
    <input type="submit" value="<?php echo __('Oui, supprimer ce fichier') ?>" class="delete"/> |
    <a href="#" onclick="javascript:history.go(-1); return false;"><?php echo __('Non, revenir à la page précédente') ?></a>
  </p>
</form>
