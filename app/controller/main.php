<?php

function fz_action_main_index () {
    set ('upload_id', md5(uniqid(mt_rand(), true)));
    set ('start_from', Zend_Date::now ()->get (Zend_Date::DATE_SHORT));
    return html ('main/index.php');
}
