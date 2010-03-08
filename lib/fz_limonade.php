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
    fz_log ("Error ($errno) $errstr in $errfile:$errline", FZ_LOG_ERROR);
    return error_default_handler ($errno, $errstr, $errfile, $errline);
}

function fz_exception_handler (Exception $e) {
    fz_log ($e, FZ_LOG_ERROR);
    return error_handler_dispatcher (SERVER_ERROR, 'Unknown error', $e->getFile(), $e->getLine());
}
