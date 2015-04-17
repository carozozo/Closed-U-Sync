<?php
/**
 * ownCloud
 *
 * @author Michael Gapczynski
 * @copyright 2011 Michael Gapczynski GapczynskiM@gmail.com
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU AFFERO GENERAL PUBLIC LICENSE
 * License as published by the Free Software Foundation; either
 * version 3 of the License, or any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU AFFERO GENERAL PUBLIC LICENSE for more details.
 *
 * You should have received a copy of the GNU Affero General Public
 * License along with this library.  If not, see <http://www.gnu.org/licenses/>.
 *
 */
/**
 * Convert target path to source path and pass the function call to the correct storage provider
 */
class OC_Filestorage_GroupShared extends OC_Filestorage {

	private $datadir;
	private $sourcePaths = array();

	public function __construct($arguments) {
		$this -> datadir = $arguments['datadir'];
		$this -> datadir .= "/";
	}

	public function getInternalPath($path) {
		$mountPoint = OC_Filesystem::getMountPoint($path);
		$internalPath = substr($path, strlen($mountPoint));
		return $internalPath;
	}

	public function getSource($target) {
		$target = $this -> datadir . $target;
		if (array_key_exists($target, $this -> sourcePaths)) {
			return $this -> sourcePaths[$target];
		} else {
			$source = OC_GroupShare::getSource($target);
			$this -> sourcePaths[$target] = $source;
			return $source;
		}
	}

	public function mkdir($path) {
		if ($path == "" || $path == "/" || !$this -> is_writeable($path)) {
			return false;
		} else {
			$source = $this -> getSource($path);
			if ($source) {
				$storage = OC_Filesystem::getStorage($source);
				return $storage -> mkdir($this -> getInternalPath($source));
			}
		}
	}

	public function rmdir($path) {
		# The folder will be removed from the database, but won't be deleted from the owner's filesystem
		OC_GroupShare::unshareFromMySelf($this -> datadir . $path);
	}

	public function opendir($path) {
		if ($path == "" || $path == "/") {
			$path = $this -> datadir . $path;
			$sharedItems = OC_GroupShare::getItemsInFolder($path);
			global $FAKEDIRS;
			$files = array();
			foreach ($sharedItems as $item) {
				# If item is in the root of the shared storage provider and the item exists add it to the fakedirs
				if (dirname($item['target']) . "/" == $path && $this -> file_exists(basename($item['target']))) {
					$files[] = basename($item['target']);
				}
			}
			$FAKEDIRS['groupshared'] = $files;
			return opendir('fakedir://groupshared');
		} else {
			$source = $this -> getSource($path);
			if ($source) {
				$storage = OC_Filesystem::getStorage($source);
				$dh = $storage -> opendir($this -> getInternalPath($source));
				$modifiedItems = OC_GroupShare::getItemsInFolder($source);
				if ($modifiedItems && $dh) {
					$sources = array();
					$targets = array();
					# Remove any duplicate or trailing '/'
					$path = preg_replace('{(/)\1+}', "/", $path);
					$targetFolder = rtrim($this -> datadir . $path, "/");
					foreach ($modifiedItems as $item) {
						# If the item is in the current directory and the item exists add it to the arrays
						if (dirname($item['target']) == $targetFolder && $this -> file_exists($path . "/" . basename($item['target']))) {
							# If the item was unshared from self, add it it to the arrays
							if ($item['permissions'] == OC_GroupShare::UNSHARED) {
								$sources[] = basename($item['source']);
								$targets[] = "";
							} else {
								$sources[] = basename($item['source']);
								$targets[] = basename($item['target']);
							}
						}
					}
					# Don't waste time if there aren't any modified items in the current directory
					if (empty($sources)) {
						return $dh;
					} else {
						global $FAKEDIRS;
						$files = array();
						while (($filename = readdir($dh)) !== false) {
							if ($filename != "." && $filename != "..") {
								# If the file isn't in the sources array it isn't modified and can be added as is
								if (!in_array($filename, $sources)) {
									$files[] = $filename;
									# The file has a different name than the source and is added to the fakedirs
								} else {
									$target = $targets[array_search($filename, $sources)];
									# Don't add the file if it was unshared from self by the user
									if ($target != "") {
										$files[] = $target;
									}
								}
							}
						}
						$FAKEDIRS['groupshared'] = $files;
						return opendir('fakedir://groupshared');
					}
				} else {
					return $dh;
				}
			}
		}
	}

	public function is_dir($path) {
		if ($path == "" || $path == "/") {
			return true;
		} else {
			$source = $this -> getSource($path);
			if ($source) {
				$storage = OC_Filesystem::getStorage($source);
				return $storage -> is_dir($this -> getInternalPath($source));
			}
		}
	}

