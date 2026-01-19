<?php

set_time_limit(0);

set_include_path(
    get_include_path() . PATH_SEPARATOR . '/usr/www/users/kraibn/'
);

$_GET["url"] = "admin/tpepdb2/import";

$_GET["auth_key"] = md5("!KRAIBURG TPE!");

$_GET['set_root_path'] = 1;

require_once "index.php";

?>
