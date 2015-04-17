<?php
OC_Hook::connect("OC_Filesystem", OC_Filesystem::signal_post_create, "OC_U_Drive_Hooks", "post_createItem");
OC_Hook::connect("OC_Filesystem", OC_Filesystem::signal_post_write, "OC_U_Drive_Hooks", "post_writeItem");
OC_Hook::connect("OC_Filesystem", OC_Filesystem::signal_post_delete, "OC_U_Drive_Hooks", "post_deleteItem");
OC_Hook::connect("OC_Filesystem", OC_Filesystem::signal_rename, "OC_U_Drive_Hooks", "renameItem");
OC_Hook::connect("OC_Filesystem", OC_Filesystem::signal_post_rename, "OC_U_Drive_Hooks", "post_renameItem");
OC_Hook::connect("OC_Filesystem", OC_Filesystem::signal_post_copy, "OC_U_Drive_Hooks", "post_copyItem");

OC::$CLASSPATH['OC_U_Drive'] = "apps/u_drive/lib/u_drive.php";
OC::$CLASSPATH['OC_U_Drive_Hooks'] = "apps/u_drive/lib/u_drive_hooks.php";
OC::$CLASSPATH['OC_U_Drive_DB'] = "apps/u_drive/lib/u_drive_db.php";

$l = new OC_L10N('u_drive');
OC_App::register(array(
	"order" => 3,
	"id" => "u_drive",
	"name" => "U-drive"
));
OC_App::addNavigationEntry(array(
	"id" => "u_drive",
	"order" => 6,
	"href" => OC_Helper::linkTo("files", "index.php?dir=" . OC_U_Drive::getDataDir()),
	"icon" => OC_Helper::imagePath("core", "places/synced.png"),
	"name" => $l -> t("U-Drive Folder")
));

OC_U_Drive::createDataDir();
?>