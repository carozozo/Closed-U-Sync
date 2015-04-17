<?php
if (!isset($_REQUEST['username'])) {
	exit ;
}

$username = $_REQUEST['username'];

require_once ('../lib/base.php');

// if user input email as id
if (strpos($username, '@')) {
	$username = OC_User::getUserIdByEmail($username);
}

function getDirectorySize($path) {
	$totalsize = 0;
	$totalcount = 0;
	$dircount = 0;
	if ($handle = opendir($path)) {
		while (false !== ($file = readdir($handle))) {
			$nextpath = $path . '/' . $file;
			if ($file != '.' && $file != '..' && !is_link($nextpath)) {
				if (is_dir($nextpath)) {
					$dircount++;
					$result = getDirectorySize($nextpath);
					$totalsize += $result['size'];
					$totalcount += $result['count'];
					$dircount += $result['dircount'];
				} elseif (is_file($nextpath)) {
					$totalsize += filesize($nextpath);
					$totalcount++;
				}
			}
		}
	}
	closedir($handle);
	$total['size'] = $totalsize;
	$total['count'] = $totalcount;
	$total['dircount'] = $dircount;
	return $total;
}

$user_path = getenv("DOCUMENT_ROOT") . '/data/' . strtolower($username);

$used = getDirectorySize($user_path);
$used['quota'] = OC_UserQuota::getUserQuota($username);
if (is_null($used['quota']))
	$used['quota'] = 0;

echo json_encode($used);
