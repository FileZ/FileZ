/**
 * Copyright 2010  Université d'Avignon et des Pays de Vaucluse 
 * email: gpl@univ-avignon.fr
 *
 * This file is part of Filez.
 *
 * Filez is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Filez is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Filez.  If not, see <http://www.gnu.org/licenses/>.
 */

if (! console) // In case the browser doesn't have a console
    var console = {log: function (txt) {}};

// Auto hide current notifications
$('document').ready (function () {$('.notif').configureNotification ();});

(function($) {

// Default settings
var settings = {};

// interval ID
var progressCheckerLoop = 0;

var uploadForm = null;


/*******************************************************************************
 * PUBLIC METHODS
 ******************************************************************************/


/**
 * Initialise actions event handlers
 */
$.fn.initFilez = function (options) {

    uploadForm = $(this);

    settings = jQuery.extend(true, {
        refreshRate: 2000,
        useProgressBar: false
    }, options);

    $(this).ajaxForm ({
        beforeSubmit: onFormSubmit,     // pre-submit callback
        success:      onFileUploadEnd,  // post-submit callback
        resetForm:    true,             // reset the form after successful submit
        dataType:     'json',           // force response type to JSON
        iframe:       true              // force the form to be submitted using an iframe
    });

    if (settings.progressBar.enable) {
        $(this).prepend ('<input type="hidden" name="'+settings.progressBar.upload_id_name+'" id="upload-id"  value="'+uniqid ()+'" />');
    }
    
    // let the server knows it has to return JSON
    $(this).attr ('action', $(this).attr ('action') + '?is-async=1');

    // Initialise actions event handlers
    $('.file').each (function () { $(this).initFileActions(); } );

    // Initialise email modal box
    $('.email-modal form').ajaxForm ({success: onEmailFormSent, dataType: 'json'});

    // Handle global ajax errors
    $(this).ajaxError(function(e, xhr, ajaxSettings, exception) {
        if (ajaxSettings.url.indexOf ('upload') != -1 &&
            ajaxSettings.url.indexOf ('progress') == -1 ) { // ajaxForm error
            // Close the modal box
            $('.ui-dialog-content').dialog('close');
            reloadUploadForm ();
            // Display error
            notifyError (settings.messages.unknownErrorHappened);
        }
    });

    return $(this);
};

/**
 *
 */
$.fn.initFileActions = function () {

    $('a.share', this).click (function (e) {
        e.preventDefault();
        var modal = $('#share-modal');
        var fileUrl = $(this).attr ('href')
                .substring (-1, $(this).attr ('href').lastIndexOf ('/'));

        var filename = $('.filename a', $(this).closest('.file-description')).html();

        $('#share-modal #share-link a').attr ('href', fileUrl).html (fileUrl);
        $('#share-modal #share-destinations li a').each (function () {
            $(this).attr ('href', this.getAttribute ('data-url')
                .replace ('%url%', fileUrl)
                .replace ('%filename%', $.trim(filename)));
        });

        $('#share-modal #share-destinations li.email a').click (function (e) {
            e.preventDefault ();
            $('#share-modal').dialog ('close');

            $('form', $('#email-modal')).attr ('action', $(this).attr ('href'))
            $('.open-email-client')
                .attr ('href', 'mailto:?body='+settings.messages.emailMessage+' : '+fileUrl)
                .click (function (e) { $('.ui-dialog-content').dialog('close'); });

            $('#email-modal').dialog ('open');
        });

        $('#share-modal').dialog ('option', 'title', filename);
        $('#email-modal').dialog ('option', 'title', filename);

        modal.dialog ('open');
    });

    $('a.delete', this).click (function (e) {
        if (confirm (settings.messages.confirmDelete))
            $('<form action="'+$(this).attr('href')+'" method="post"></form>').appendTo('body').submit();
        e.preventDefault();
    });

    $('a.extend', this).click (function (e) {
        e.preventDefault();
        var fileListItem = $(this).closest ('li.file');
        var link = $(this);
        $.getJSON($(this).attr('href'), function (data) {
            if (data.status == undefined) {
                notifyError (settings.messages.unknownErrorHappened);
            } else if (data.status == 'success') {
                link.qtip('destroy');
                fileListItem.html (data.html);
                fileListItem.initFileActions ();
            } else if (data.status == 'error'){
                notifyError (data.statusText);
            }
        });
    });

    // initialize tips
    $('a.extend, a.delete, a.share').qtip({
        content: {
           attr: 'title'
        },
        position: {
            my: 'bottom center', 
            at: 'top center'
        },
        style: { 
            tip: true,
            classes: 'ui-tooltip-dark ui-tooltip-rounded ui-tooltip-shadow'
        }
    });

    return $(this);
}

$.fn.hideNotifDelayed = function () {
    $(this).delay (10000).animate({
        opacity: 'toggle', height: 'toggle',
        paddingTop: 0, paddingBottom: 0,
        marginTop: 0, marginBottom: 0
    }, {
        duration: 3000,
        complete:  function () {$(this).remove ()}
    });
}

$.fn.configureNotification = function () {
    if ($(this).hasClass ('ok'))
        $(this).hideNotifDelayed();

    $('<a href="#" class="notif-close">Close</a>')
        .prependTo ($(this))
        .click (function () {
            $(this).closest ('.notif').click (function () {$(this).remove ();});
        });

    return $(this);
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
        alert (settings.messages.unknownError);
    }
}

/**
 * Process informations returned by the server about current file upload progress
 */
var onFileUpoadProgress = function (data, textStatus, xhr) {
    console.log (data);

    if (data == false || data == null) {
        onCheckProgressError (xhr, settings.messages.unknownError, null);
    }
    else if (data.total > settings.maxFileSize) {
        xhr.abort (); // FIXME
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
        console.log ('Upload monitor not installed.')
    }
    else if (xhr.status == 404)
    {
        // Upload not found
        console.log ('Upload progress not found.')
    }
    clearInterval (progressCheckerLoop);

    //notifyError (textStatus);
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
                url:      settings.progressBar.progressUrl + '/' + $('#upload-id').val (),
                dataType: "json",
                error:    onCheckProgressError,
                success:  onFileUpoadProgress
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

    if (data.status == 'success') {
        appendFile (data.html);
        $('#disk-usage-value').html (data.disk_usage);
        notify (data.statusText);
    } else if (data.status == 'error'){
        notifyError (data.statusText);
    } else {
        notifyError (settings.messages.unknownErrorHappened);
    }

    // Hide the modal box
    $('.ui-dialog-content').dialog('close');

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
};

var reloadUploadForm = function () {
    //clearInterval (progressCheckerLoop);
    uploadForm.resetForm ();
    $('#start-upload').show ();
    $(settings.progressBox).progressBar (0);
    $(settings.progressBox).hide ();
    $(settings.loadingBox).hide ();
    $('#upload-id').val (uniqid ()); // upload id reset
    $('#input-password').hide();
};


/**
 * Display an error notification and register the delete handler
 */
var notifyError = function (msg) {
    $('.notif').remove();
    $('<p class="notif error">'+msg+'</p>')
        .appendTo ($('header'))
        .configureNotification ();
};

/**
 * Display a success notification and register the delete handler
 */
var notify = function (msg) {
    $('.notif').remove();
    $('<p class="notif ok">'+msg+'</p>')
        .appendTo ($('header'))
        .configureNotification ();
};


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
