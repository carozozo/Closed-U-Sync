<?php

// Init owncloud
require_once('../../lib/base.php');

OC_JSON::checkLoggedIn();

$l=new OC_L10N('core');

// Get data
if( isset( $_POST['nickname'] ) ){
	$nickname=trim($_POST['nickname']);
	OC_Preferences::setValue(OC_User::getUser(),'settings','nickname',$nickname);
	OC_JSON::success(array("data" => array( "message" => $l->t("Nickname changed") )));
}else{
	OC_JSON::error(array("data" => array( "message" => $l->t("Invalid request") )));
}

?>
