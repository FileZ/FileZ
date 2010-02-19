
if (! console) // In case the browser don't have a console
    var console = {log: function (txt) {}};

(function($) {

// Default settings
var settings = {};

// interval ID
var progressCheckerLoop = 0;


/*******************************************************************************
 * PUBLIC METHODS
 ******************************************************************************/


/**
 * Initialise actions event handlers
 */
$.fn.initFilez = function (options) {

    var emailAjaxFormOptions = {success: onEmailFormSent, dataType: 'json'};

    settings = jQuery.extend(true, {
        refreshRate: 2000,
        useProgressBar: false,
        emailModalConf: {
            position:     {target: $(document.body), corner: 'center'},
            show:         {when: 'click', solo: true},
            hide:         false,
            style: {
                width:    {min: 500, max: 500},
                padding:  '14px',
                border:   {width: 9, radius: 9, color: '#666666'},
                name:     'light'
            },
            api: {
                beforeShow: function() {
                    $('#modal-background').fadeIn(this.options.show.effect.length);
                },
                onShow: function () {
                    $('form', $(this.elements.content))
                        .data('qtip', this.elements.target[0])
                        .ajaxForm (emailAjaxFormOptions);
                },
                beforeHide: function() {$('#modal-background').fadeOut(this.options.hide.effect.length);}
            }
        }
    }, options);
    console.log(settings);

    $(this).ajaxForm ({
        beforeSubmit: onFormSubmit,     // pre-submit callback
        success:      onFileUploadEnd,  // post-submit callback
        resetForm:    true,             // reset the form after successful submit
        dataType:     'json',           // force response type to JSON
        iframe:       true              // force the form to be submitted using an iframe
    });

    if (settings.progressBar.enable) {
        // let the server knows it has to return JSON
        $(this).append ('<input type="hidden" name="is_async" value="1" />');
        $(this).append ('<input type="hidden" name="APC_UPLOAD_PROGRESS" id="upload-id"  value="'+uniqid ()+'" />');
    }

    // Initialise actions event handlers
    $('.file .actions').initFileActions();
};

/**
 *
 */
$.fn.initFileActions = function () {
    var emailLink = $('.send-by-email', this);
    var config = settings.emailModalConf;
    config.content.text =
        '<form class="send-email-form" action="'+emailLink.attr ('href')+'" method="POST">'+
            config.content.text +
        '</form>';
    emailLink.click (function (e) {e.preventDefault();})
             .qtip  (config);

    var deleteLink = $('.delete', this);
    deleteLink.click (function (e) {
        e.preventDefault();
    });

    var extendLink = $('.extend', this);
    extendLink.click (function (e) {
        e.preventDefault();
    });

}


/*******************************************************************************
 * PRIVATE METHODS
 ******************************************************************************/


/*------------------------------------------------------------------------------
 * EVENT HANDLERS
 *----------------------------------------------------------------------------*/

var onEmailFormSent = function (data, status, form) {
    if (data.status && data.status == 'success') {
        form.clearForm();
        form.data ('qtip').qtip('hide');
    } else if (data.status && data.status == 'error') {
        alert (data.statusText);
    } else {
        alert ('Unknown error.');
    }

}

/**
 * Process informations returned by the server about current file upload progress
 */
var onFileUpoadProgress = function (data, textStatus, xhr) {
    console.log (data);
    if (data == false) {
        onCheckProgressError (xhr, 'Unknown Error', null);
    }
    else if (data.done == 1) {
        clearInterval (progressCheckerLoop);
    }
    else {
        var percentage = Math.floor (100 * parseInt (data.current) / parseInt (data.total));
        $("#upload-progress").progressBar (percentage);
    }
};

/**
 * Function called when an error occurs while requesting progression of the
 * file being uploaded.
 *
 * TODO
 */
var onCheckProgressError = function (xhr, textStatus, errorThrown) {
    console.log ('Check Progress Error...');
    if (xhr.status == 501)
    {
        // APC is missing
    }
    else if (xhr.status == 404)
    {
        // Upload not found
    }

    notifyError (textStatus);
};

/**
 * Function called on form submission
 */
var onFormSubmit = function (data, form, options) {
    console.log ('upload starts...');
    $('#start-upload').hide (); // hidding the start upload button

    // If the progress bar is enabled
    if (settings.progressBar.enable) {
        $("#upload-loading").hide ();
        $('#upload-progress').show ().progressBar ({
            barImage: settings.progressBar.barImage,
            boxImage: settings.progressBar.boxImage
        });

        // Checking progress
        progressCheckerLoop = setInterval (function () {
            $.ajax({
                url:        settings.progressBar.progressUrl + $('#upload-id').val (),
                dataType:   "json",
                error:      onCheckProgressError,
                success:    onFileUpoadProgress
            });
        }, settings.progressBar.refreshRate);

    } else /* the progress bar is disabled */ {
        $("#upload-loading").show ();
        $('#upload-progress').hide ();
    }
};

/**
 * Function called once the file has been successfully uploaded
 */
var onFileUploadEnd = function (data, status) {
    console.log ('upload ends.');
    clearInterval (progressCheckerLoop);
    reloadUploadForm ();
    console.log (data);

    if (data.status && data.status == 'success') {
        appendFile (data.html);
    } else {
        notifyError ('Une erreur inconnue s\'est produite, veuillez réessayer'); // TODO i18n
    }
};

/*------------------------------------------------------------------------------
 * UI TOOLKIT
 *----------------------------------------------------------------------------*/

/**
 * Append a file (html code) to the top of the file list
 */
var appendFile = function (html) {
    var files = $(settings.fileList);
    var cssClass = files.children ('li:first').hasClass ('odd') ? 'even' : 'odd' ;

    files.prepend (
        '<li class="file '+cssClass+'" style="display: none;">'+html+'</li>'
        );
    files.children ('li:first').slideDown (500);
    $('.file:first .actionsemail', files).initFileActions();
}

var reloadUploadForm = function () {
    //clearInterval (progressCheckerLoop);
    $('#start-upload').show ();
    $(settings.progressBox).progressBar (0);
    $(settings.progressBox).hide ();
    $(settings.loadingBox).hide ();
    $('#upload-id').val (uniqid ()); // APC_UPLOAD_PROGRESS id reset
}


var notifyError = function (msg) {
    $('header').append ('<p class="notif error">'+msg+'</p>');
}

/* -----------------------------------------------------------------------------
 * MISC
 *----------------------------------------------------------------------------*/


// +   original by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
// +    revised by: Kankrelune (http://www.webfaktory.info/)
// %        note 1: Uses an internal counter (in php_js global) to avoid collision
// *     example 1: uniqid();
// *     returns 1: 'a30285b160c14'
// *     example 2: uniqid('foo');
// *     returns 2: 'fooa30285b1cd361'
// *     example 3: uniqid('bar', true);
// *     returns 3: 'bara20285b23dfd1.31879087'
var uniqid = function (prefix, more_entropy) {

    if (typeof prefix == 'undefined') {
        prefix = "";
    }

    var retId;
    var formatSeed = function (seed, reqWidth) {
        seed = parseInt(seed,10).toString(16); // to hex str
        if (reqWidth < seed.length) { // so long we split
            return seed.slice(seed.length - reqWidth);
        }
        if (reqWidth > seed.length) { // so short we pad
            return Array(1 + (reqWidth - seed.length)).join('0')+seed;
        }
        return seed;
    };

    // BEGIN REDUNDANT
    if (!this.php_js) {
        this.php_js = {};
    }
    // END REDUNDANT
    if (!this.php_js.uniqidSeed) { // init seed with big random int
        this.php_js.uniqidSeed = Math.floor(Math.random() * 0x75bcd15);
    }
    this.php_js.uniqidSeed++;

    retId  = prefix; // start with prefix, add current milliseconds hex string
    retId += formatSeed(parseInt(new Date().getTime()/1000,10),8);
    retId += formatSeed(this.php_js.uniqidSeed,5); // add seed hex string

    if (more_entropy) {
        // for more entropy we add a float lower to 10
        retId += (Math.random()*10).toFixed(8).toString();
    }

    return retId;
};

})(jQuery);