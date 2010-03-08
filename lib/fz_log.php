<?php

define ('FZ_LOG_DEBUG', 'debug');
define ('FZ_LOG_ERROR', 'error');

function fz_log ($message, $type = null, $vars = null) {
    if ($type !== null)
        $type = '-'.$type;
    $log_file = fz_config_get ('app', 'log_dir').'/filez'.$type.'.log';

    $message = trim ($message);
    if ($vars !== null)
        $message .= var_export ($vars, true)."\n";

    $date = new Zend_Date ();
    $message = str_replace("\n", "\n   ", $message);
    $message = '['.$date->toString(Zend_Date::ISO_8601).'] '.$message."\n";

    if (file_put_contents ($log_file, $message, FILE_APPEND) === false) {
        debug_msg ('Can\'t open log file ('.$log_file.')');
    }

    if (fz_config_get('app', 'debug'))
        debug_msg ($message);
}

function debug_msg ($message) {
    $messages = flash_now ('debug');
    if (! is_array ($messages))
        $messages = array ();

    $messages [] = $message;

    flash_now ('debug', $messages);
}
