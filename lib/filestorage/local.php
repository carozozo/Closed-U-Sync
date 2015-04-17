<?php
/**
 * for local filestore, we only have to map the paths
 */
class OC_Filestorage_Local extends OC_Filestorage {
	private $datadir;
	private static $mimetypes = null;
	public function __construct($arguments) {
		$this -> datadir = $arguments['datadir'];
		if (substr($this -> datadir, -1) !== '/') {
			$this -> datadir .= '/';
		}
	}

	public function mkdir($path) {
		return $return = mkdir($this -> datadir . $path);
	}

	public function rmdir($path) {
		return $return = rmdir($this -> datadir . $path);
	}

	public function opendir($path) {
		return opendir($this -> datadir . $path);
	}

	public function is_dir($path) {
		return (is_dir($this -> datadir . $path) or substr($path, -1) == '/');
	}

	public function is_file($path) {
		return is_file($this -> datadir . $path);
	}

	public function stat($path) {
		return stat($this -> datadir . $path);
	}

	public function filetype($path) {
		if ($this -> file_exists($path)) {
			$filetype = filetype($this -> datadir . $path);
			if ($filetype == 'link') {
				$filetype = filetype(realpath($this -> datadir . $path));
			}
			return $filetype;
		}
		return false;
	}

	public function filesize($path) {
		if ($this -> is_dir($path)) {
			return $this -> getFolderSize($path);
		} else {
			return filesize($this -> datadir . $path);
		}
	}

	public function filesizeWithoutFolder($path) {
		if ($this -> is_dir($path)) {
			return 0;
		} else {
			return filesize($this -> datadir . $path);
		}
	}

	public function is_readable($path) {
		return is_readable($this -> datadir . $path);
	}

	public function is_writeable($path) {
		return is_writable($this -> datadir . $path);
	}

	public function file_exists($path) {
		return file_exists($this -> datadir . $path);
	}

	public function readfile($path) {
		//return readfile($this->datadir.$path);
		return $this -> readfile_chunked($this -> datadir . $path);
	}

	public function filectime($path) {
		return filectime($this -> datadir . $path);
	}

	public function filemtime($path) {
		return filemtime($this -> datadir . $path);
	}

	public function fileatime($path) {
		return fileatime($this -> datadir . $path);
	}

	public function touch($path, $mtime = null) {
		// sets the modification time of the file to the given value.
		// If mtime is nil the current time is set.
		// note that the access time of the file always changes to the current time.
		if (!is_null($mtime)) {
			$result = touch($this -> datadir . $path, $mtime);
		} else {
			$result = touch($this -> datadir . $path);
		}
		if ($result) {
			clearstatcache(true, $this -> datadir . $path);
		}

		return $result;
	}

	public function file_get_contents($path) {
		return file_get_contents($this -> datadir . $path);
	}

	public function file_put_contents($path, $data) {
		file_put_contents($this -> datadir . $path, $data);
	}

	public function unlink($path) {
		return $return = $this -> delTree($path);
	}

	public function rename($path1, $path2) {
		if (!$this -> file_exists($path1)) {
			OC_Log::write('core', 'unable to rename, file does not exists : ' . $path1, OC_Log::ERROR);
			return false;
		}
		$localPath1 = $this -> datadir . $path1;
		$localPath2 = $this -> datadir . $path2;
		return self::localRename($localPath1, $localPath2);
	}

