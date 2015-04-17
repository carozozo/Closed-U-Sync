<?php
//Note: sync can not work in https(ssl), so can not include OwnCloud Class [/lib/base.php]
require_once ($_SERVER['DOCUMENT_ROOT'] . '/config/config.php');
require_once "inc_db.php";
require_once "inc_func.php";

$sql = "select configvalue from ".$prefix."appconfig where appId = 'u_drive' and configkey = 'dataDir' limit 1";
$dataset = mysql_query($sql);
list($dataDir) = mysql_fetch_array($dataset);

echo $dataDir;