	public function is_file($path) {
		$source = $this -> getSource($path);
		if ($source) {
			$storage = OC_Filesystem::getStorage($source);
			return $storage -> is_file($this -> getInternalPath($source));
		}
	}

	# TODO fill in other components of array
	public function stat($path) {
		if ($path == "" || $path == "/") {
			$stat["dev"] = "";
			$stat["ino"] = "";
			$stat["mode"] = "";
			$stat["nlink"] = "";
			$stat["uid"] = "";
			$stat["gid"] = "";
			$stat["rdev"] = "";
			$stat["size"] = $this -> filesize($path);
			$stat["atime"] = $this -> fileatime($path);
			$stat["mtime"] = $this -> filemtime($path);
			$stat["ctime"] = $this -> filectime($path);
			$stat["blksize"] = "";
			$stat["blocks"] = "";
			return $stat;
		} else {
			$source = $this -> getSource($path);
			if ($source) {
				$storage = OC_Filesystem::getStorage($source);
				return $storage -> stat($this -> getInternalPath($source));
			}
		}
	}

	public function filetype($path) {
		if ($path == "" || $path == "/") {
			return "dir";
		} else {
			$source = $this -> getSource($path);
			if ($source) {
				$storage = OC_Filesystem::getStorage($source);
				return $storage -> filetype($this -> getInternalPath($source));
			}
		}

	}

	public function filesize($path) {
		if ($path == "" || $path == "/" || $this -> is_dir($path)) {
			return $this -> getFolderSize($path);
		} else {
			$source = $this -> getSource($path);
			if ($source) {
				$storage = OC_Filesystem::getStorage($source);
				return $storage -> filesize($this -> getInternalPath($source));
			}
		}
	}

	public function filesizeWithoutFolder($path) {
		if ($path == "" || $path == "/" || $this -> is_dir($path)) {
			return 0;
		} else {
			$source = $this -> getSource($path);
			if ($source) {
				$storage = OC_Filesystem::getStorage($source);
				return $storage -> filesize($this -> getInternalPath($source));
			}
		}
	}

	public function getFolderSize($path) {
		# Shared folder sizes are cached separately from the source folder sizes because folders can have different names
		$path = rtrim($path, "/");
		$path = ltrim($path, "/");
		$path = preg_replace('{(/)\1+}', "/", $path);
		$dbpath = rtrim($this -> datadir . $path, "/");
		$query = OC_DB::prepare("SELECT size FROM *PREFIX*foldersize WHERE path = ?");
		$size = $query -> execute(array($dbpath)) -> fetchAll();
		if (count($size) > 0) {
			return $size[0]['size'];
		} else {
			return $this -> calculateFolderSize($path);
		}
	}

	private function calculateFolderSize($path) {
		if ($this -> is_file($path)) {
			$path = dirname($path);
		}
		$size = 0;
		if ($dh = $this -> opendir($path)) {
			while (($filename = readdir($dh)) !== false) {
				if ($filename != "." && $filename != "..") {
					$subFile = $path . "/" . $filename;
					if ($this -> is_file($subFile)) {
						$size += $this -> filesize($subFile);
					} else {
						$size += $this -> getFolderSize($subFile);
					}
				}
			}
			if ($size > 0) {
				$dbpath = rtrim($this -> datadir . $path, "/");
				$query = OC_DB::prepare("INSERT INTO *PREFIX*foldersize VALUES(?,?)");
				$result = $query -> execute(array(
					$dbpath,
					$size
				));
			}
		}
		return $size;
	}

	# TODO 權限部份還未定案
	public function is_readable($path) {
		# 傳過來的path為「Groupshared/」底下的路徑
		if ($path == "" || $path == "/") {
			return true;
		}
		$permission = OC_GroupShare::getPermissions($this -> datadir . $path);
		if ($permission == OC_GroupShare::ALLOW || $permission == OC_GroupShare::DOWNLOAD_COPY) {
			return true;
		}
		return false;
	}

	# TODO 權限部份還未定案
	public function is_writeable($path) {
		# 傳過來的path為「Groupshared/」底下的路徑
		if ($path == "" || $path == "/") {
			return false;
		}
		$permission = OC_GroupShare::getPermissions($this -> datadir . $path);
		if ($permission == OC_GroupShare::ALLOW || $permission == OC_GroupShare::UPLOAD) {
			return true;
		}
		return false;
	}

	public function file_exists($path) {
		if ($path == "" || $path == "/") {
			return true;
		} else {
			$source = $this -> getSource($path);
			if ($source) {
				$storage = OC_Filesystem::getStorage($source);
				return $storage -> file_exists($this -> getInternalPath($source));
			}
		}
	}

	public function readfile($path) {
		$source = $this -> getSource($path);
		if ($source) {
			$storage = OC_Filesystem::getStorage($source);
			return $storage -> readfile($this -> getInternalPath($source));
		}
	}

