<?php

if (isset($_REQUEST['username']) and isset($_REQUEST['email']))
{
    $email=$_REQUEST['email'];
    $username=$_REQUEST['username'];
}
else
{
    exit;
}

$RUNTIME_NOAPPS = FALSE; // do not load apps
require_once "../lib/base.php";

// if user not exist, not allow to insert email
$query = OC_DB::prepare('SELECT count(1) cnt FROM *PREFIX*users WHERE uid= ?');
$result = $query -> execute(array($username));
$row = $result -> fetchRow();
if ($row)
{
    $cnt = $row["cnt"];
}

if ($cnt==1)
{
    OC_Preferences::setValue($username, 'settings', 'email', $email);
}
else
{
	$result=-1;
}

if ($result==1)
{
    echo 1;
}
else
{
    echo -1;    
}
