<?php
// Server limited
require_once ('inc_server.php');
// Init owncloud
require_once ('../lib/base.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	$postText = rawurldecode(file_get_contents('php://input'));
} else {
	exit ;
}
$data = json_decode($postText, 1);

$action = $data['action'];
$userId = $data['userId'];
$dir = $data['dir'];
$fileName = $data['fileName'];

header('Content-Type: application/json; charset=utf-8');

switch($action) {
	case 'getGroupSharedSourcePath' :
		if ($userId && $dir && $fileName) {
			$webDavPath = OC_Helper::getProtocol() . $_SERVER["HTTP_HOST"] . '/dav/webdav.php';
			$dir = preg_replace('#' . $webDavPath . '#', '', $dir, 1);
			$groupSharedDirIndex = strpos($dir, '/' . OC_GroupShare::groupSharedDir());
			if ($groupSharedDirIndex === 0) {
				$positions = array();
				$pos = -1;
				while (($pos = strpos($dir, '/', $pos + 1)) !== FALSE) {
					// OC_Log::write('media_getway', '$pos=' . $pos, 1);
					$positions[] = $pos;
				}
				//get the first dir name under Group Shared folder
				if (count($positions) >= 3) {
					//$dir would like [/Groupshared/aaa/]
					$groupSharedFirstDir = substr($dir, $positions[1], $positions[2] - $positions[1]);
					$otherDir = substr($dir, $positions[2]);
					// OC_Log::write('media_getway', '$groupSharedFirstDir=' . $groupSharedFirstDir, 1);
					// OC_Log::write('media_getway', '$otherDir=' . $otherDir, 1);
					$target = '/' . $userId . '/files/' . OC_GroupShare::groupSharedDir() . $groupSharedFirstDir;
					// OC_Log::write('media_getway', '$target=' . $target, 1);
					$items = OC_GroupShare::getItem($target);
					$source = OC_GroupShare::getSourceByTarget($target);
					// if (!empty($items)) {
					if (!empty($source)) {
						$userId = substr($source, 1, strpos($source, '/', 1) - 1);
						$source = preg_replace('#/' . $userId . '/files#', '', $source);
						$source .= $otherDir . $fileName;
						$fileName = OC_Helper::getProtocol() . $_SERVER["HTTP_HOST"] . '/dav/mcdav.php' . $source;
						$fileName = urlencode($fileName);
						// OC_Log::write('media_getway', 'Success $userId=' . $userId . ', $fileName=' . $fileName, 1);
						echo json_encode(array(
							'status' => 'success',
							'userId' => $userId,
							'fileName' => $fileName
						));
					} else {
						// OC_Log::write('media_getway', 'Not get the owner id and source' , 1);
						echo json_encode(array(
							'status' => 'error',
							'message' => 'Not get the owner id and source'
						));
					}
				} else {
					// OC_Log::write('media_getway', 'dir is error' , 1);
					echo json_encode(array(
						'status' => 'error',
						'message' => 'dir is error'
					));
				}
			} else {
				// OC_Log::write('media_getway', 'Not get group shared folder name in dir' , 1);
				echo json_encode(array(
					'status' => 'error',
					'message' => 'Not get group shared folder name in dir'
				));
			}
		} else {
			// OC_Log::write('media_getway', 'No userId or dir or fileName' , 1);
			echo json_encode(array(
				'status' => 'error',
				'message' => 'No userId or dir or fileName'
			));
		}
		break;
	default :
		// OC_Log::write('media_getway', 'No Action Name' , 1);
		echo json_encode(array(
			'status' => 'error',
			'message' => 'No Action Name'
		));
		break;
}
?>