	public function filectime($path) {
		if ($path == "" || $path == "/") {
			$ctime = 0;
			if ($dh = $this -> opendir($path)) {
				while (($filename = readdir($dh)) !== false) {
					$tempctime = $this -> filectime($filename);
					if ($tempctime < $ctime) {
						$ctime = $tempctime;
					}
				}
				return $ctime;
			}
		} else {
			$source = $this -> getSource($path);
			if ($source) {
				$storage = OC_Filesystem::getStorage($source);
				return $storage -> filectime($this -> getInternalPath($source));
			}
		}
	}

	public function filemtime($path) {
		if ($path == "" || $path == "/") {
			$mtime = 0;
			if ($dh = $this -> opendir($path)) {
				while (($filename = readdir($dh)) !== false) {
					$tempmtime = $this -> filemtime($filename);
					if ($tempmtime > $mtime) {
						$mtime = $tempmtime;
					}
				}
				return $mtime;
			}
		} else {
			$source = $this -> getSource($path);
			if ($source) {
				$storage = OC_Filesystem::getStorage($source);
				return $storage -> filemtime($this -> getInternalPath($source));
			}
		}
	}

	public function fileatime($path) {
		if ($path == "" || $path == "/") {
			$atime = 0;
			if ($dh = $this -> opendir($path)) {
				while (($filename = readdir($dh)) !== false) {
					$tempatime = $this -> fileatime($filename);
					if ($tempatime > $atime) {
						$atime = $tempatime;
					}
				}
				return $atime;
			}
		} else {
			$source = $this -> getSource($path);
			if ($source) {
				$storage = OC_Filesystem::getStorage($source);
				return $storage -> fileatime($this -> getInternalPath($source));
			}
		}
	}

	public function file_get_contents($path) {
		$source = $this -> getSource($path);
		if ($source) {
			$storage = OC_Filesystem::getStorage($source);
			return $storage -> file_get_contents($this -> getInternalPath($source));
		}
	}

	public function file_put_contents($path, $data) {
		if ($this -> is_writeable($path)) {
			$source = $this -> getSource($path);
			if ($source) {
				$storage = OC_Filesystem::getStorage($source);
				$result = $storage -> file_put_contents($this -> getInternalPath($source), $data);
				return $result;
			}
		}
	}

	public function unlink($path) {
		# The item will be removed from the database, but won't be touched on the owner's filesystem
		$target = $this -> datadir . $path;
		/*
		 # Check if the item is inside a shared folder
		 if (OC_GroupShare::getParentFolders($target)) {
		 # If entry for item already exists
		 if (OC_GroupShare::getItem($target)) {
		 OC_GroupShare::unshareFromMySelf($target, false);
		 } else {
		 OC_GroupShare::pullOutOfFolder($target, $target);
		 OC_GroupShare::unshareFromMySelf($target, false);
		 }
		 # Delete the database entry
		 } else {
		 OC_GroupShare::unshareFromMySelf($target);
		 }
		 */
		OC_GroupShare::unshareFromMySelf($target);
		return true;
	}

	public function rename($path1, $path2) {
		$oldTarget = $this -> datadir . $path1;
		$ext = pathinfo($oldTarget, PATHINFO_EXTENSION);
		# 如果副檔名不是usync的話(device上傳檔案的暫存檔)，則直接跳出
		if ($ext != 'usync') {
			return true;
		}

		$newTarget = $this -> datadir . $path2;
		# Check if the item is inside a shared folder
		if ($folders = OC_GroupShare::getParentFolders($oldTarget)) {
			$root1 = substr($path1, 0, strpos($path1, "/"));
			$root2 = substr($path1, 0, strpos($path2, "/"));
			# Prevent items from being moved into different shared folders until versioning (cut and paste) and prevent items going into 'Shared'
			if ($root1 !== $root2) {
				return false;
				# Check if both paths have write permission
			} else if ($this -> is_writeable($path1) && $this -> is_writeable($path2)) {
				$oldSource = $this -> getSource($path1);
				$newSource = $folders['source'] . substr($newTarget, strlen($folders['target']));
				if ($oldSource) {
					$storage = OC_Filesystem::getStorage($oldSource);
					return $storage -> rename($this -> getInternalPath($oldSource), $this -> getInternalPath($newSource));
				}
				# If the user doesn't have write permission, items can only be renamed and not moved
			} else if (dirname($path1) !== dirname($path2)) {
				return false;
				# The item will be renamed in the database, but won't be touched on the owner's filesystem
			} else {
				OC_GroupShare::pullOutOfFolder($oldTarget, $newTarget);
				# If this is a folder being renamed, call setTarget in case there are any database entries inside the folder
				if (self::is_dir($path1)) {
					OC_GroupShare::setTarget($oldTarget, $newTarget);
				}
			}
		} else {
			OC_GroupShare::setTarget($oldTarget, $newTarget);
		}
		return true;

	}

