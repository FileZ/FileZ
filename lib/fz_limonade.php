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

function fz_dispatch        ($path_or_array, $controller, $action) 
    {return dispatch        ($path_or_array, 'fz_dispatcher', array ($controller, $action));}

function fz_dispatch_get    ($path_or_array, $controller, $action) 
    {return dispatch_get    ($path_or_array, 'fz_dispatcher', array ($controller, $action));}

function fz_dispatch_post   ($path_or_array, $controller, $action) 
    {return dispatch_post   ($path_or_array, 'fz_dispatcher', array ($controller, $action));}

function fz_dispatch_put    ($path_or_array, $controller, $action) 
    {return dispatch_put    ($path_or_array, 'fz_dispatcher', array ($controller, $action));}

function fz_dispatch_delete ($path_or_array, $controller, $action) 
    {return dispatch_delete ($path_or_array, 'fz_dispatcher', array ($controller, $action));}

function fz_dispatcher ($controller, $action) {
    $controller = 'App_Controller_'.$controller;
    $controllerInstance = new $controller ();
    return call_user_func (array ($controllerInstance, $action.'Action'));
}

function fz_php_error_handler ($errno, $errstr, $errfile, $errline) {
    $errortype = array (
        E_ERROR              => 'Error',
        E_WARNING            => 'Warning',
        E_PARSE              => 'Parsing Error',
        E_NOTICE             => 'Notice',
        E_CORE_ERROR         => 'Core Error',
        E_CORE_WARNING       => 'Core Warning',
        E_COMPILE_ERROR      => 'Compile Error',
        E_COMPILE_WARNING    => 'Compile Warning',
        E_USER_ERROR         => 'User Error',
        E_USER_WARNING       => 'User Warning',
        E_USER_NOTICE        => 'User Notice',
        E_STRICT             => 'Runtime Notice',
        E_RECOVERABLE_ERROR  => 'Catchable Fatal Error'
    );

    fz_log ("Error ($errortype[$errno]) $errstr in $errfile:$errline", FZ_LOG_ERROR);
    return error_handler_dispatcher ($errno, $errstr, $errfile, $errline);
}

function fz_exception_handler (Exception $e) {
    fz_log ($e, FZ_LOG_ERROR);
    return error_handler_dispatcher (SERVER_ERROR, $e->getMessage(), $e->getFile(), $e->getLine());
}

/**
 * Default not found error output
 *
 * @param string $errno
 * @param string $errstr
 * @param string $errfile
 * @param string $errline
 * @return string
 */
function not_found($errno, $errstr, $errfile=null, $errline=null)
{
  option('views_dir', option('error_views_dir'));
  $msg = h(rawurldecode($errstr));
  return html('<h1>'.__('Page not found')." :</h1><p><code>{$msg}</code></p>", error_layout());
}
