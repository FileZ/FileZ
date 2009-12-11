<?php

/**
 * Check if the current user is authenticated and forward
 * to a login action if not.
 *
 * TODO handle credentials
 *
 * @param string  $credential
 */
function fz_secure ($credential) {
    $user = fz_get_current_user ();
    if ($user->isAuthenticated ())
        return true;

    $auth_class = fz_config_get ('auth', 'handler_class', 'fzAuthenticationCas');
    call_user_func_array (array ($auth_class, 'secure'));
}
