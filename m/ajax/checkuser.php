<?php
if (!($username=$_REQUEST['username']))
{
    exit;
}

$RUNTIME_NOAPPS = true; // do not load apps
require_once "../../api/inc_db.php";
//[lib/base.php] must be last required
require_once('../../lib/base.php');

$valid = false;

if (!OC_Joomla::getUserInfo($username))
{
    $valid = true;
}

mysql_close($link);

echo json_encode($valid);