	public function copy($path1, $path2) {
		if ($path2 == "" || $path2 == "/") {
			# TODO Construct new shared item or should this not be allowed?
		} else {
			if ($this -> is_writeable($path2)) {
				$tmpFile = $this -> toTmpFile($path1);
				$result = $this -> fromTmpFile($tmpFile, $path2);
				return $result;
			} else {
				return false;
			}
		}
	}

	public function fopen($path, $mode) {
		$source = $this -> getSource($path);
		if ($source) {
			$dir = dirname($source);
			$lastDir = substr($dir, strrpos($dir, '/'));
			# 如果是可讀的，或者是縮圖資料夾底下的縮圖，才可以下載
			if ($this -> is_readable($path) || $lastDir == '/.thumbs') {
				$storage = OC_Filesystem::getStorage($source);
				return $storage -> fopen($this -> getInternalPath($source), $mode);
			}
		}
	}

	public function toTmpFile($path) {
		$source = $this -> getSource($path);
		if ($source) {
			$storage = OC_Filesystem::getStorage($source);
			return $storage -> toTmpFile($this -> getInternalPath($source));
		}
	}

	public function fromTmpFile($tmpFile, $path) {
		if ($this -> is_writeable($path)) {
			$source = $this -> getSource($path);
			if ($source) {
				$storage = OC_Filesystem::getStorage($source);
				$result = $storage -> fromTmpFile($tmpFile, $this -> getInternalPath($source));
				return $result;
			}
		} else {
			return false;
		}
	}

	public function fromUploadedFile($tmpFile, $path) {
		if ($this -> is_writeable($path)) {
			$source = $this -> getSource($path);
			if ($source) {
				$storage = OC_Filesystem::getStorage($source);
				$result = $storage -> fromUploadedFile($tmpFile, $this -> getInternalPath($source));
				return $result;
			}
		} else {
			return false;
		}
	}

	public function getMimeType($path) {
		$source = $this -> getSource($path);
		if ($source) {
			$storage = OC_Filesystem::getStorage($source);
			return $storage -> getMimeType($this -> getInternalPath($source));
		}
	}

	/**
	 * 取得副檔名
	 * @author 20130708 add by Caro Huang
	 * @param 檔案路徑
	 */
	public function getExtension($path) {
		$source = $this -> getSource($path);
		if ($source) {
			$storage = OC_Filesystem::getStorage($source);
			return $storage -> getExtension($this -> getInternalPath($source));
		}
		return false;
	}

	public function hash($type, $path, $raw) {
		$source = $this -> getSource($path);
		if ($source) {
			$storage = OC_Filesystem::getStorage($source);
			return $storage -> hash($type, $this -> getInternalPath($source), $raw);
		}
	}

	public function free_space($path) {
		$source = $this -> getSource($path);
		if ($source) {
			$storage = OC_Filesystem::getStorage($source);
			return $storage -> free_space($this -> getInternalPath($source));
		}
	}

	public function search($query) {
		return $this -> searchInDir($query);
	}

	private function searchInDir($query, $path = "") {
		$files = array();
		if ($dh = $this -> opendir($path)) {
			while (($filename = readdir($dh)) !== false) {
				if ($filename != "." && $filename != "..") {
					if (strstr(strtolower($filename), strtolower($query))) {
						$files[] = $path . "/" . $filename;
					}
					if ($this -> is_dir($path . "/" . $filename)) {
						$files = array_merge($files, $this -> searchInDir($query, $path . "/" . $filename));
					}
				}
			}
		}
		return $files;
	}

	public function getLocalFullPath($path) {
		$source = $this -> getSource($path);
		if (OC::$CONFIG_DATADIRECTORY_ROOT) {
			$source = OC_Helper::pathForbiddenChar(OC::$CONFIG_DATADIRECTORY_ROOT . '/' . $source);
			return $source;
		}
	}

	public function getLocalUserIdByPath($path) {
		$localPath = $this -> getLocalFullPath($path);
		if ($localPath != OC::$CONFIG_DATADIRECTORY_ROOT) {
			$localPath = substr($localPath, strlen(OC::$CONFIG_DATADIRECTORY_ROOT));
			$localPathArray = explode('/', $localPath);
			return $userId = $localPathArray[1];
		}
		return OC_User::getUser();
	}

	public function touch($path, $mtime = null) {
		$source = $this -> getSource($path);
		if ($source) {
			$storage = OC_Filesystem::getStorage($source);
			return $storage -> touch($this -> getInternalPath($source), $time);
		}
	}

}
?>
