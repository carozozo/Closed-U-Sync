<?php
class OC_Thumbnail_Hooks {

	/**
	 * 上傳/新增檔案後，判斷產生縮圖
	 */
	static function createThumb($arguments) {
		$path = $arguments[OC_Filesystem::signal_param_path];
		$dir = dirname($path);
		$fileName = basename($path);
		$thumbnailObj = new OC_Thumbnail($dir);
		$extension = OC_Filesystem::getExtension($path);
		$mime = OC_Filesystem::getMimeType($path);
		if ($extension != 'usync' && $thumbnailObj -> ifNeedToCreateThumbs($mime)) {
			$file = array();
			$file['name'] = $fileName;
			$file['mime'] = $mime;
			$file['size'] = OC_Filesystem::filesize($path);
			$file['date'] = date('Y-m-d H:i:s');
			$thumbnailObj -> createAndGetThumbByFile($file);
		}
	}

	/**
	 * 刪除指定縮圖
	 */
	static function deleteThumb($arguments) {
		$path = $arguments[OC_Filesystem::signal_param_path];
		$fileType = OC_Filesystem::filetype($path);
		$dir = dirname($path);
		$fileName = basename($path);
		$thumbnailObj = new OC_Thumbnail($dir, $fileName);
		$thumbnailObj -> deleteThumb($fileType);
	}

	/**
	 * 更名及移動
	 */
	static function renameThumb($arguments) {
		$oldPath = $arguments[OC_Filesystem::signal_param_oldpath];
		$newPath = $arguments[OC_Filesystem::signal_param_newpath];
		$dir = pathinfo($newPath, PATHINFO_DIRNAME);
		# 帶入原始完整路徑
		self::renameOrCopyThumb($oldPath, $newPath, 'rename');
	}

	/**
	 * 複製縮圖
	 */
	static function copyThumb($arguments) {
		$oldPath = $arguments[OC_Filesystem::signal_param_oldpath];
		$newPath = $arguments[OC_Filesystem::signal_param_newpath];
		$dir = pathinfo($oldPath, PATHINFO_DIRNAME);
		self::renameOrCopyThumb($oldPath, $newPath, 'copy');
	}

	/**
	 * rename 或 copy 縮圖
	 * @param 舊路徑,新路徑,要執行的action
	 */
	static function renameOrCopyThumb($oldPath, $newPath, $action = 'copy') {

		$oldUserId = OC_LocalSystem::getLocalUserIdByPath($oldPath);
		$newUserId = OC_LocalSystem::getLocalUserIdByPath($newPath);
		$oldFullPath = OC_LocalSystem::getLocalFullPath($oldPath);
		$newFullPath = OC_LocalSystem::getLocalFullPath($newPath);

		$oldDataDir = OC_LocalSystem::getDataDirFullPathByUserId($oldUserId);
		$oldPath = preg_replace('#' . $oldDataDir . '#', '', $oldFullPath);

		$newDataDir = OC_LocalSystem::getDataDirFullPathByUserId($newUserId);
		$newPath = preg_replace('#' . $newDataDir . '#', '', $newFullPath);

		# 判斷依據 - copy目錄的話is_dir($oldFullPath)為true,rename的話則是is_dir($newFullPath)為true
		if (is_dir($oldFullPath) || is_dir($newFullPath)) {
			# 如果是資料夾，只需要處理縮圖DB
			$oldThumbsArray = OC_Thumbnail_DB::selectThumbDataUnderDir($oldUserId, $oldPath);
			foreach ($oldThumbsArray as $key => $thumb) {
				$path = $thumb['path'];
				# $oldPath和$newPath就相當於資料夾路徑
				$path = preg_replace('#' . $oldPath . '#', $newPath, $path);
				$type = $thumb['type'];
				$size = $thumb['size'];
				$modifyTime = date('Y-m-d H:i:s');

				$dbAction = OC_Thumbnail::checkInsertOrUpdate($newUserId, $path, $size, $modifyTime);
				OC_Thumbnail_DB::inserOrUpdateToDB($newUserId, $path, $type, $size, $modifyTime, $dbAction);
			}
			if ($action != 'copy') {
				OC_Thumbnail_DB::deleteThumbDataByDir($oldUserId, $oldPath);
			}

		} else {
			$oldDir = dirname($oldPath);
			$oldDir = ($oldDir != '/') ? $oldDir : '';
			$oldName = basename($oldPath);
			$newDir = dirname($newPath);
			$newDir = ($newDir != '/') ? $newDir : '';
			$newName = basename($newPath);

			$oldThumbPath = OC_Thumbnail_Handle::createThumbDir($oldDataDir . $oldDir) . '/' . $oldName . '.jpg';
			$newThumbPath = OC_Thumbnail_Handle::createThumbDir($newDataDir . $newDir) . '/' . $newName . '.jpg';
			if (file_exists($oldThumbPath)) {
				if ($action == 'copy') {
					exec('cp "' . $oldThumbPath . '" "' . $newThumbPath . '"');
				} else {
					exec('mv "' . $oldThumbPath . '" "' . $newThumbPath . '"');
				}

				# 將source的縮圖資料，存到destination的資料庫
				$thumb = OC_Thumbnail_DB::selectThumbDataByPath($oldUserId, $oldPath);
				if ($thumb) {
					$path = $thumb['path'];
					$path = preg_replace('#' . preg_quote($oldPath) . '#', $newPath, $path);
					$type = $thumb['type'];
					$size = $thumb['size'];
					$modifyTime = date('Y-m-d H:i:s');
					$dbAction = OC_Thumbnail::checkInsertOrUpdate($newUserId, $path, $size, $modifyTime);
					OC_Thumbnail_DB::inserOrUpdateToDB($newUserId, $path, $type, $size, $modifyTime, $dbAction);
					if ($action != 'copy') {
						OC_Thumbnail_DB::deleteThumbData($oldUserId, $oldPath);
					}
				}
			}
		}
	}

}