	public function localRename($localPath1, $localPath2) {
		if (!file_exists($localPath1)) {
			OC_Log::write('core', 'unable to rename, file does not exists : ' . $localPath1, OC_Log::ERROR);
			return false;
		}
		$return = true;
		if (is_dir($localPath1)) {
			if (!file_exists($localPath2)) {
				if (!mkdir($localPath2)) {
					OC_Log::write('OC_Filestorage_Local', 'unable to rename, mkdir failed : ' . $localPath2, OC_Log::ERROR);
					$return = false;
				}
			}
			if ($dh = opendir($localPath1)) {
				while (($filename = readdir($dh)) !== false) {
					if ($filename != '.' && $filename != '..') {
						$fileLocalPath1 = $localPath1 . '/' . $filename;
						$fileLocalPath2 = $localPath2 . '/' . $filename;
						if (!$this::localRename($fileLocalPath1, $fileLocalPath2)) {
							$return = false;
						}
					}
				}
				closedir($dh);
				# 如果資料夾底下的檔案都移走了，則刪掉資料夾
				if ($return)
					rmdir($localPath1);
			}
		} else {
			if (!rename($localPath1, $localPath2)) {
				OC_Log::write('OC_Filestorage_Local', 'rename ' . $localPath1 . ' to ' . $localPath2 . ' failed', OC_Log::ERROR);
				$return = false;
			}
		}
		return $return;
	}

	public function copy($path1, $path2) {
		// OC_Log::write('OC_FilesStorge copy', '$path1=' . $path1 . ',$path2=' . $path2, 1);
		if (!$this -> file_exists($path1)) {
			OC_Log::write('OC_Filestorage_Local', 'unable to copy, file does not exists : ' . $path1, OC_Log::ERROR);
			return false;
		}
		$localPath1 = $this -> datadir . $path1;
		$localPath2 = $this -> datadir . $path2;
		return self::localCopy($localPath1, $localPath2);
	}

	public function localCopy($localPath1, $localPath2) {
		if (!file_exists($localPath1)) {
			OC_Log::write('OC_Filestorage_Local', 'unable to copy, file does not exists : ' . $localPath1, OC_Log::ERROR);
			return false;
		}
		// $return = true;
		if (is_dir($localPath1)) {
			if (!file_exists($localPath2)) {
				if (!mkdir($localPath2)) {
					OC_Log::write('OC_Filestorage_Local', 'unable to copy, mkdir failed : ' . $localPath2, OC_Log::ERROR);
					return false;
				}
			}
			if ($dh = opendir($localPath1)) {
				while (($filename = readdir($dh)) !== false) {
					if ($filename != '.' && $filename != '..') {
						$fileLocalPath1 = $localPath1 . '/' . $filename;
						$fileLocalPath2 = $localPath2 . '/' . $filename;
						// OC_Log::write('OC_Filestorage_Local localCopy', '$fileLocalPath1=' . $fileLocalPath1 . ',$fileLocalPath2=' . $fileLocalPath2, 1);
						if (!$this::localCopy($fileLocalPath1, $fileLocalPath2)) {
							return false;
						}
					}
				}
				closedir($dh);
			}
		} else {
			if (!copy($localPath1, $localPath2)) {
				OC_Log::write('OC_Filestorage_Local', 'copy ' . $localPath1 . ' to ' . $localPath2 . ' failed', OC_Log::ERROR);
				return false;
			}
		}
		return true;
	}

	public function fopen($path, $mode) {
		if ($return = fopen($this -> datadir . $path, $mode)) {
			switch($mode) {
				case 'r' :
					break;
				case 'r+' :
				case 'w+' :
				case 'x+' :
				case 'a+' :
					break;
				case 'w' :
				case 'x' :
				case 'a' :
					break;
			}
		}
		return $return;
	}

	/**
	 * 跨不同存儲體時，將檔案複製到暫存路徑
	 * @param  string $tmpFile
	 * @param  string $path
	 * @return boolean
	 */
	public function toTmpfile($path) {
		$localPath = $this -> datadir . $path;
		if (!file_exists($localPath)) {
			OC_Log::write('OC_Filestorage_Local', 'toTmpfile failed, file does not exists : ' . $localPath, OC_Log::ERROR);
			return false;
		}
		$tmpFolder = get_temp_dir();
		$randName = OC_Helper::randomPassword(10);
		$tmpPath = $tmpFolder . '/' . $randName;
		if ($this -> localCopy($localPath, $tmpPath))
			return $tmpPath;
		return false;
	}

