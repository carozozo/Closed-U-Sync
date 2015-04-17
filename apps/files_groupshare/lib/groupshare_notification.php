<?php
class OC_GroupShare_Notification {
	//發佈分享資料夾更名通知
	static function notificationByRenameSharedFolder($userId, $uidSharedWith, $oldFolderName, $newFolderName) {
		if (OC_APP::isEnabled('notification')) {
			//多國語系的內容用<>包住
			$message = $userId . ' <change the shared folder name> [' . $oldFolderName . '] -> [' . $newFolderName . ']';
			$link = '/files/index.php?dir=/' . OC_GroupShare::groupSharedDir();
			OC_Notification::addNotification('files_groupshare', 'Group Share', $uidSharedWith, $message, $link);
		}
	}

	//owner 變更分享資料夾下的內容變時，發佈通知
	static function notificationWhenContentUpdatedByOwner($path) {
		//如果Notification APP有啟動
		if (OC_App::isEnabled('notification')) {
			$items = OC_GroupShare::getItemsByUidOwner();
			// $items = OC_GroupShare_Manager::getGroupShareManagerList();
			foreach ($items as $key => $item) {
				//如果被分享者允許被分享
				if ($item['accept']) {
					//找出分享出去的資料夾原紿路徑(將/xxxx/files/yyyy轉成/yyyy)
					$source = $item['source'];
					$source = preg_replace('#\/\w+\/files#', '', $source);
					// OC_Log::write('checkIfNotification', '$path=' . $path . ', $source=' . $source, 1);
					//如果上傳/移動/更名/複製的路徑是在分享出去的資料夾路徑底下
					if (strpos($path, $source) === 0 && $path != $source) {
						//發送訊息給被分享者
						$userId = OC_User::getUser();
						// OC_Log::write('checkIfNotification', '$userId=' .$userId, 1);
						$uidSharedWith = $item['uid_shared_with'];
						$folderName = basename($source);
						$targetFolder = basename($item['target']);

						//多國語系的內容用<>包住
						$message = $userId . ' <update the data in share folder> ' . ' [' . $folderName . ']';
						$link = '/files/index.php?dir=/' . OC_GroupShare::groupSharedDir() . '/' . $targetFolder;
						OC_Notification::addNotification('files_groupshare', 'Group Share', $uidSharedWith, $message, $link);
					}

				}
			}
		}
	}

	/**
	 * shared user 變更分享資料夾下的內容變時，發佈通知
	 * @param 變更(上傳/新增/更名/移動/複製)後的路徑
	 */
	static function notificationWhenContentUpdatedBySharedUser($path) {
		//如果Notification APP有啟動
		if (OC_App::isEnabled('notification')) {
			$uidOwner = '';
			$items = OC_GroupShare::getGroupShareByUidSharedWith();
			foreach ($items as $key => $item) {
				$source = $item['source'];
				$target = $item['target'];
				$shortSource = preg_replace('#\/\w+\/files#', '', $source);
				$target = preg_replace('#\/\w+\/files#', '', $target);

				//如果路徑剛好是在被分享資料夾底下
				if (strpos($path, $target) === 0) {
					//發送訊息給owner
					$userId = OC_User::getUser();
					// OC_Log::write('notificationWhenContentUpdatedBySharedUser', '$userId=' . $userId, 1);
					$uidOwner = $item['uid_owner'];
					$folderName = basename($shortSource);
					//多國語系的內容用<>包住
					$message = $userId . ' <update the data in share folder> ' . ' [' . $folderName . ']';
					$link = '/files/index.php?dir=' . $shortSource;
					OC_Notification::addNotification('files_groupshare', 'Group Share', $uidOwner, $message, $link);

					//發送訊息給其它被分享者
					self::notificationToOtherSharedUser($userId, $uidOwner, $source);
				}
			}
		}
	}

	/**
	 * 被分享者更改被分享資料夾的內容時，通知其它被分享者
	 * @param 被分享者 ,分享者,source路徑(分享者id/files/分享資料夾name)
	 */
	private static function notificationToOtherSharedUser($userId, $uidOwner, $source) {
		$items = OC_GroupShare::getItemsBySourceAndUidOwner($source, $uidOwner);
		foreach ($items as $key => $item) {
			$uidSharedWith = $item['uid_shared_with'];
			//如果被分享者不是自己
			if ($userId != $uidSharedWith) {
				$source = $item['source'];
				$target = $item['target'];
				$shortSource = preg_replace('#\/\w+\/files#', '', $source);
				$target = preg_replace('#\/\w+\/files#', '', $target);
				$folderName = basename($shortSource);
				$targetFolder = basename($target);
				$message = $userId . ' <update the data in share folder> ' . ' [' . $folderName . ']';
				$link = '/files/index.php?dir=/' . OC_GroupShare::groupSharedDir() . '/' . $targetFolder;
				OC_Notification::addNotification('files_groupshare', 'Group Share', $uidSharedWith, $message, $link);
			}
		}
	}

}
?>
