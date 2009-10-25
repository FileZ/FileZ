<?php

/**
 * Load config/filez.ini in "option ('fz_config')".
 * @return array    associative array with sections on config keys
 */
function fz_config_load () {
    $configFile = option ('root_dir').'/config/filez.ini';
    $config = parse_ini_file ($configFile, true);
    if (! $config) {
        // TODO handle missing config file or syntax error
    }

    option ('fz_config', $config);
    return $config;
}

/**
 * Write config to the config/filez.ini file.
 * (Used in installer).
 */
function fz_config_write () {
    // TODO
}

