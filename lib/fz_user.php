<?php 

/**
 * Retrieve the current user
 *
 * @return fzUser
 */
function fz_get_current_user () {
    $auth_class = fz_config_get ('auth', 'auth_class', 'fzAuthenticationCas');
    return call_user_func_array (array ($auth_class, 'getCurrentUser'));
}

/**
 * Set the current user
 *
 * @param fzUser $uid
 * @return fzUser
 */
function fz_set_current_user ($user) {
    $auth_class = fz_config_get ('auth', 'auth_class', 'fzAuthenticationCas');
    return call_user_func_array (array ($auth_class, 'setCurrentUser'), array ($user));
}

/**
 * Retrieve a user by its uid
 *
 * @param mixed $uid
 * @return fzUser
 */
function fz_get_user ($uid) {
    $factory_class = fz_config_get ('auth', 'user_factory_class', 'fzUserFactoryLdap');
    return call_user_func_array (array ($factory_class, 'findByUid'), array ($uid));
}

