<?php

/**
 * XXX 公佈欄連結
 *
 */
class OC_PublicBoard {

	/**
	 * 產生公佈欄連結(未來規劃可能轉APP方式,或直接用groupshare取代)
	 * @author Caro Huang
	 */
	static function createPublicBoardLink() {
		$publicBoard = OC_Config::getValue("publicBoard", null, 'CONFIG_CUSTOM');
		if ($publicBoard && is_array($publicBoard) && count($publicBoard) > 0) {
			foreach ($publicBoard as $publicBoardName => $publicBoardPath) {
				$publicBoardExists = file_exists($publicBoardPath);
				# 如果公佈欄Folder存在,則產生該link
				if ($publicBoardExists && !is_link(OC::$CONFIG_DATADIRECTORY . '/' . $publicBoardName) and $publicBoardName != '') {
					symlink($publicBoardPath, OC::$CONFIG_DATADIRECTORY . '/' . $publicBoardName);
				}
			}
		}
	}

	/**
	 * Get the soft link public Board total size
	 *
	 */
	static public function publicBoardSize() {
		$publicBoard = OC_Config::getValue("publicBoard", "", 'CONFIG_CUSTOM');
		$publicBoardSize = 0;
		if ($publicBoard <> "") {
			foreach ($publicBoard as $key => $value) {
				if (is_link(OC::$CONFIG_DATADIRECTORY . '/' . $key)) {
					$linkFullPath = OC_LocalSystem::getLocalFullPath('/'.$key);
					# 不可使用OC_Filesystem::filesize(), 因為OC_PublicBoard_Hooks有使用這支function, 會造成無限迴圈
					// $publicBoardSize += OC_Filesystem::filesize('/' . $key);
					$publicBoardSize += filesize($linkFullPath);
				}
			}
		}
		return $publicBoardSize;
	}

}
