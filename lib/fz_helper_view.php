<?php

function public_url_for ($path) {
    $args = func_get_args();
    $paths = array(option('base_path'));
    $paths = array_merge($paths, $args);
    return call_user_func_array('file_path', $paths);
}

