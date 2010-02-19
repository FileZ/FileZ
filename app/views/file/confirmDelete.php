
<h2>Confirmez vous la suppression du fichier : <span class="filename"><?php echo h($file->file_name) ?> ?</span></h2>

<form method="post">
  <p style="padding: 2em 0;">
    <input type="submit" value="Oui, supprimer ce fichier" class="delete"/> |
    <a href="#" onclick="javascript:istory.go(-1);">Non, revenir à la page précédente</a>
  </p>
</form>
