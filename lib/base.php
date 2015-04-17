<?php
/**
 * ownCloud
 *
 * @author Frank Karlitschek
 * @copyright 2010 Frank Karlitschek karlitschek@kde.org
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
 * Class that is a namespace for all global OC variables
 * No, we can not put this class in its own file because it is used by
 * OC_autoload!
 */
class OC {
	/**
	 * Assoziative array for autoloading. classname => filename
	 */
	public static $CLASSPATH = array();
	/**
	 * $_SERVER['DOCUMENTROOT'] but without symlinks
	 */
	public static $DOCUMENTROOT = '';
	/**
	 * The installation path for owncloud on the server (e.g. /srv/http/owncloud)
	 */
	public static $SERVERROOT = '';
	/**
	 * the current request path relative to the owncloud root (e.g. files/index.php)
	 */
	public static $SUBURI = '';
	/**
	 * the owncloud root path for http requests (e.g. owncloud/)
	 */
	public static $WEBROOT = '';
	/**
	 * the folder that stores that data files for the filesystem of the user (e.g. /srv/http/owncloud/data/myusername/files)
	 */
	public static $CONFIG_DATADIRECTORY = '';
	/**
	 * the folder that stores the data for the root filesystem (e.g. /srv/http/owncloud/data)
	 */
	public static $CONFIG_DATADIRECTORY_ROOT = '';
	/**
	 * 使用者存放檔案的Folder路徑
	 */
	public static $USER_DATA_FOLDER = 'files';

	/**
	 * 設定php.ini檔
	 */
	private static function setPhpIni() {
		// set some stuff
		//ob_start();

		# 指定錯誤回報層級
		error_reporting(E_ALL | E_STRICT);
		if (defined('DEBUG') && DEBUG) {
			ini_set('display_errors', 1);
		}

		# 指定時區
		setlocale(LC_ALL, 'zh_TW.UTF8');
		date_default_timezone_set('Asia/Taipei');

		ini_set('arg_separator.output', '&amp;');

		# 設定程式最大可以執行時間, 12hrs(3600*12 seconds)
		@set_time_limit(43200);
		@ini_set('max_execution_time', 43200);
		@ini_set('max_input_time', 43200);

		# try to set the maximum filesize to 10G
		# 似乎無作用 server其實會以 server目錄下的.htaccess設定為主
		@ini_set('upload_max_filesize', '10G');
		@ini_set('post_max_size', '10G');
		@ini_set('file_uploads', '50');

		#session 存活時間 to 12hrs(3600*12 seconds)
		@ini_set('gc_maxlifetime', '43200');
	}

	/**
	 * SPL autoload,取得OwnClous核心class file
	 */
	public static function autoload($className) {
		if (strpos($className, 'OC_') === 0) {
			$file = 'lib/' . strtolower(str_replace('_', '/', substr($className, 3)) . '.php');
			$file = OC::$SERVERROOT . '/' . $file;
			if (file_exists($file)) {
				require_once ($file);
			}
		}
	}

	/**
	 * SPL autoload,如果有登錄到OC::$CLASSPATH,則requice_once該檔案
	 */
	static function autoloadByClassPath($className) {
		if (array_key_exists($className, OC::$CLASSPATH)) {
			$file = OC::$CLASSPATH[$className];
			$file = OC::$SERVERROOT . '/' . $file;
			if (file_exists($file)) {
				require_once ($file);
			}
		}
	}

	/**
	 * SPL autoload,如果class name開頭為 Sebre_,則requice_once Sebre底下對應的class file
	 */
	static function autoloadBySebre($className) {
		if (strpos($className, 'Sabre_') === 0) {
			$file = '3rdparty/' . str_replace('_', '/', $className) . '.php';
			$file = OC::$SERVERROOT . '/' . $file;
			if (file_exists($file)) {
				require_once ($file);
			}
		}
	}

