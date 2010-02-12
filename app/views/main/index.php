
<h2>Déposer un nouveau fichier :</h2> 
<section id="new-file">
<form method="POST" enctype="multipart/form-data" action="<?php echo url_for ('upload') ?>" id="upload-form">
  <input type="hidden" name="APC_UPLOAD_PROGRESS" id="upload-id"  value="<?php echo $upload_id ?>" />
  <div id="file">
    <label for="file">Fichier :</label>
    <div id="input-file">
      <input type="button" value="Sélectionnez un fichier" id="choose-file-button" style="display: none;" />
      <input type="file" id="file-input" name="file" value="" alt="Fichier à déposer" />
    </div>
  </div>
  <div id="duration">
    <label for="duration">Durée :</label>
    <select id="select-duration" name="duration" alt="Sélectionnez une durée">
      <option value="3">3 jours</option>
      <option value="4">4 jours</option>
      <option value="5">5 jours</option>
      <option value="6">6 jours</option>
      <option value="7">7 jours</option>
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

  <!--
  <script type="text/javascript">

    // TODO mettre ce code dans un fichier

    var emailModalConf = {
      content: {
        title: {
          text: 'Envoyer le fichier par email', // TODO i18n
          button: 'Annuler'
        },
        text: '<p><label for="to">Destinataires séparés par des virgules :</label><input type="text" class="to" name="to" /></p>'+
              '<p><label for="msg">Message :</label><textarea name="msg"></textarea></p>' +
              '<p><input type="submit" value="Envoyer" /></p>'
      },
      position: {
        target: $(document.body), // Position it via the document body...
        corner: 'center' // ...at the center of the viewport
      },
      show: {
        when: 'click', // Show it on click
        solo: true // And hide all other tooltips
      },
      hide: false,
      style: {
        width: { min: 500, max: 500 },
        padding: '14px',
        border: {
          width: 9,
          radius: 9,
          color: '#666666'
        },
        name: 'light'
      },
      api: {
        beforeShow: function() {
          // Fade in the modal "blanket" using the defined show speed
          $('#qtip-blanket').fadeIn(this.options.show.effect.length);
        },
        beforeHide: function() {
          // Fade out the modal "blanket" using the defined hide speed
          $('#qtip-blanket').fadeOut(this.options.hide.effect.length);
        },
        onShow: function () {
          //$('form', $(this).elements.content).ajaxForm ();
        }
      }
    };

    // @var interval ID
    var progressChecker = 0;

    // @var boolean 
    var uploadFinished = true;

    /**
     * Function called on form submission
     */
    function onFileUploadStart (data, form, options) {
      console.log ('upload starts...');
      uploadFinished = false;
      $('#start-upload').hide ();
      $("#upload-loading").show ();
      $("#upload-progress").progressBar ({
        barImage: 'resources/images/progressbg_green.gif', // FIXME URI
        boxImage: 'resources/images/progressbar.gif' // FIXME URI
      });

      progressChecker = setInterval (function () {
        $.getJSON ('<?php echo url_for ('upload/progress/'.$upload_id) ?>', 
          function (data){
            console.log (data);

            if (data == false) {
              // we assume APC and "apc.rfc1867 = on" is not configured
              clearInterval (progressChecker); // We don't need to call the progress checker again
              return;
            }

            $("#upload-loading").hide ();
            $('#upload-progress').show ();

            if (data.done == 1) {
              clearInterval (progressChecker); 
              if (uploadFinished) {
                reloadUploadForm ();
              }
              else {
                $("#upload-loading").show ();
                $('#upload-progress').hide ();
              }
            }
            else {
              var percentage = Math.floor (100 * parseInt (data.current) / parseInt (data.total));
              $("#upload-progress").progressBar (percentage);
            }
          }
        )}, <?php echo $refresh_rate ?>);
    }

    /**
     * Function called once the file has been successfully uploaded
     */
    function onFileUploadEnd (data, status) {
      console.log ('upload ends.');
      uploadFinished = true;
      clearInterval (progressChecker); 
      reloadUploadForm ();
      console.log (data);

      if (data.status == 'ok') {
        var files = $('ul#files');
        var cssClass = files.children ('li:first').hasClass ('odd') ? 'even' : 'odd' ;
   
        files.prepend (
          '<li class="file '+cssClass+'" style="display: none;">'+data.html+'</li>'
        );
        files.children ('li:first').slideDown (500);
        $('.file:first .actions .send-by-email', files).each (configureEmailModal);
      }
      else {
        $('header').append ('<p class="notif error">'
          +'Une erreur s\'est produite, veuillez réessayer'
          +'</p>');
      }
    }

    function reloadUploadForm () {
      //clearInterval (progressChecker);
      $('#start-upload').show ();
      $("#upload-progress").progressBar (0);
      $('#upload-progress').hide ();
      $('#upload-loading').hide ();
      $('#upload-id').val (uniqid ()); // APC_UPLOAD_PROGRESS id reset
    }

    function configureEmailModal () {
      var config = emailModalConf;
      config.content.text = '<form class="send-email-form" action="'+$(this).attr ('href')+'" method="POST">'
        + config.content.text + '</form>';
      $(this).click (function (e) {e.preventDefault()});
      $(this).qtip (config);
    }

    var ajaxFormOptions = { 
      beforeSubmit: onFileUploadStart, // pre-submit callback 
      success:      onFileUploadEnd,   // post-submit callback 
      resetForm:    true,              // reset the form after successful submit 
      iframe:       true,              // force the form to be submitted using an iframe
                                       // even if no file has been selected
      dataType: 'json'                 // force response type to JSON
    }; 
  
    $(document).ready (function () {
      $('#upload-form').ajaxForm (ajaxFormOptions);
      $('#upload-form').append ('<input type="hidden" name="is_async" value="1" />');
      $('.file .actions .send-by-email').each (configureEmailModal);

      // Create the modal backdrop on document load so all modal tooltips can use it
      $('<div id="qtip-blanket">').css({
        position: 'absolute',
        top: $(document).scrollTop(), // Use document scrollTop so it's on-screen even if the window is scrolled
        left: 0,
        height: $(document).height(), // Span the full document height...
        width: '100%', // ...and full width
        opacity: 0.7, // Make it slightly transparent
        backgroundColor: 'black',
        zIndex: 5000  // Make sure the zIndex is below 6000 to keep it below tooltips!
      })
      .appendTo(document.body) // Append to the document body
      .hide(); // Hide it initially
      
    });
  </script>
  -->

  </form>
</section>

<h2 id="uploaded-files-title">Vos fichiers déjà déposés :</h2>
<section id="uploaded-files">
  <ul id="files">
    <?php $odd = true; foreach ($files as $file): ?>
      <li class="file <?php echo $odd ? 'odd' : 'even'; $odd = ! $odd; ?>">
        <?php echo partial ('main/_file_row.php', array ('file' => $file)) ?> 
      </li>
    <?php endforeach ?>
  </ul>
</section>

