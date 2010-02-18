
<h2>Envoyer le fichier par email <span class="filename">(<?php echo h($file->file_name) ?>)</span></h2>

<form method="POST" class="send-email-form">

  <!-- TODO i18n -->
  <p>
    <label for="to">Destinataires séparés par des virgules :</label>
    <input type="text" class="to" name="to" value="<?php echo params('to') ?>"/>
  </p>
  <p>
    <label for="msg">Message (l'adresse du fichier sera ajoutée automatiquement) :</label>
    <textarea cols="80" rows="10" name="msg" value="<?php echo params ('msg') ?>"></textarea>
  </p>
  <p>
    <input type="submit" value="Envoyer" />
  </p>
    
</form>
