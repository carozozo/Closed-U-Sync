<?php
// 用法 http://cloud.u-sync.com/s/api_datashare.php?url=http://www.google.com

require_once( dirname(__FILE__).'/includes/function-custom.php' );

// 檔名 urlencode
//$_REQUEST['url'] = get_urlpath($_REQUEST['url']) . '/' . urlencode( get_urlfilename($_REQUEST['url']) );

// 設定 keyword 規則
$_REQUEST['keyword'] = get_keyword('', 8, 4);

$_REQUEST['title'] = 'Data Share';

mysql_close($link);

require_once( dirname(__FILE__).'/yourls-api.php' );