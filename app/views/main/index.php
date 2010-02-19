
<h2 class="new-file">Déposer un nouveau fichier</h2>
<section class="new-file">
<form method="POST" enctype="multipart/form-data" action="<?php echo url_for ('upload') ?>" id="upload-form">
  <input type="hidden" name="APC_UPLOAD_PROGRESS" id="upload-id"  value="<?php echo $upload_id ?>" />
  <div id="file">
    <label for="file">Fichier :</label>
    <div id="input-file">
      <input type="file" id="file-input" name="file" value="" alt="Fichier à déposer" />
    </div>
  </div>
  <div id="lifetime">
    <label for="lifetime">Durée :</label>
    <select id="select-lifetime" name="lifetime" alt="Sélectionnez une durée">
      <?php $default = fz_config_get ('app', 'default_file_lifetime', 10);
            $max     = fz_config_get ('app', 'max_file_lifetime',     20);
            for ($i = 1; $i <= $max; ++$i  ): ?>
        <option value=<?php echo "\"$i\"".($i == $default ? ' selected="selected" ' : '').'>'.$i ?> jours</option>
      <?php endfor ?>
    </select>
  </div>
  <div id="start-from">
    <label for="start-from">Date de début :</label>
    <input type="text" id="input-start-from" name="start-from" value="<?php echo $start_from ?>" alt="Sélectionnez une date de début" />
  </div>
  <div id="comment">
    <label for="comment">Commentaire :</label>
    <input type="text" id="input-comment" name="comment" value="" alt="Ajoutez un commentaire (facultatif)" />
  </div>
  <div id="upload">
    <input type="submit" id="start-upload" name="upload" class="awesome blue large" value="&raquo; Envoyer le fichier" />
    <div id="upload-loading"  style="display: none;"></div>
    <div id="upload-progress" style="display: none;"></div>
  </div>
  </form>
</section>

<h2 id="uploaded-files-title">Vos fichiers déjà déposés</h2>
<section id="uploaded-files">
  <ul id="files">
    <?php $odd = true; foreach ($files as $file): ?>
      <li class="file <?php echo $odd ? 'odd' : 'even'; $odd = ! $odd ?>" id="<?php echo 'file-'.$file->getHash() ?>">
        <?php echo partial ('main/_file_row.php', array ('file' => $file)) ?> 
      </li>
    <?php endforeach ?>
  </ul>
</section>


<script type="text/javascript">
    $(document).ready (function () {
      $('#input-start-from').datepicker ();
      $('#upload-form').initFilez ({
        fileList:         'ul#files',
        progressBox:      '#upload-progress',
        loadingBox:       '#upload-loading',
        progressBar: {
          enable:        <?php echo ($use_progress_bar ? 'true':'false') ?>,
          barImage:     '<?php echo public_url_for ('resources/images/progressbg_green.gif') ?>',
          boxImage:     '<?php echo public_url_for ('resources/images/progressbar.gif') ?>',
          refreshRate:   <?php echo $refresh_rate ?>,
          progressUrl:  '<?php echo url_for ('upload/progress/') ?>'
        },
        emailModalConf: {
          content: {
            title: {text: 'Envoyer le fichier par email' /* TODO i18n */},
            text: '<p><label for="to">Destinataires séparés par des virgules :</label><input type="text" class="to" name="to" /></p>'+
                  '<p><label for="msg">Message :</label><textarea name="msg"></textarea></p>' +
                  '<p><input type="submit" value="Envoyer" /></p>'
          }
        }
      });
      // On transforme le titre de la section "new-file" en lien (bouton)
      $('h2.new-file').wrapInner ($('<a href="#" class="awesome large"></a>'));
      $('h2.new-file a').click (function (e) {e.preventDefault();});
      $('h2.new-file a').qtipModal ({
        content: {
          title: {text: 'Ajouter un fichier' /* TODO i18n */},
          text: $('section.new-file')
        },
        style: {padding: '0', classes: {content: 'qtip-content qtip-new-file-content'}}
      });
      $('section.new-file').hide ();
    });
</script>

