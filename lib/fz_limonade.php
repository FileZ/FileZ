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

