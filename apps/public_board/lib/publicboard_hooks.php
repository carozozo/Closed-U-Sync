<?php
class OC_PublicBoard_Hooks {

	/**
	 * 使用者檔案根目錄的檔案總容量(排除public board)
	 */
	static public function filesizeWithoutPublicBoard($arguments) {
		$filePath = $arguments[OC_Filesystem::signal_param_path];
		if ($filePath == '' || $filePath == '/') {
			$fileSize = $arguments[OC_Filesystem::signal_param_result];
			$fileSize -= OC_PublicBoard::publicBoardSize();
			$arguments[OC_Filesystem::signal_param_result] = $fileSize;
		}
	}

	/**
	 * 取得使用者剩餘空間(排除public board)
	 */
	static public function freeSpaceWithoutPublicBoard($arguments) {
		$freeSpace = $arguments[OC_Filesystem::signal_param_result];
		$freeSpace += OC_PublicBoard::publicBoardSize();
		$arguments[OC_Filesystem::signal_param_result] = $freeSpace;
	}

}
