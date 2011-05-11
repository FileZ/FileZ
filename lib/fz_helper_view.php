<?php

/**
 * @file
 * Short description.
 * 
 * Long description.
 * 
 * @package FileZ
 */

/**
 *
 * @param string $path
 * @return string Url containing the public path of the project
 */
function public_url_for ($path) {
    $args = func_get_args ();
    $paths = array (option ('base_path'));
    $paths = array_merge ($paths, $args);
    return call_user_func_array ('file_path', $paths);
}

/**
 * Truncate a string in the middle with "[...]"
 * 
 * @param string $str
 * @param integer $size
 * @return string Truncated string
 */
function truncate_string ($str, $maxSize) {
    if (($size = strlen ($str)) > $maxSize) {
        $halfDiff = ($size - $maxSize + 5) / 2;
        $str = substr ($str, 0, ($size / 2) - ceil ($halfDiff))
              .'[...]'
              .substr ($str, ($size / 2) + floor ($halfDiff));
    }
    return $str;
}


/**
 * Translate a string
 * @param string
 * @return string
 */
function __($msg) {
    if (! option ('translate')) return $msg;
    return option ('translate')->translate ($msg);
}

/**
 * Translate a string with different plural form
 * @param string    $sing Singular form
 * @param string    $plur Plural form
 * @param integer   
 * @return string
 */
function __p($sing, $plur, $nb) {
    if (! option ('translate')) return $msg;
    return option ('translate')->plural ($sing, $plur, $nb);
}

/**
 * Translate a string and subtitute values defined in $subtitution
 *
 * @param string    $msg
 * @param array     $subtitutions   ex: array('var'=>'real value') will replace
 *                                  %var% by 'real value
 * @return string
 */
function __r($msg, array $subtitutions) {
    $msg = __($msg);
    foreach ($subtitutions as $key => $value)
        $msg = str_replace ("%$key%", $value, $msg);
    return $msg;
}


/**
 * Transform a size in bytes to the shorthand format ('K', 'M', 'G')
 *
 * @param   string      $size
 * @return  integer
 */
function bytesToShorthand ($size) {
    return ($size >= 1073741824 ? (round ($size / 1073741824, 2).'G') : (
            $size >= 1048576    ? (round ($size / 1048576, 2).'M') : (
            $size >= 1024       ? (round ($size / 1024, 2).'K') :
                                           $size.'B')));
}

function doc_img_tag ($name) {

    return '<div class="img-block">'
        .'<img src="'.url_for('/').'doc/user/'.option ('locale')->getLanguage ().'/images/'.$name.'" />'
        .'</div>';

}

function get_mimetype_icon_url ($mimetype, $size = 32) {

    $mimetype = str_replace ('/', '-', $mimetype).'.png';
    $path = 'resources/images/icons/mimetypes/'.$size.'/';
    $mime_basesir = public_url_for ($path);
    $root = option ('root_dir').'/';

    if (file_exists ($root.$path.$mimetype))
        return $mime_basesir.$mimetype;

    $mimetype = str_replace ('application-', '', $mimetype);
    if (file_exists ($root.$path.$mimetype))
        return $mime_basesir.$mimetype;

    $mimetype = 'gnome-mime-'.$mimetype;
    if (file_exists ($root.$path.$mimetype))
        return $mime_basesir.$mimetype;

}

function check_cron() {
    if (! option ('installing')) {
        $lastCron = Fz_Db::getTable('Info')->getLastCronTimestamp();
        $freq = fz_config_get ('cron', 'frequency');

        if(strtotime($freq." ".$lastCron) <= time()) {
            Fz_Db::getTable('Info')->setLastCronTimestamp(date('Y-m-d H:i:s'));
            return "<script src='".url_for('admin/checkFiles')."'></script>";
        }
    }
}
