<?php

/**
 * @file
 * Short description.
 * 
 * Long description.
 * 
 * @package FileZ
 */

function fz_dispatch        ($path_or_array, $controller, $action) 
    {return dispatch        ($path_or_array, 'fz_dispatcher', array ('params' => array ('controller' => $controller, 'action' => $action)));}

function fz_dispatch_get    ($path_or_array, $controller, $action) 
    {return dispatch_get    ($path_or_array, 'fz_dispatcher', array ('params' => array ('controller' => $controller, 'action' => $action)));}

function fz_dispatch_post   ($path_or_array, $controller, $action) 
    {return dispatch_post   ($path_or_array, 'fz_dispatcher', array ('params' => array ('controller' => $controller, 'action' => $action)));}

function fz_dispatch_put    ($path_or_array, $controller, $action) 
    {return dispatch_put    ($path_or_array, 'fz_dispatcher', array ('params' => array ('controller' => $controller, 'action' => $action)));}

function fz_dispatch_delete ($path_or_array, $controller, $action) 
    {return dispatch_delete ($path_or_array, 'fz_dispatcher', array ('params' => array ('controller' => $controller, 'action' => $action)));}

function fz_dispatcher () {
    $controller = 'App_Controller_'.params ('controller');;
    $controllerInstance = new $controller ();
    $controllerInstance->init ();
    return call_user_func (array ($controllerInstance, params ('action').'Action'));
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


/**
 * Tells if the request protocol is HTTPS
 */
function fz_is_request_secured () {
    return (
        (isset($_SERVER['HTTPS'])                  && (strtolower($_SERVER['HTTPS'])          == 'on' || $_SERVER['HTTPS']          == 1)) ||
        (isset($_SERVER['HTTP_SSL_HTTPS'])         && (strtolower($_SERVER['HTTP_SSL_HTTPS']) == 'on' || $_SERVER['HTTP_SSL_HTTPS'] == 1)) ||
        (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) &&  strtolower($_SERVER['HTTP_X_FORWARDED_PROTO']) == 'https')
    );
}

/**
 * Simple wrapper for Limonade url_for () function
 *
 * @param string  $url
 * @param boolean $secured  Whether to use https or not
 * @return string
 */
function fz_url_for ($url, $secured = false) {
    if (fz_is_request_secured () && $secured === false)
        $url =  'http://'.$_SERVER["SERVER_NAME"].url_for ($url);
    else if (! fz_is_request_secured () && $secured === true)
        $url =  'https://'.$_SERVER["SERVER_NAME"].url_for ($url);

    return $url;
}

/**
 * Simple wrapper for Limonade redirect_to () function
 *
 * @param string  $url
 * @param boolean $secured  Whether to use https or not
 * @return string
 */
function fz_redirect_to ($url, $secured = false) {
    return redirect_to (fz_url_for ($url, $secured));
}

/**
 * If the request wasn't made with https. The user will be redirected to the
 * https one.
 *
 * @return void
 */
function fz_force_https () {
    if (fz_is_request_secured ())
        return;

    redirect_to ('https://'.$_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"]);
}

