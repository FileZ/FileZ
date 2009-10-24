<?php 

function fz_helper_action_is_xhr_request () {
    return ($_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest'); 
}