	/**
	 * 跨不同存儲體時，將檔案從暫存路徑複製到目標路徑
	 * @param  string $tmpFile
	 * @param  string $path
	 * @return boolean
	 */
	public function fromTmpFile($tmpPath, $path) {
		// $fileStats = stat($tmpPath);
		if (!file_exists($tmpPath)) {
			OC_Log::write('OC_Filestorage_Local', 'fromTmpFile failed, file does not exists : ' . $tmpPath, OC_Log::ERROR);
			return false;
		}
		$localPath = $this -> datadir . $path;
		return $this -> localRename($tmpPath, $localPath);
	}

	public function fromUploadedFile($tmpFile, $path) {
		$fileStats = stat($tmpFile);
		if (move_uploaded_file($tmpFile, $this -> datadir . $path)) {
			touch($this -> datadir . $path, $fileStats['mtime'], $fileStats['atime']);
			return true;
		} else {
			return false;
		}
	}

	public function getMimeType($fspath) {
		if ($this -> is_readable($fspath)) {
			$mimeType = 'application/octet-stream';
			if ($mimeType == 'application/octet-stream') {
				self::$mimetypes =
				include ('mimetypes.fixlist.php');
				$extention = strtolower(strrchr(basename($fspath), "."));
				$extention = substr($extention, 1);
				# remove leading .
				$mimeType = (isset(self::$mimetypes[$extention])) ? self::$mimetypes[$extention] : 'application/octet-stream';
			}
			if (@is_dir($this -> datadir . $fspath)) {
				# directories are easy
				return "httpd/unix-directory";
			}
			if ($mimeType == 'application/octet-stream' and function_exists('finfo_open') and function_exists('finfo_file') and $finfo = finfo_open(FILEINFO_MIME)) {
				$mimeType = strtolower(finfo_file($finfo, $this -> datadir . $fspath));
				$mimeType = substr($mimeType, 0, strpos($mimeType, ';'));
				finfo_close($finfo);
			}
			if ($mimeType == 'application/octet-stream' && function_exists("mime_content_type")) {
				# use mime magic extension if available
				$mimeType = mime_content_type($this -> datadir . $fspath);
			}
			if ($mimeType == 'application/octet-stream' && OC_Helper::canExecute("file")) {
				# it looks like we have a 'file' command,
				# lets see it it does have mime support
				$fspath = str_replace("'", "\'", $fspath);
				$fp = popen("file -i -b '{$this->datadir}$fspath' 2>/dev/null", "r");
				$reply = fgets($fp);
				pclose($fp);
				# trim the character set from the end of the response
				# reply的格式類似[application/octet-stream; charset=binary]
				$mimeType = substr($reply, 0, strrpos($reply, ';'));
			}
			if ($mimeType == 'application/octet-stream') {
				# Fallback solution: (try to guess the type by the file extension
				if (!self::$mimetypes || self::$mimetypes !=
				include ('mimetypes.list.php')) {
					self::$mimetypes =
					include ('mimetypes.list.php');
				}
				$extention = strtolower(strrchr(basename($fspath), "."));
				$extention = substr($extention, 1);
				//remove leading .
				$mimeType = (isset(self::$mimetypes[$extention])) ? self::$mimetypes[$extention] : 'application/octet-stream';
			}
			return $mimeType;
		}
	}

	/**
	 * 取得副檔名
	 * @author 20130708 add by Caro Huang
	 * @param 檔案路徑
	 */
	public function getExtension($path) {
		$localPath = $this -> getLocalFullPath($path);
		if (!file_exists($localPath)) {
			OC_Log::write('OC_Filestorage_Local', 'getExtension failed, file does not exists : ' . $localPath, OC_Log::ERROR);
			return false;
		}
		return $ext = pathinfo($localPath, PATHINFO_EXTENSION);
	}

	private function delTree($dir) {
		$dirRelative = $dir;
		$dir = $this -> datadir . $dir;
		if (!file_exists($dir))
			return true;
		if (!is_dir($dir) || is_link($dir))
			return unlink($dir);
		foreach (scandir($dir) as $item) {
			if ($item == '.' || $item == '..')
				continue;
			if (is_file($dir . '/' . $item)) {
				unlink($dir . '/' . $item);
			} elseif (is_dir($dir . '/' . $item)) {
				if (!$this -> delTree($dirRelative . "/" . $item)) {
					return false;
				};
			}
		}
		return rmdir($dir);
	}

