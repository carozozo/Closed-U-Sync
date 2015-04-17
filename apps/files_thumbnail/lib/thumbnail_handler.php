<?php
/**
 * ownCloud - Thumbnail plugin
 *
 * @author Caro Huang
 * @copyright 2013 www.u-sync.com
 *
 * 產生圖檔或影片檔的縮圖
 */
class OC_Thumbnail_Handle {

	# 這裡的userId 為 縮圖擁有者的id
	private static $localUserId;
	# 圖檔的真實資料夾路徑
	private static $localDirFullPath;
	# 縮圖完整路徑
	private static $thumbsDirFullPath;

	function __construct($localUserId, $dirPath) {
		$dirPath = ($dirPath != '') ? $dirPath : '/';
		self::$localDirFullPath = OC_LocalSystem::getDataDirFullPathByUserId($localUserId) . $dirPath;
		self::$thumbsDirFullPath = self::createThumbDir(self::$localDirFullPath);
		self::$localUserId = $localUserId;
	}

	/**
	 * 產生存放縮圖的資料夾
	 * @param 資料夾完整路籼
	 */
	static function createThumbDir($localDirFullPath) {
		$thumbsDirFullPath = $localDirFullPath . '/' . OC_Thumbnail::thumbsDir;
		$thumbsDirFullPath = OC_Helper::pathForbiddenChar($thumbsDirFullPath);
		if (!file_exists($thumbsDirFullPath)) {
			@mkdir($thumbsDirFullPath);
			global $RUNTIME_ERROR;
			if ($RUNTIME_ERROR) {
				# 重設 $RUNTIME_ERROE 為 false
				$RUNTIME_ERROR = false;
				# 縮圖資料夾產生失敗，則 self::$thumbsDirFullPath 會等於 null
				return null;
			}
		}
		return $thumbsDirFullPath;
	}

	/**
	 * 取得縮圖資料夾完整路徑
	 */
	static function getThumbDirFullPath() {
		return self::$thumbsDirFullPath;
	}

	/**
	 * 產生縮圖
	 * @param 資料夾路徑,檔案(array)
	 */
	static function thumbFromImgOrVideo($dirPath, $file) {
		# 如果有縮圖資料夾的話
		if (self::$thumbsDirFullPath) {
			$name = $file['name'];
			$mime = $file['mime'];
			$size = $file['size'];
			$modifyTime = date('Y-m-d H:i:s');
			$filePath = OC_Helper::pathForbiddenChar($dirPath . '/' . $name);

			# 原始檔完整路徑
			$srcFullPath = self::$localDirFullPath . "/" . $name;
			# 如果檔案是可讀取
			if (is_readable($srcFullPath)) {
				# 要輸出的檔案完整路徑
				$outputFullPath = self::$thumbsDirFullPath . "/" . $name . ".jpg";
				if (self::checkIfImage($mime)) {
					# 如果比對之後需要新增或修改DB資料
					$dbAction = OC_Thumbnail::checkInsertOrUpdate(self::$localUserId, $filePath, $size, $modifyTime);
					if ($dbAction != "") {
						# 縮圖產生成功，而且也寫入db
						return self::createThumbFromImg($srcFullPath, $outputFullPath) && OC_Thumbnail_DB::inserOrUpdateToDB(self::$localUserId, $filePath, 'image', $size, $modifyTime, $dbAction);
					}
					# 不需要修改資料，代表原本就有縮圖
					return true;
				}

				if (self::checkIfVideo($mime)) {
					$dbAction = OC_Thumbnail::checkInsertOrUpdate(self::$localUserId, $filePath, $size, $modifyTime);
					if ($dbAction != "") {
						return self::createThumbFromVideo($srcFullPath, $outputFullPath) && OC_Thumbnail_DB::inserOrUpdateToDB(self::$localUserId, $filePath, 'video', $size, $modifyTime, $dbAction);
					}
				}
			}
		}
		return false;
	}

	/**
	 * 產生圖檔的縮圖
	 * @param 來源完整路徑,輸出完整路徑
	 * @return bool
	 */
	static function createThumbFromImg($srcFullPath, $outputFullPath) {
		$im = new Imagick($srcFullPath);
		# 裁切並縮放
		$im -> cropThumbnailImage(120, 120);
		# 轉為jpg格式
		$im -> setImageFormat("jpg");
		# 輸出
		$im -> writeImage($outputFullPath);
		$im -> destroy();
		return true;
	}

	/**
	 * 產生影片的縮圖
	 * @param 來源完整路徑,輸出完整路徑
	 * @return bool
	 */
	static function createThumbFromVideo($srcFullPath, $outputFullPath) {
		# ffmpeg 的執行路徑
		$ffmpegPath = "/usr/bin/ffmpeg";
		# 要截取的時間點(為秒)
		$cutTime = OC_Video_Info::getCutTime($srcFullPath);
		# 從影片產生縮圖(影片來源及輸出加上單引號，避免檔名有空白字元時無法執行)
		# -ss $cutTime為「要截取的時間點」參數，務必放在前面(否則截圖時間會變長)
		system($ffmpegPath . ' -ss ' . $cutTime . ' -i "' . $srcFullPath . '" -y -f image2 -t 0.001 -s 160x120  "' . $outputFullPath . '"', $retval);
		return true;
	}

	/**
	 * 判斷是否為圖檔
	 * @param 檔案類型
	 * @return bool
	 */
	static function checkIfImage($mime) {
		if (strrpos($mime, "image") > -1) {
			return true;
		}
		return false;
	}

	/**
	 * 判斷是否為影片檔
	 * @param 檔案類型
	 * @return bool
	 */
	static function checkIfVideo($mime) {
		if (strrpos($mime, "video") > -1 || strrpos($mime, "realmedia") > -1) {
			return true;
		}
		return false;
	}

}