	/**
	 * SPL autoload,如果class name開頭為 DIFF_,則依設定檔取得各別servertype的class file
	 * ex:
	 * config檔中的server type = p1
	 * 則 DIFF_User會自動抓取
	 * 主class:'/lib/servertype/p/user.php'
	 * 次class:'/lib/servertype/p/1/user.php'
	 */
	public static function autoloadByDiffServerType($className) {
		$serverType = OC_Helper::serverType();
		# p or s
		$mainType = substr($serverType, 0, 1);
		# number
		$subType = substr($serverType, 1);
		if ($serverType && strpos($className, 'DIFF_') === 0) {
			$className = strtolower(str_replace('_', '/', substr($className, 5)));
			# 載入主要分類class
			$file = $className . '.php';
			$file = OC::$SERVERROOT . '/lib/servertype/' . $mainType . '/' . $file;
			if (file_exists($file)) {
				require_once ($file);
			}
			# 載入次要分類class
			$file = $className . '.php';
			$file = OC::$SERVERROOT . '/lib/servertype/' . $mainType . '/' . $subType . '/' . $file;
			if (file_exists($file)) {
				require_once ($file);
			}
		}
	}

	/**
	 * autodetects the formfactor of the used device
	 * default -> the normal desktop browser interface
	 * mobile -> interface for smartphones
	 * tablet -> interface for tablets
	 * standalone -> the default interface but without header, footer and sidebar. just the application. useful to ue just a specific app on the desktop in a standalone window.
	 */
	public static function detectFormfactor() {
		// please add more useragent strings for other devices
		if (isset($_SERVER['HTTP_USER_AGENT'])) {
			if (stripos($_SERVER['HTTP_USER_AGENT'], 'ipad') > 0) {
				$mode = 'tablet';
			} elseif (stripos($_SERVER['HTTP_USER_AGENT'], 'iphone') > 0) {
				$mode = 'mobile';
			} elseif ((stripos($_SERVER['HTTP_USER_AGENT'], 'N9') > 0) and (stripos($_SERVER['HTTP_USER_AGENT'], 'nokia') > 0)) {
				$mode = 'mobile';
			} else {
				$mode = 'default';
			}
		} else {
			$mode = 'default';
		}
		return ($mode);
	}

	/**
	 * 設定要讀取的js及css檔
	 */
	private static function loadWebFiles() {
		// Add the stuff we need always
		// OC_Util::addScript("jquery-1.6.4.min");
		// OC_Util::addScript("https://ajax.googleapis.com/ajax/libs/jquery/1.9.0/jquery.min");
		// OC_Util::addScript("jquery-1.9.1.min");
		OC_Util::addScript("jquery-1.10.2");
		// OC_Util::addScript("jquery-ui-1.8.16.custom.min");
		// OC_Util::addScript("https://ajax.googleapis.com/ajax/libs/jqueryui/1.10.1/jquery-ui");
		OC_Util::addScript("jquery-ui-1.10.2.custom.min");
		OC_Util::addScript("js");
		OC_Util::addScript("login");
		OC_Util::addScript("app");
		OC_Util::addScript("config");
		OC_Util::addScript("app_config");
		OC_Util::addScript("helper");
		OC_Util::addScript("navigation");
		OC_Util::addScript("public_functions");
		OC_Util::addScript('search', 'result');
		OC_Util::addScript("jquery-showpassword");
		OC_Util::addScript("jquery.infieldlabel.min");
		# 提示訊息
		OC_Util::addScript("jquery-tipsy");
		# string加密
		OC_Util::addScript('jquery.base64');
		OC_Util::addScript('jquery.mousewheel-3.0.6.pack');
		# 彈跳視窗
		OC_Util::addScript('jquery.fancybox-2.1.4.pack');
		# 輸入限制
		OC_Util::addScript('jquery.alphanumeric.pack');
		# 影片播放
		OC_Util::addScript('jwplayer/jwplayer');
		OC_Util::addScript('jwplayer/jwplayer.html5');

		OC_Util::addStyle("styles");
		//OC_Util::addStyle("jquery-ui-1.8.16.custom");
		//OC_Util::addStyle("https://ajax.googleapis.com/ajax/libs/jqueryui/1.10.1/themes/base/jquery-ui");
		OC_Util::addStyle("jquery-ui-1.10.2.custom.min");
		OC_Util::addStyle("jquery-tipsy");
		OC_Util::addStyle('jquery.fancybox-2.1.4');
	}

