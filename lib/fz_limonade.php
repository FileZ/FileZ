<?php

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
