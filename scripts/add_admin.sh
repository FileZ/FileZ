#!/usr/bin/php
<?php

if ($argc != 2) {
    echo "ERROR : You have to specify a username\n";
    exit(1);
}

define ('FZ_ROOT', dirname (__FILE__).'/../');

require_once FZ_ROOT.'lib/limonade.php';
require_once FZ_ROOT.'lib/fz_config.php';

fz_config_load (FZ_ROOT.'config/');

$sql = "UPDATE fz_user set is_admin=1 where username='".$argv[1]."'";

$db = new PDO (fz_config_get ('db', 'dsn'), fz_config_get ('db', 'user'), fz_config_get ('db', 'password'));
$rows = $db->exec ($sql);

echo $rows." user updated\n";
