<?php
/**
 * Copyright 2010  Université d'Avignon et des Pays de Vaucluse 
 * email: gpl@univ-avignon.fr
 *
 * This file is part of Filez.
 *
 * Filez is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Filez is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Filez.  If not, see <http://www.gnu.org/licenses/>.
 */

define ('FZ_LOG_DEBUG',      'debug');
define ('FZ_LOG_ERROR',      'error');
define ('FZ_LOG_CRON',       'cron');
define ('FZ_LOG_CRON_ERROR', 'cron-error');

function fz_log ($message, $type = null, $vars = null) {
    if ($type !== null)
        $type = '-'.$type;

    $message = trim ($message);
    if ($vars !== null)
        $message .= var_export ($vars, true)."\n";

    //echo '"'.__NAMESPACE__.'"' ; die;
    $message = str_replace("\n", "\n   ", $message);
    $message = '['.strftime ('%F %T').'] ['
            .$_SERVER["REMOTE_ADDR"].'] '
            .$message."\n";

    if (fz_config_get ('app', 'log_dir') !== null) {
        $log_file = fz_config_get ('app', 'log_dir').'/filez'.$type.'.log';
        if (file_put_contents ($log_file, $message, FILE_APPEND) === false) {
            trigger_error('Can\'t open log file ('.$log_file.')', E_USER_WARNING);
        }
    }

    if (option ('debug') === true)
        debug_msg ($message);
}

function debug_msg ($message) {
    /*/ FIXME
    if (fz_config_get('app', 'debug'))
        return;
    /**/
    $messages = option ('debug_msg');
    if (! is_array ($messages))
        $messages = array ();

    $messages [] = $message;

    option ('debug_msg', $messages);
}