	public static function init() {
		# register autoloader
		# 優先讀取有登錄到 OC::$CLASSPATH 的程式
		spl_autoload_register(array(
			'OC',
			'autoloadByClassPath'
		));
		# 讀取 OwnCloud 的核心程式
		spl_autoload_register(array(
			'OC',
			'autoload'
		));
		# 讀取 Sebre Dav 的程式
		spl_autoload_register(array(
			'OC',
			'autoloadBySebre'
		));
		# 讀取不同 ServerType 的程式
		spl_autoload_register(array(
			'OC',
			'autoloadByDiffServerType'
		));

		# 修改php設定檔
		self::setPhpIni();

		//set http auth headers for apache+php-cgi work around
		if (isset($_SERVER['HTTP_AUTHORIZATION']) && preg_match('/Basic\s+(.*)$/i', $_SERVER['HTTP_AUTHORIZATION'], $matches)) {
			list($name, $password) = explode(':', base64_decode($matches[1]));
			$_SERVER['PHP_AUTH_USER'] = strip_tags($name);
			$_SERVER['PHP_AUTH_PW'] = strip_tags($password);
		}

		//set http auth headers for apache+php-cgi work around if variable gets renamed by apache
		if (isset($_SERVER['REDIRECT_HTTP_AUTHORIZATION']) && preg_match('/Basic\s+(.*)$/i', $_SERVER['REDIRECT_HTTP_AUTHORIZATION'], $matches)) {
			list($name, $password) = explode(':', base64_decode($matches[1]));
			$_SERVER['PHP_AUTH_USER'] = strip_tags($name);
			$_SERVER['PHP_AUTH_PW'] = strip_tags($password);
		}

		// calculate the documentroot
		OC::$DOCUMENTROOT = realpath($_SERVER['DOCUMENT_ROOT']);
		OC::$SERVERROOT = str_replace("\\", '/', substr(__FILE__, 0, -13));
		OC::$SUBURI = substr(realpath($_SERVER["SCRIPT_FILENAME"]), strlen(OC::$SERVERROOT));
		$scriptName = $_SERVER["SCRIPT_NAME"];
		if (substr($scriptName, -1) == '/') {
			$scriptName .= 'index.php';
		}
		OC::$WEBROOT = substr($scriptName, 0, strlen($scriptName) - strlen(OC::$SUBURI));

		if (OC::$WEBROOT != '' and OC::$WEBROOT[0] !== '/') {
			OC::$WEBROOT = '/' . OC::$WEBROOT;
		}

		// set the right include path
		set_include_path(OC::$SERVERROOT . '/lib' . PATH_SEPARATOR . OC::$SERVERROOT . '/config' . PATH_SEPARATOR . OC::$SERVERROOT . '/3rdparty' . PATH_SEPARATOR . get_include_path() . PATH_SEPARATOR . OC::$SERVERROOT);

		// Redirect to installer if not installed
		if (!OC_Config::getValue('installed', false) && OC::$SUBURI != '/index.php') {
			$url = 'http://' . $_SERVER['SERVER_NAME'] . OC::$WEBROOT . '/index.php';
			header("Location: $url");
			exit();
		}

		# 讀取更新檔
		OC_Util::loadUpdateFile();
		# 判斷是否強制開啟SSL, 否則重新導向
		global $RUNTIME_NOSSL;
		if (!$RUNTIME_NOSSL) {
			OC_Util::forceSsl();
		}
		# 更新安裝資訊(OwnCloud原生,已不使用)
		// OC_Util::updateInstallVersion();

		ini_set('session.cookie_httponly', '1;');
		session_start();

		// if the formfactor is not yet autodetected do the autodetection now. For possible forfactors check the detectFormfactor documentation
		if (!isset($_SESSION['formfactor'])) {
			$_SESSION['formfactor'] = OC::detectFormfactor();
		}
		// allow manual override via GET parameter
		if (isset($_GET['formfactor'])) {
			$_SESSION['formfactor'] = $_GET['formfactor'];
		}

		$errors = OC_Util::checkServer();
		if (count($errors) > 0) {
			OC_Template::printGuestPage('', 'error', array('errors' => $errors));
			exit ;
		}

		# 儲存取得的data dir路徑
		OC::$CONFIG_DATADIRECTORY_ROOT = OC_Config::getValue("datadirectory", OC::$SERVERROOT . "/data");
		# 使用者還未登入,先data dir路徑寫入
		OC::$CONFIG_DATADIRECTORY = OC::$CONFIG_DATADIRECTORY_ROOT;

		// User and Groups
		if (!OC_Config::getValue("installed", false)) {
			$_SESSION['user_id'] = '';
		}

		OC_User::useBackend(OC_Config::getValue("userbackend", "database"));
		OC_Group::setBackend(OC_Config::getValue("groupbackend", "database"));

		# 登錄預設的儲存路徑
		OC_Filesystem::registerStorageType('local', 'OC_Filestorage_Local', array('datadir' => 'string'));

		# 是否不要讀取js/css檔(要放在$RUNTIME_NOAPPS前面,必先讀取了APP的js才開始讀取核心js)
		global $RUNTIME_NOWEBFILES;
		if (!$RUNTIME_NOWEBFILES) {
			self::loadWebFiles();
		}

		# 是否不要讀取檔案系統
		global $RUNTIME_NOSETUPFS;
		if (!$RUNTIME_NOSETUPFS) {
			OC_Util::setupFS();
		}
		# 是否不要讀取APP
		global $RUNTIME_NOAPPS;
		# 要讀取的APP類型
		global $RUNTIME_APPTYPES;
		if (!$RUNTIME_NOAPPS) {
			if ($RUNTIME_APPTYPES) {
				# 讀取指定類別的APP
				OC_App::loadApps($RUNTIME_APPTYPES);
			} else {
				OC_App::loadApps();
			}
		}

		# 清除OwnCloud暫存資料
		OC_Files::cleanTmpFiles();
		// Last part: connect some hooks
		OC_HOOK::connect('OC_User', 'post_createUser', 'OC_Connector_Sabre_Principal', 'addPrincipal');
		OC_HOOK::connect('OC_User', 'post_deleteUser', 'OC_Connector_Sabre_Principal', 'deletePrincipal');
	}

}

