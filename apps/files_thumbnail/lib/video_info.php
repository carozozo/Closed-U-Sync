<?php
class OC_Video_Info {

	/**
	 * 取得影片要截取的時間點
	 * @param 影片完整路徑
	 */
	public static function getCutTime($srcFullPath) {
		# 取得影片總時間
		$duration = self::getVideoDuration($srcFullPath);
		list($hours, $mins, $secs) = preg_split('/[:]/', $duration);
		# 截取總秒數的1/2
		return $secondsAll = floor(($hours * 60 * 60 + $mins * 60 + $secs) / 2);
	}

	/**
	 * 取得影片長度
	 * @param 影片完整路徑
	 */
	public static function getVideoDuration($srcFullPath) {
		ob_start();
		passthru("/usr/bin/ffmpeg -i \"{$srcFullPath}\" 2>&1");
		$duration = ob_get_contents();
		ob_end_clean();

		$search = '/Duration: (.*?),/';
		$duration = preg_match($search, $duration, $matches, PREG_OFFSET_CAPTURE, 3);
		return $duration = $matches[1][0];
	}

}
