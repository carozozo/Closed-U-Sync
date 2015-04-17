<?php

// Server limited
require_once('inc_server.php');

// Init owncloud
// require_once('../lib/base.php');

// 目前流量
$data_file = "/var/www/log/usage/net/r_net";

header('Content-type: text/plain');
header('Content-length: '.filesize($data_file));
$file = @fopen($data_file, 'rb');
if ($file)
{
    fpassthru($file);
    exit;
}