# 宣告「是否不要讀取檔案系統」變數
if (!isset($RUNTIME_NOSETUPFS)) {
	$RUNTIME_NOSETUPFS = false;
}
# 宣告「是否不要讀取APP」變數
if (!isset($RUNTIME_NOAPPS)) {
	$RUNTIME_NOAPPS = false;
}
# 宣告「是否強制判斷SSL」變數
if (!isset($RUNTIME_NOSSL)) {
	$RUNTIME_NOSSL = false;
}
# 宣告「要讀取的APP類型」變數
if (!isset($RUNTIME_APPTYPES)) {
	$RUNTIME_APPTYPES = null;
}
# 宣告「是否不要讀取js/css檔」變數
if (!isset($RUNTIME_NOWEBFILES)) {
	$RUNTIME_NOWEBFILES = false;
}

# 宣告「是否有error exception」變數
if (!isset($RUNTIME_ERROR)) {
	$RUNTIME_ERROR = false;
}

if (!function_exists('get_temp_dir')) {
	function get_temp_dir() {
		if ($temp = ini_get('upload_tmp_dir'))
			return $temp;
		if ($temp = getenv('TMP'))
			return $temp;
		if ($temp = getenv('TEMP'))
			return $temp;
		if ($temp = getenv('TMPDIR'))
			return $temp;
		$temp = tempnam(__FILE__, '');
		if (file_exists($temp)) {
			unlink($temp);
			return dirname($temp);
		}
		return null;
	}

}

OC::init();

require_once ('fakedirstream.php');

// FROM search.php
new OC_Search_Provider_File();

# 讓try..catch..可以抓取Error錯誤
function exceptionErrorHandler($errno, $errstr, $errfile, $errline) {
	global $RUNTIME_ERROR;
	$RUNTIME_ERROR = true;
	// throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
}

set_error_handler("exceptionErrorHandler");