	public function hash($type, $path, $raw) {
		return hash_file($type, $this -> datadir . $path, $raw);
	}

	public function free_space($path) {
		return disk_free_space($this -> datadir . $path);
	}

	public function search($query) {
		return $this -> searchInDir($query);
	}

	public function getLocalFullPath($path) {
		return OC_Helper::pathForbiddenChar($this -> datadir . '/' . $path);
	}

	public function getLocalUserIdByPath($path) {
		$localPath = $this -> getLocalFullPath($path);
		$localPath = substr($localPath, strlen(OC::$CONFIG_DATADIRECTORY_ROOT));
		$localPathArray = explode('/', $localPath);
		return $userId = $localPathArray[1];
	}

	private function searchInDir($query, $dir = '') {
		$files = array();
		foreach (scandir($this->datadir.$dir) as $item) {
			if ($item == '.' || $item == '..')
				continue;
			if (strstr(strtolower($item), strtolower($query)) !== false) {
				$files[] = $dir . '/' . $item;
			}
			if (is_dir($this -> datadir . $dir . '/' . $item)) {
				$files = array_merge($files, $this -> searchInDir($query, $dir . '/' . $item));
			}
		}
		return $files;
	}

	/**
	 * @brief get the size of folder and it's content
	 * @param string $path file path
	 * @return int size of folder and it's content
	 */
	public function getFolderSize($path) {
		$path = str_replace('//', '/', $path);
		if ($this -> is_dir($path) && substr($path, -1) != '/') {
			$path .= '/';
		}

		/* 不從DB找記錄，直接計算 folder size
		 $query = OC_DB::prepare("SELECT size FROM *PREFIX*foldersize");
		 $size = $query -> execute(array($path)) -> fetchAll();
		 if ( count($size) > 0 ) {// we already the size, just return it
		 return $size[0]['size'];
		 } else {//the size of the folder isn't know, calulate it

		 return $this -> calculateFolderSize($path);
		 }*/
		return $this -> calculateFolderSize($path);
	}

	/**
	 * @brief calulate the size of folder and it's content and cache it
	 * @param string $path file path
	 * @return int size of folder and it's content
	 */
	public function calculateFolderSize($path) {
		if ($this -> is_file($path)) {
			$path = dirname($path);
		}
		$path = str_replace('//', '/', $path);
		if ($this -> is_dir($path) && substr($path, -1) != '/') {
			$path .= '/';
		}
		$size = 0;
		if ($dh = $this -> opendir($path)) {
			while (($filename = readdir($dh)) !== false) {
				if ($filename != '.' && $filename != '..') {
					$subFile = $path . '/' . $filename;
					if ($this -> is_file($subFile)) {
						$size += $this -> filesize($subFile);
					} else {
						$size += $this -> getFolderSize($subFile);
					}
				}
			}
			/* 20121211.不將File Size 寫入DB
			 if($size>0){
			 $query=OC_DB::prepare("INSERT INTO *PREFIX*foldersize VALUES(?,?)");
			 $result=$query->execute(array($path,$size));
			 }*/
		}
		return $size;
	}

	/**
	 * Solve file size over 4G can't download problem
	 *
	 * @param string $filename
	 *
	 */
	public function readfile_chunked($filename, $retbytes = true) {
		$chunksize = (1024) * 8;
		// how many bytes per chunk
		$buffer = '';
		$cnt = 0;
		@ob_end_clean();
		$handle = fopen($filename, 'rb');
		if ($handle === false) {
			return false;
		}
		while (!feof($handle)) {
			$buffer = fread($handle, $chunksize);
			echo $buffer;
			//ob_flush();
			//flush();
			if ($retbytes) {
				$cnt += strlen($buffer);
			}
		}
		$status = fclose($handle);
		if ($retbytes && $status) {
			return $cnt;
		}
		return $status;
	}

}
