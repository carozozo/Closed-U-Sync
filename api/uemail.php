<?php

if (isset($_REQUEST['username']))
{
    $username=$_REQUEST['username'];
}
else
{
    exit;
}

$RUNTIME_NOAPPS = FALSE; // do not load apps
require_once "../lib/base.php";

echo OC_User::getUserEmail($username);