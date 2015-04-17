<?php

class OC_U_Drive_DB {
	static function insertItem($uid, $filename, $type, $size, $md5, $lastModified) {
		try {
			// OC_Log::write('OC_U_Drive_DB', 'insertItem', 1);
			$queryStr = 'INSERT INTO *PREFIX*fs (`uid`,`filename`,`type`,`size`,`md5`,`lastModified`) VALUES (?,?,?,?,?,?)';
			$query = OC_DB::prepare($queryStr);
			$query -> execute(array($uid, $filename, $type, $size, $md5, $lastModified));
			return TRUE;
		} catch(exception $e) {
			self::updateItem($type, $size, $md5, $lastModified, $uid, $filename);
			// OC_Log::writeException('OC_U_Drive_DB', 'insertItem', $e);
			return FALSE;
		}
	}

	static function updateItem($type, $size, $md5, $lastModified, $uid, $filename) {
		try {
			// OC_Log::write('OC_U_Drive_DB', 'updateItem', 1);
			$queryStr = 'UPDATE *PREFIX*fs SET `type`=?,`size`=?,`md5`=?,`lastModified`=?,`deleteDate`=? WHERE `uid`=? AND `filename`=?';
			$query = OC_DB::prepare($queryStr);
			$query -> execute(array($type, $size, $md5, $lastModified, NULL, $uid, $filename));
			return TRUE;
		} catch(exception $e) {
			OC_Log::writeException('OC_U_Drive_DB', 'insertItem', $e);
			return FALSE;
		}
	}

	static function deleteItem($deleteDate, $uid, $filename) {
		try {
			// OC_Log::write('OC_U_Drive_DB', 'deleteItem', 1);
			$queryStr = 'UPDATE *PREFIX*fs SET `deleteDate`=? WHERE `uid`=? AND `filename`=?';
			//如果是資料夾的話，則底下的檔案也要mark delete
			if (substr($filename, -1, 1) == '/') {
				$queryStr = 'UPDATE *PREFIX*fs SET `deleteDate`=? WHERE `uid`=? AND `filename`LIKE ?';
				$filename .= '%';
			}
			$query = OC_DB::prepare($queryStr);
			$query -> execute(array($deleteDate, $uid, $filename));
			return TRUE;
		} catch(exception $e) {
			OC_Log::writeException('OC_U_Drive_DB', 'deleteItem', $e);
			return FALSE;
		}
	}

}
