<?php
/**
 * ownCloud - Thumbnail plugin
 *
 * @author Caro Huang
 * @copyright 2013 www.u-sync.com
 *
 * 縮圖資料庫處理
 */
class OC_Thumbnail_DB {

	/**
	 * 找出指定的資料夾底下一層的縮圖資料
	 * @param 使用者帳號,資料夾路徑
	 * @return array
	 */
	static function selectThumbDataInDir($localUserId, $localDirPath) {
		try {
			$path1 = OC_Helper::pathForbiddenChar($localDirPath . '/%');
			$path2 = OC_Helper::pathForbiddenChar($localDirPath . '/%/%');
			$thumbsInDB = array();
			$query = OC_DB::prepare("SELECT * FROM *PREFIX*files_thumbnail WHERE uid = ? AND path LIKE ? AND path NOT LIKE ?");
			$result = $query -> execute(array(
				$localUserId,
				$path1,
				$path2
			));
			while ($row = $result -> fetchRow()) {
				$thumbsInDB[] = array(
					"uid" => $row['uid'],
					"path" => $row['path'],
					"type" => $row['type'],
					"size" => $row['size'],
					"modifyTime" => $row['modifyTime']
				);
			}
			return $thumbsInDB;
		} catch(exception $e) {
			OC_Log::writeException('OC_Thumbnail_DB', 'selectThumbDataInDir', $e);
		}
	}

	/**
	 * 找出指定的資料夾底下所有的縮圖資料
	 * @param 使用者帳號,資料夾路徑
	 * @return array
	 */
	static function selectThumbDataUnderDir($localUserId, $localDirPath) {
		try {
			$path = OC_Helper::pathForbiddenChar($localDirPath . '/%');
			$thumbsInDB = array();
			$query = OC_DB::prepare("SELECT * FROM *PREFIX*files_thumbnail WHERE uid = ? AND path LIKE ?");
			$result = $query -> execute(array(
				$localUserId,
				$path
			));
			while ($row = $result -> fetchRow()) {
				$thumbsInDB[] = array(
					"uid" => $row['uid'],
					"path" => $row['path'],
					"type" => $row['type'],
					"size" => $row['size'],
					"modifyTime" => $row['modifyTime']
				);
			}
			return $thumbsInDB;
		} catch(exception $e) {
			OC_Log::writeException('OC_Thumbnail_DB', 'selectThumbDataUnderDir', $e);
		}
	}

	/**
	 * 找出指定的資料夾底下一層的子資料夾的縮圖資料
	 * @param 使用者帳號,資料夾路徑
	 * @return array
	 */
	static function selectThumbDataInSubDir($localUserId, $localDirPath) {
		try {
			$path1 = OC_Helper::pathForbiddenChar($localDirPath . '/%/%');
			$path2 = OC_Helper::pathForbiddenChar($localDirPath . '/%/%/%');
			$thumbsInDB = array();
			$query = OC_DB::prepare("SELECT * FROM *PREFIX*files_thumbnail WHERE uid = ? AND path != ? AND path LIKE ? AND path NOT LIKE ?");
			$result = $query -> execute(array(
				$localUserId,
				$localDirPath,
				$path1,
				$path2
			));
			while ($row = $result -> fetchRow()) {
				$thumbsInDB[] = array(
					"uid" => $row['uid'],
					"path" => $row['path'],
					"type" => $row['type'],
					"size" => $row['size'],
					"modifyTime" => $row['modifyTime']
				);
			}
			return $thumbsInDB;
		} catch(exception $e) {
			OC_Log::writeException('OC_Thumbnail_DB', 'selectThumbDataInSubDir', $e);
		}
	}

	/**
	 * 取得縮圖資 料
	 * @param 使用者帳號,縮圖路徑,縮圖檔案大小,縮圖更新時間
	 */
	static function selectThumbDataByDetail($localUserId, $localFilePath, $size, $modifyTime) {
		try {
			$localFilePath = OC_Helper::pathForbiddenChar($localFilePath);
			$query = OC_DB::prepare("SELECT * FROM *PREFIX*files_thumbnail WHERE uid = ? AND path = ? AND size = ? AND modifyTime = ?");
			$result = $query -> execute(array(
				$localUserId,
				$localFilePath,
				$size,
				$modifyTime
			));
			while ($row = $result -> fetchRow()) {
				return array(
					"uid" => $row['uid'],
					"path" => $row['path'],
					"type" => $row['type'],
					"size" => $row['size'],
					"modifyTime" => $row['modifyTime']
				);
			}
		} catch(exception $e) {
			OC_Log::writeException('OC_Thumbnail_DB', 'selectThumbDataByDetail', $e);
		}
	}

