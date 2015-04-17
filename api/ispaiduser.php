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

if (OC_User::userExists($username)) {
    $isPaidUser = OC_User::isPaidUser($username);
} else {
    echo OC_JSON::error(array('username'=>"$username", 'ispaid'=>FALSE));
    exit;
}

if (!$isPaidUser) {
    echo OC_JSON::success(array('username'=>"$username", 'ispaid'=>FALSE));
} else {
	echo OC_JSON::success(array('username'=>"$username", 'ispaid'=>($isPaidUser===true) ? '1' : $isPaidUser));
}