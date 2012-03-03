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
 * Load config/filez.ini in "option ('fz_config')".
 * @return boolean  Whether if filez.ini was found or not
 */
function fz_config_load ($config_dir) {
    $result = true;

    $config = parse_ini_file ($config_dir.'filez.ini', true);
    if (empty ($config)) {
        $config = array();
        $result = false;
        trigger_error ('Missing or malformed config file.', E_USER_WARNING);
    }

    $default = parse_ini_file ($config_dir.'filez.default.ini', true);
    if (empty ($default)) {
        trigger_error ('Missing file "filez.default.ini".', E_USER_ERROR);
    }

    option ('fz_config', merge_config ($config, $default));
    
    return $result;
}

/**
 * Merge 2 configuration array. $user configuration will overide $default
 * configuration.
 *
 * @param array $user
 * @param array $default
 * @return array
 */
function merge_config ($user, $default) {
    $result = array ();
    foreach ($default as $section => $values) {
        $result [$section] = (array_key_exists ($section, $user) ?
            array_merge ($default[$section], $user[$section]) : $default[$section]);
    }
    return $result  + array_diff_key ($user, $default);
}

/**
 * Return a config var
 */
function fz_config_get ($section = null, $var = null, $default = null) {
    $conf = option ('fz_config');
    if (! is_array ($conf))
        return $default;

    if ($section === null) {
        return $conf;
    }
    if (array_key_exists ($section, $conf)) {
        if ($var === null)
            return $conf [$section];
        if (array_key_exists ($var, $conf[$section]))
            return $conf [$section][$var];
    }

    return $default;
}

/**
 * Write config to the config/filez.ini file.
 * (Used in installer).
 */
function fz_config_save ($config, $file) {
    return file_put_contents ($file, fz_serialize_ini_array($config, true));
}

function fz_serialize_ini_array ($assoc_arr, $has_sections = false) {
    $content = "";

    if ($has_sections) {
        foreach ($assoc_arr as $section => $values) {
            $content .= "\n[$section]\n".fz_serialize_ini_array($values);
        }
    }
    else {
        foreach ($assoc_arr as $key => $value) {
            if (is_array ($value))
                foreach ($value as $v)
                    $content .= "{$key}[] = \"$v\"\n";
            elseif (is_numeric ($value))
                $content .= "$key = $value\n";
            else if($value != "")
                $content .= "$key = \"$value\"\n";
        }
    }

    return $content;
}

