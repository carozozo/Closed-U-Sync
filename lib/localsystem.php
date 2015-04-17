<?php

/**
 * ownCloud
 *
 * @author Caro Huang
 * @copyright 2013 caro@u-sync.com
 *
 * 取得檔案真正所屬的path及user相關函式
 */
class OC_LocalSystem extends OC_Filesystem {
	/**
	 * 取得檔案真正存放的路徑
	 * @param string path
	 * @return string
	 */
	static public function getLocalPath($path) {
		$localUserId = self::getLocalUserIdByPath($path);
		$localPath = self::getLocalFullPath($path);
		$userDataDir = OC_LocalSystem::getDataDirFullPathByUserId($localUserId);
		return $path = substr($localPath, strlen($userDataDir));
	}

	/**
	 * return the path to a local version of the file
	 * we need this because we can't know if a file is stored local or not from outside the filestorage and for some purposes a local file is needed
	 * @param string path
	 * @return string
	 */
	static public function getLocalFullPath($path) {
		$parent = substr($path, 0, strrpos($path, '/'));
		if (self::isValidPath($parent) and $storage = self::getStorage($path)) {
			return $storage -> getLocalFullPath(self::getInternalPath($path));
		}
	}

	/**
	 * 透過路徑取得檔案真正存放在哪個user id下
	 * @param string path
	 * @return string
	 */
	static public function getLocalUserIdByPath($path) {
		$parent = substr($path, 0, strrpos($path, '/'));
		if (self::isValidPath($parent) and $storage = self::getStorage($path)) {
			return $storage -> getLocalUserIdByPath(self::getInternalPath($path));
		}
	}

	/**
	 * 輸入user id,取得user id資料根目錄完整路徑
	 * @param string $path
	 * @return string
	 */
	static function getDataDirFullPathByUserId($userId = NULL) {
		$userId = OC_User::getUserByUserInput($userId);
		if ($userId) {
			$dataDirFullPath = OC::$CONFIG_DATADIRECTORY_ROOT . '/' . $userId . '/' . OC::$USER_DATA_FOLDER;
			return $dataDirFullPath = OC_Helper::pathForbiddenChar($dataDirFullPath);
		}
	}

	/**
	 * 輸入user id 及檔案路徑,取得完整的檔案路徑
	 * @param string $path
	 * @return string
	 */
	static function getFullPathByUserId($userId, $path) {
		$userDataDir = self::getDataDirFullPathByUserId($userId);
		if ($userDataDir)
			return OC_Helper::pathForbiddenChar($userDataDir . '/' . $path);
	}

}
