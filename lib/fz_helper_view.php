<?php

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
    return $msg; // TODO
}
