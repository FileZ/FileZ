
if (! console) // In case the browser don't have a console
  var console = {log: function (txt) {}};


$(document).ready (function () {
    $('#input-start-from').datepicker ();
    $('#choose-file-button').show ();
    $('#file-input').hide ();

    var uploader = new plupload.Uploader ({
        multi_selection:        false,
        runtimes :              'gears,html5,flash,silverlight,browserplus',
        browse_button :         'choose-file-button',
        max_file_size :         '700mb', // FIXME
        url :                   $('#upload-form').attr('action'),
        flash_swf_url :         '/plupload/js/plupload.flash.swf', // FIX uri
        silverlight_xap_url :   '/plupload/js/plupload.silverlight.xap' // FIX uris
        // drop_element: '' TODO
    });

    // fire when files are added to the queue
    uploader.bind('Init', function (up) {
        console.log ('Initialised');
    });

    // fire when files are added to the queue
    uploader.bind('FilesAdded', function (up, files) {
        /*
        $.each(files, function(i, file) {
            $('#filelist').append(
                '<div id="' + file.id + '">' +
                'File: ' + file.name + ' (' + plupload.formatSize(file.size) + ') <b></b>' +
                '</div>'
            );
        });
        */
    });

    // fire when the queue is changed
    uploader.bind('QueueChanged', function (up) {
        console.log ('QueueChanged');
        // if the queue is empty, we disable the submit button

        // if full we enable the submit button
        
    });

    // fire during file uploading
    uploader.bind('UploadProgress', function (up, file) {
        console.log ('UploadProgress '+file.percent);
        $("#upload-progress").progressBar (file.percent);
    });

    // fire when a file is to be uploaded
    uploader.bind('UploadFile', function (up, file) {
      console.log ('upload starts...');
      uploadFinished = false;
      $('#start-upload').hide ();
      $("#upload-progress").show ().progressBar ({
        barImage: 'resources/images/progressbg_green.gif', // FIXME URI
        boxImage: 'resources/images/progressbar.gif' // FIXME URI
      });
    });

    // fire when a file has been uploaded
    uploader.bind('FileUploaded', function (up, file, response) {
        console.log ('FileUploaded');
        console.log (arguments);
        // Submit the form
    });

    // fire on form submission
    $('#start-upload').click (function (e) {
        uploader.start();
        e.preventDefault();
    });

    uploader.init();
});