<?php

/**
 * Load config/filez.ini in "option ('fz_config')".
 * @return array    associative array with sections on config keys
 */
function fz_config_load ($config_file) {
    $config = parse_ini_file ($config_file, true);
    if (empty ($config)) {
        die ('Missing or malformed config file.');
    }

    option ('fz_config', $config);
    return $config;
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

