<?php

// Server limited
require_once('inc_server.php');

// Init owncloud
// require_once('../lib/base.php');

if( isset($_REQUEST["date"]) )
{
    // 每日流量
    $data_file = "/var/www/log/usage/net/daily_net_".$_REQUEST["date"];

    header('Content-type: text/plain');
    header('Content-length: '.filesize($data_file));
    $file = @fopen($data_file, 'rb');
    if ($file)
    {
        fpassthru($file);
        exit;
    }
}