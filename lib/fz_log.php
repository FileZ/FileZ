<?php

define ('FZ_LOG_DEBUG', 'debug');
define ('FZ_LOG_ERROR', 'error');
define ('FZ_LOG_ERROR', 'cron');

function fz_log ($message, $type = null, $vars = null) {
    if ($type !== null)
        $type = '-'.$type;
    $log_file = fz_config_get ('app', 'log_dir').'/filez'.$type.'.log';

    $message = trim ($message);
    if ($vars !== null)
        $message .= var_export ($vars, true)."\n";

    $date = new Zend_Date ();
    $message = str_replace("\n", "\n   ", $message);
    $message = '['.$date->toString(Zend_Date::ISO_8601).'] ['
            .$_SERVER["REMOTE_ADDR"].'] '
            .$message."\n";

    if (file_put_contents ($log_file, $message, FILE_APPEND) === false) {
        trigger_error('Can\'t open log file ('.$log_file.')', E_USER_WARNING);
    }

    if (option ('debug'))
        debug_msg ($message);
}

function debug_msg ($message) {
    $messages = option ('debug_msg');
    if (! is_array ($messages))
        $messages = array ();

    $messages [] = $message;

    option ('debug_msg', $messages);
}
