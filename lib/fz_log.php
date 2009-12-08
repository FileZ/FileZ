<?php

define ('FZ_LOG_DEBUG', 'debug');
define ('FZ_LOG_ERROR', 'error');

function fz_log ($message, $type = null, $vars = null) {
    if ($type !== null)
        $type = '-'.$type;
    $log_file = fz_config_get ('app', 'log_dir').'/filez'.$type.'.log';

    $message = trim ($message);
    if ($vars !== null)
        $message .= "\n".var_export ($vars, true);

    $message = strftime ("\n[%Y-%m-%d %H:%M:%S] - ", time ()).$message;

    if (file_put_contents ($log_file, $message, FILE_APPEND) === false)
        trigger_error ('Can\' write log in "'.$log_file.'"', E_WARNING);
}
