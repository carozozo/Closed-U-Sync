<?php
/**
 * ownCloud - Thumbnail plugin
 *
 * @author Caro Huang
 * @copyright 2013 www.u-sync.com
 *
 * 產生圖檔或影片檔的縮圖，並顯示於 Web/Device 頁面中
 */
class OC_Thumbnail {

	const appId = 'files_thumbnail';
	const thumbsDir = '.thumbs';

	# 目前只有 OC_Thumbnail_Sync 有用到$dirPath
	protected static $dirPath;
	protected static $localUserId;
	protected static $localDirFullPath;
	protected static $localDirPath;
	protected static $localFileFullPath;
	protected static $localFilePath;
	protected static $localFileName;

	function __construct($dirPath, $fileName = null) {
		self::$dirPath = $dirPath;
		self::$localUserId = OC_LocalSystem::getLocalUserIdByPath($dirPath);
		self::$localDirFullPath = OC_LocalSystem::getLocalFullPath($dirPath);
		self::$localDirPath = OC_LocalSystem::getLocalPath($dirPath);
		if ($fileName) {
			$filePath = OC_Helper::pathForbiddenChar($dirPath . '/' . $fileName);
			self::$localFileFullPath = OC_LocalSystem::getLocalFullPath($filePath);
			self::$localFilePath = OC_LocalSystem::getLocalPath($filePath);
			self::$localFileName = basename(self::$localFilePath);
		}
	}

	/**
	 * (在web dav中)隱藏縮圖資料夾
	 */
	static function hideThumbsDir() {
		OC_Connector_Sabre_Directory::addHideFileName(self::thumbsDir);
	}

	/**
	 * 取得資料夾內的縮圖資料
	 * @return array
	 */
	static function getThumbsInDir() {
		$thumbsInDB = OC_Thumbnail_DB::selectThumbDataInDir(self::$localUserId, self::$localDirPath);
		$thumbUrlArray = array();
		foreach ($thumbsInDB as $thumbs) {
			$localFilePath = $thumbs['path'];
			$localFileName = basename($localFilePath);
			$thumbPath = self::getThumbPath(self::$localDirPath, $localFileName);
			if (OC_Filesystem::file_exists($thumbPath)) {
				$thumbUrlArray[] = array(
					'name' => $localFileName,
					'url' => self::setThumbURL($localFileName)
				);
			} else {
				# DB有資料，但縮圖不存在，所以刪除DB資料
				OC_Thumbnail_DB::deleteThumbData(self::$localUserId, $localFilePath);
			}
		}
		return $thumbUrlArray;
	}

	/**
	 * 產生並取得縮圖
	 * @return array
	 */
	static function createAndGetThumbByFile($file) {
		$mime = $file['mime'];
		if (self::ifNeedToCreateThumbs($mime) && self::createThumb($file)) {
			return array(
				'name' => self::$localFileName,
				'url' => self::setThumbURL(self::$localFileName)
			);
		}
		return array(
			'name' => self::$localFileName,
			'url' => OC_Helper::mimetypeIcon($mime)
		);
	}

	/**
	 * 產生縮圖
	 * @param 檔案資料(array)
	 * @return bool
	 */
	static function createThumb($file) {
		$fullPath = self::$localDirFullPath . "/" . self::$localFileName;
		# 如果檔案存在，而且DB沒有資料，則產生縮圖
		if (file_exists($fullPath) && !self::getThumbDataByFile($file)) {
			$thumbHandler = new OC_Thumbnail_Handle(self::$localUserId, self::$localDirPath);
			return $thumbHandler -> thumbFromImgOrVideo(self::$localDirPath, $file);
		}
		return false;
	}

	/**
	 * 取得縮圖資料
	 * @param 檔案資料(array)
	 * @return bool
	 */
	static function getThumbDataByFile($file) {
		$size = $file['size'];
		$date = $file['date'];
		$getThumbByDB = OC_Thumbnail_DB::selectThumbDataByDetail(self::$localUserId, self::$localFilePath, $size, $date);
		# 找到縮圖資料
		if ($getThumbByDB && count($getThumbByDB) > 0) {
			# 有縮圖資料，回傳秀圖的url
			return self::setThumbURL(self::$localFileName);
		}
		return false;
	}

	/**
	 * 確認檔案是否需要做成縮圖,這邊的$mime是透過FileSystem轉譯過的
	 * @param mime type
	 * @return bool
	 */
	static function ifNeedToCreateThumbs($mime) {
		return OC_Thumbnail_Handle::checkIfImage($mime) || OC_Thumbnail_Handle::checkIfVideo($mime);
	}

	/**
	 * 設置縮圖的url
	 * @param 檔案名稱
	 * @return string
	 */
	private static function setThumbURL($fileName = '') {
		if ($fileName) {
			$thumbsURL = OC_Helper::linkTo(self::appId, "showthumbnail.php");
			$thumbsURL .= '?userId=' . self::$localUserId;
			$thumbsURL .= '&dir=' . urlencode(self::$localDirPath) . '&file=' . urlencode($fileName);
			return $thumbsURL;
		}
	}

	/**
	 * 刪除指定縮圖
	 * @param 檔案名稱,檔案類型
	 * @return bool
	 */
	static function deleteThumb($fileType = 'dir') {
		$localFilePath = self::$localDirPath . '/' . self::$localFileName;
		if ($fileType == 'dir') {
			# 刪除的是資料夾
			OC_Thumbnail_DB::deleteThumbDataByDir(self::$localUserId, self::$localFilePath);
		} else {
			# 刪除的是圖檔，則找出縮圖路徑
			$thumbnailHandle = new OC_Thumbnail_Handle(self::$localUserId, self::$localDirPath);
			$thumbFilePath = $thumbnailHandle::getThumbDirFullPath() . '/' . self::$localFileName . '.jpg';
			if (file_exists($thumbFilePath)) {
				# 刪除縮圖和DB裡的資料
				@unlink($thumbFilePath);
				OC_Thumbnail_DB::deleteThumbData(self::$localUserId, self::$localFilePath);
			}
		}
	}

	/**
	 * 判斷該縮圖是要新增/更新到DB
	 * @param 使用者帳號,檔案路徑,檔案大小,檔案更新時間
	 * @return string
	 */
	static function checkInsertOrUpdate($localUserId, $localFilePath, $size, $modifyTime) {
		# 和DB中同path的資料比對
		$result = OC_Thumbnail_DB::selectThumbDataByPath($localUserId, $localFilePath);
		if (!$result || sizeof($result) == 0) {
			return "insert";
		} else if ($result['size'] != $size || $result['modifyTime'] != $modifyTime) {
			# 如果file的size不一樣，或是修改時間和DB的不同
			return "update";
		}
		return "";
	}

	/**
	 * 取得縮圖的路徑
	 * @param 資料夾路徑,檔案名稱
	 * @return string
	 */
	private static function getThumbPath($dirPath, $fileName) {
		$thumbsDir = self::thumbsDir;
		return $dirPath . '/' . $thumbsDir . '/' . $fileName . '.jpg';
	}

}
