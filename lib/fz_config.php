<?php

/**
 * Load config/filez.ini in "option ('fz_config')".
 * @return array    associative array with sections on config keys
 */
function fz_config_load ($config_file, $default_config_file) {

    $config = parse_ini_file ($config_file, true);
    if (empty ($config)) {
        trigger_error ('Missing or malformed config file.', E_USER_WARNING);
    }

    $default = parse_ini_file ($default_config_file, true);
    if (empty ($default)) {
        trigger_error ('Missing file "filez.default.ini".', E_USER_ERROR);
    }

    option ('fz_config', merge_config ($config, $default));
    return $config;
}

function merge_config ($user, $default) {
    $result = array ();
    foreach ($default as $section => $values) {
        $result [$section] = (array_key_exists ($section, $user) ?
            array_merge ($default[$section], $user[$section]) : $default[$section]);
    }
    return $result + array_diff_key ($user, $default);
}

/**
 * Return a config var
 */
function fz_config_get ($section, $var = null, $default = null) {
    $conf = option ('fz_config');
    if (array_key_exists ($section, $conf)) {
        if (array_key_exists ($var, $conf[$section]))
            return $conf [$section][$var];
        else if ($var === null)
            return $conf [$section];
    }

    return null;
}

/**
 * Write config to the config/filez.ini file.
 * (Used in installer).
 */
function fz_config_write () {
    // TODO
}

