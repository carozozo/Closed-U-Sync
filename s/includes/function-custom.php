<?php

$_REQUEST['username'] = 'admin';
$_REQUEST['password'] = 'admin';
$_REQUEST['format'] = 'simple';
$_REQUEST['action'] = 'shorturl';

/*
 * DB connection
 */

$dbname = 'yourls';
$dbuser = 'yourls';
$dbpass = 'yourls';
$dbhost = $_SERVER['SERVER_NAME'];

$link = mysql_connect($dbhost, $dbuser, $dbpass);
if(!$link)
{
	die('Could not connect: ' . mysql_error());
}

$sel = mysql_select_db($dbname, $link);
if(!$sel)
{
	die('Can\'t use db: ' . mysql_error());
}

/*
 * Ascii　亂數產生器
 */
function get_rand($num, $type)
{
	$str_n = '0123456789';
	$str_e = 'abcdefghijklmnopqrstuvwxyz';
	$str_E = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';

	$result = '';

	switch ($type) {

		// 數字
		case 1:
			$charset = $str_n;
			break;

		// 英文小寫
		case 2:
			$charset = $str_e;
			break;

		// 英文大寫
		case 3:
			$charset = $str_E;
			break;

		// 數字+英文大小寫
		case 4:
			$charset = $str_n . $str_e . $str_E;
			break;

		// 英文大小寫
		case 5:
			$charset = $str_e . $str_E;
			break;

		// 數字+英文小寫
		case 6:
			$charset = $str_n . $str_e;
			break;
	}

	$len_charset = strlen($charset);

	for ($j = 0; $j < $num; $j++)
	{
		$result .= substr($charset, rand(0, ($len_charset - 1) ), 1);
	}

	return $result;
}

/*
 * Keyword 產生器	keyword的長度為 (開頭字+位數)
 *
 * @param $pre_fix	開頭字
 * @param $num		位數
 * @param $type		keyword 的類型
 */
function get_keyword($pre_fix, $num, $type)
{
	do {
		$keyword = $pre_fix . get_rand($num, $type); // 設定短網址 Keyword 的規則
		$sql = "select count(1) cnt  from yourls_url where keyword='".$keyword."'";
		$dataset = mysql_query($sql);
		list($cnt) = mysql_fetch_array($dataset);
	} while ($cnt == 1);

	return $keyword;
}


function get_urlpath($url) {
	$l = strlen($url);
	$pos = strrpos($url, '/');
	$path = substr($url, 0, $pos);

	return $path;
}

function get_urlfilename($url) {
	$l = strlen($url);
	$pos = strrpos($url, '/');
	$filename = substr($url, ($pos+1), ($l-$pos)+1);

	return $filename;
}

