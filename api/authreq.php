<?php
require_once "inc_db.php";

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

$counts = rand(16,32);
$tokenprefix = get_rand($counts, 6);

$sql = "delete from $prefix"."tokenlogin where TIME_TO_SEC(TIMEDIFF(now(), createtime)) > 30";
$dataset = mysql_query($sql);

$sql = "insert into $prefix"."tokenlogin (tokenprefix) values ('$tokenprefix')";
mysql_query($sql);

echo $tokenprefix;