	/**
	 * 透過檔案路徑取得縮圖資料
	 * @param 使用者帳號,縮圖路徑
	 * @return array
	 */
	static function selectThumbDataByPath($localUserId, $localFilePath) {
		try {
			$localFilePath = OC_Helper::pathForbiddenChar($localFilePath);
			$query = OC_DB::prepare("SELECT * FROM *PREFIX*files_thumbnail WHERE uid = ? AND path = ?");
			$result = $query -> execute(array(
				$localUserId,
				$localFilePath
			));
			while ($row = $result -> fetchRow()) {
				return array(
					"uid" => $row['uid'],
					"path" => $row['path'],
					"type" => $row['type'],
					"size" => $row['size'],
					"modifyTime" => $row['modifyTime']
				);
			}
		} catch(exception $e) {
			OC_Log::writeException('OC_Thumbnail_DB', 'selectThumbDataByPath', $e);
		}
	}

	/**
	 * 寫入or更新縮圖資料
	 * @param 使用者帳號,檔案路徑,檔案類型,檔案大小,更新時間,寫入/更新
	 * @return bool
	 */
	static function inserOrUpdateToDB($localUserId, $localFilePath, $type, $size, $modifyTime, $dbAction) {
		try {
			$localFilePath = OC_Helper::pathForbiddenChar($localFilePath);
			if ($dbAction == "insert") {
				self::newThumbData($localUserId, $localFilePath, $type, $size, $modifyTime);
			} else if ($dbAction == "update") {
				self::updateThumbData($localUserId, $localFilePath, $type, $size, $modifyTime);
			}
			return true;
		} catch(exception $e) {
			OC_Log::writeException('OC_Thumbnail_DB', 'inserOrUpdateToDB', $e);
		}
	}

	/**
	 * 新增縮圖資料
	 * @param 使用者帳號,檔案路徑,檔案類型,檔案大小,更新時間,寫入/更新
	 * @return bool
	 */
	static function newThumbData($localUserId, $localFilePath, $type, $size, $modifyTime) {
		try {
			$localFilePath = OC_Helper::pathForbiddenChar($localFilePath);
			$query = OC_DB::prepare("INSERT INTO *PREFIX*files_thumbnail (uid, path, type, size, modifyTime) VALUES (?, ?, ?, ?, ?)");
			$result = $query -> execute(array(
				$localUserId,
				$localFilePath,
				$type,
				$size,
				$modifyTime
			));
			return true;
		} catch(exception $e) {
			OC_Log::writeException('OC_Thumbnail_DB', 'newThumbData', $e);
		}
	}

	/**
	 * 更新縮圖資料
	 * @param 使用者帳號,檔案路徑,檔案類型,檔案大小,更新時間,寫入/更新
	 * @return bool
	 */
	static function updateThumbData($localUserId, $localFilePath, $type, $size, $modifyTime) {
		try {
			$localFilePath = OC_Helper::pathForbiddenChar($localFilePath);
			$query = OC_DB::prepare("UPDATE *PREFIX*files_thumbnail SET type = ?, size = ?, modifyTime = ? WHERE uid = ? AND path = ?");
			$result = $query -> execute(array(
				$type,
				$size,
				$modifyTime,
				$localUserId,
				$localFilePath
			));
			return true;
		} catch(exception $e) {
			OC_Log::writeException('OC_Thumbnail_DB', 'updateThumbData', $e);
		}
	}

	/**
	 * 刪除指定的檔案
	 * @param 使用者帳號,檔案路徑
	 * @return bool
	 */
	static function deleteThumbData($localUserId, $localFilePath) {
		try {
			$localFilePath = OC_Helper::pathForbiddenChar($localFilePath);
			$query = OC_DB::prepare("DELETE FROM *PREFIX*files_thumbnail WHERE uid = ? AND path = ?");
			$result = $query -> execute(array(
				$localUserId,
				$localFilePath
			));
			return true;
		} catch(exception $e) {
			OC_Log::writeException('OC_Thumbnail_DB', 'deleteThumbData', $e);
		}
	}

	/**
	 * 刪除指定資料夾底下的所有縮圖資料
	 * @param 使用者帳號,資料夾路徑
	 * @return bool
	 */
	static function deleteThumbDataByDir($localUserId, $localDirPath) {
		try {
			$localDirPath = OC_Helper::pathForbiddenChar($localDirPath);
			$query = OC_DB::prepare("DELETE FROM *PREFIX*files_thumbnail WHERE uid = ? AND path LIKE ?");
			$result = $query -> execute(array(
				$localUserId,
				$localDirPath . '/%'
			));
			return true;
		} catch(exception $e) {
			OC_Log::writeException('OC_Thumbnail_DB', 'deleteThumbDataByDir', $e);
		}
	}

}