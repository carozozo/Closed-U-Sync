<?php

/**
 * Class for utility functions
 *
 */
class OC_Util {
    public static $scripts = array();
    public static $styles = array();
    public static $headers = array();
    private static $fsSetup = false;
    # 是否載入 Jmail原件
    private static $requiredJmail = false;


    /**
     * 設置檔案系統
     * @param $user 使用者帳號
     * @param $userDataFolder 使用者放資料的路徑(沒設的話則用config.php的預設值)
     * @param $forceSetup 是否強制設置(某些特殊狀況下需要，例如某 user 已登入，卻需要讀取其它 user 的檔案結構)
     */
    public static function setupFS($user = "", $userDataFolder = null,$forceSetup=false) {// configure the initial filesystem based on the configuration
        if (!$userDataFolder) {
            # 如果沒有指名檔案要放在user底下的哪個folder,則使用預設值
            $userDataFolder = OC::$USER_DATA_FOLDER;
        } else {
            OC::$USER_DATA_FOLDER = $userDataFolder;
        }
        # 如果不強制設置，而檔案系統在先前已建立，則跳出
        if (!$forceSetup and self::$fsSetup) {
            return false;
        }
        $CONFIG_DATADIRECTORY_ROOT = OC_Config::getValue("datadirectory", OC::$SERVERROOT . "/data");
        $CONFIG_BACKUPDIRECTORY = OC_Config::getValue("backupdirectory", OC::$SERVERROOT . "/backup");

        // Create root dir
        if (!is_dir($CONFIG_DATADIRECTORY_ROOT)) {
            $success = @mkdir($CONFIG_DATADIRECTORY_ROOT);
            if (!$success) {
                $tmpl = new OC_Template('', 'error', 'guest');
                $tmpl -> assign('errors', array(1 => array(
                        'error' => "Can't create data directory (" . $CONFIG_DATADIRECTORY_ROOT . ")",
                        'hint' => "You can usually fix this by giving the webserver write access to the ownCloud directory '" . OC::$SERVERROOT . "' "
                    )));
                $tmpl -> printPage();
                exit ;
            }
        }
        // If we are not forced to load a specific user we load the one that is logged in
        if ($user == "" && OC_User::isLoggedIn()) {
            $user = OC_User::getUser();
        }

        if ($user != "") {//if we aren't logged in, there is no use to set up the filesystem
            //first set up the local "root" storage
            OC_Filesystem::mount('local', array('datadir' => $CONFIG_DATADIRECTORY_ROOT), '/');

            # 儲存取得的 user data dir路徑
            OC::$CONFIG_DATADIRECTORY = $CONFIG_DATADIRECTORY_ROOT . "/$user/$userDataFolder";
            # 產生使用者資料夾
            if (!is_dir(OC::$CONFIG_DATADIRECTORY)) {
                mkdir(OC::$CONFIG_DATADIRECTORY, 0755, true);
            }

            //jail the user into his "home" directory
            OC_Filesystem::chroot("/$user/$userDataFolder");

            $quotaProxy = new OC_FileProxy_Quota();
            OC_FileProxy::register($quotaProxy);
            self::$fsSetup = true;

            //emit the function that hooked
            OC_Hook::emit("OC_Util", "post_setupFS", array());
        }
    }

    public static function tearDownFS() {
        OC_Filesystem::tearDown();
        self::$fsSetup = false;
    }

    /**
     * get the current installed version of ownCloud
     * @return array
     */
    public static function getVersion() {
        return array(
            3,
            00,
            2
        );
    }

    /**
     * get the current installed version string of ownCloud
     * @return string
     */
    public static function getVersionString() {
        return '3.0.2';
    }

    /**
     * get the current installed edition of ownCloud. There is the community edition that just returns an empty string and the enterprise edition that returns "Enterprise".
     * @return string
     */
    public static function getEditionString() {
        return '';
    }

    /**
     * add a javascript file
     * @author modify by  Caro Huang
     * @param $appName APP資料夾名稱
     * @param $fileName js的檔名(不含副檔名)
     */
    public static function addScript($appName, $fileName = null) {
        if ($_SERVER['DOCUMENT_ROOT']) {
            if (is_null($fileName)) {
                # 只傳進一個參數appName,把appName當作fileName,並歸類為外連或核心js
                $fileName = $appName;
                if (strpos($fileName, 'http') === 0) {
                    self::$scripts[] = $fileName;
                    return;
                }
                $path = OC_Helper::pathForbiddenChar("/core/js/$fileName");
                if (file_exists($_SERVER['DOCUMENT_ROOT'] . $path . ".js")) {
                    self::$scripts[] = $path;
                    return;
                }
            }
            # 如果要新增的js檔存在於app裡面(例如/apps/media_streaming/js/media_convert.js)
            $path = OC_Helper::pathForbiddenChar("/apps/$appName/js/$fileName");
            if (file_exists($_SERVER['DOCUMENT_ROOT'] . $path . ".js")) {
                self::$scripts[] = $path;
                return;
            }
            # 如果要新增的js檔存在於app外面(例如/files/js/files.js)
            $path = OC_Helper::pathForbiddenChar("/$appName/js/$fileName");
            if (file_exists($_SERVER['DOCUMENT_ROOT'] . $path . ".js")) {
                self::$scripts[] = $path;
                return;
            }
            OC_Log::write('OC_Util', "addStript: $fileName.js not exists", OC_Log::ERROR);
        }
    }

    /**
     * add a javascript file
     * @author modify by  Caro Huang
     * @param $appName APP資料夾名稱
     * @param $fileName css的檔名(不含副檔名)
     */
    public static function addStyle($appName, $fileName = null) {
        if ($_SERVER['DOCUMENT_ROOT']) {
            if (is_null($fileName)) {
                # 只傳進一個參數appName,把appName當作fileName,並歸類為外連或核心css
                $fileName = $appName;
                if (strpos($fileName, 'http') === 0) {
                    self::$scripts[] = $fileName;
                    return;
                }
                $path = OC_Helper::pathForbiddenChar("/core/css/$fileName");
                if (file_exists($_SERVER['DOCUMENT_ROOT'] . $path . ".css")) {
                    self::$styles[] = $path;
                    return;
                }
            }
            $path = OC_Helper::pathForbiddenChar("/apps/$appName/css/$fileName");
            if (file_exists($_SERVER['DOCUMENT_ROOT'] . $path . ".css")) {
                self::$styles[] = "/apps/$appName/css/$fileName";
                return;
            }
            $path = OC_Helper::pathForbiddenChar("/$appName/css/$fileName");
            if (file_exists($_SERVER['DOCUMENT_ROOT'] . $path . ".css")) {
                self::$styles[] = "/$appName/css/$fileName";
                return;
            }
            OC_Log::write('OC_Util', "addStyle: $fileName.css not exists", OC_Log::ERROR);
        }
    }

    /**
     * @brief Add a custom element to the header
     * @param string tag tag name of the element
     * @param array $attributes array of attrobutes for the element
     * @param string $text the text content for the element
     */
    public static function addHeader($tag, $attributes, $text = '') {
        self::$headers[] = array(
            'tag' => $tag,
            'attributes' => $attributes,
            'text' => $text
        );
    }

    /**
     * formats a timestamp in the "right" way
     *
     * @param int timestamp $timestamp
     * @param bool dateOnly option to ommit time from the result
     */
    public static function formatDate($timestamp, $dateOnly = false) {
        if (isset($_SESSION['timezone'])) {//adjust to clients timezone if we know it
            $systemTimeZone = intval(date('O'));
            $systemTimeZone = (round($systemTimeZone / 100, 0) * 60) + ($systemTimeZone % 100);
            $clientTimeZone = $_SESSION['timezone'] * 60;
            $offset = $clientTimeZone - $systemTimeZone;
            $timestamp = $timestamp + $offset * 60;
        }
        $timeformat = $dateOnly ? 'F j, Y' : 'F j, Y, H:i';
        return date($timeformat, $timestamp);
    }

    /**
     * Shows a pagenavi widget where you can jump to different pages.
     *
     * @param int $pagecount
     * @param int $page
     * @param string $url
     * @return OC_Template
     */
    public static function getPageNavi($pagecount, $page, $url) {

        $pagelinkcount = 8;
        if ($pagecount > 1) {
            $pagestart = $page - $pagelinkcount;
            if ($pagestart < 0)
                $pagestart = 0;
            $pagestop = $page + $pagelinkcount;
            if ($pagestop > $pagecount)
                $pagestop = $pagecount;

            $tmpl = new OC_Template('', 'part.pagenavi', '');
            $tmpl -> assign('page', $page);
            $tmpl -> assign('pagecount', $pagecount);
            $tmpl -> assign('pagestart', $pagestart);
            $tmpl -> assign('pagestop', $pagestop);
            $tmpl -> assign('url', $url);
            return $tmpl;
        }
    }

    /**
     * check if the current server configuration is suitable for ownCloud
     * @return array arrays with error messages and hints
     */
    public static function checkServer() {
        $CONFIG_DATADIRECTORY_ROOT = OC_Config::getValue("datadirectory", OC::$SERVERROOT . "/data");
        $CONFIG_BACKUPDIRECTORY = OC_Config::getValue("backupdirectory", OC::$SERVERROOT . "/backup");
        $CONFIG_INSTALLED = OC_Config::getValue("installed", false);
        $errors = array();

        //check for database drivers
        if (!(is_callable('sqlite_open') or class_exists('SQLite3')) and !is_callable('mysql_connect') and !is_callable('pg_connect')) {
            $errors[] = array(
                'error' => 'No database drivers (sqlite, mysql, or postgresql) installed.<br/>',
                'hint' => ''
            );
            //TODO: sane hint
        }
        $CONFIG_DBTYPE = OC_Config::getValue("dbtype", "sqlite");
        $CONFIG_DBNAME = OC_Config::getValue("dbname", "owncloud");

        //common hint for all file permissons error messages
        $permissionsHint = "Permissions can usually be fixed by giving the webserver write access to the ownCloud directory";

        //check for correct file permissions
        if (!stristr(PHP_OS, 'WIN')) {
            $permissionsModHint = "Please change the permissions to 0770 so that the directory cannot be listed by other users.";
            $prems = substr(decoct(@fileperms($CONFIG_DATADIRECTORY_ROOT)), -3);
            if (substr($prems, -1) != '0') {
                OC_Helper::chmodr($CONFIG_DATADIRECTORY_ROOT, 0770);
                clearstatcache();
                $prems = substr(decoct(@fileperms($CONFIG_DATADIRECTORY_ROOT)), -3);
                if (substr($prems, 2, 1) != '0') {
                    $errors[] = array(
                        'error' => 'Data directory (' . $CONFIG_DATADIRECTORY_ROOT . ') is readable for other users<br/>',
                        'hint' => $permissionsModHint
                    );
                }
            }
            if (OC_Config::getValue("enablebackup", false)) {
                $prems = substr(decoct(@fileperms($CONFIG_BACKUPDIRECTORY)), -3);
                if (substr($prems, -1) != '0') {
                    OC_Helper::chmodr($CONFIG_BACKUPDIRECTORY, 0770);
                    clearstatcache();
                    $prems = substr(decoct(@fileperms($CONFIG_BACKUPDIRECTORY)), -3);
                    if (substr($prems, 2, 1) != '0') {
                        $errors[] = array(
                            'error' => 'Data directory (' . $CONFIG_BACKUPDIRECTORY . ') is readable for other users<br/>',
                            'hint' => $permissionsModHint
                        );
                    }
                }
            }
        } else {
            //TODO: permissions checks for windows hosts
        }
        if (is_dir($CONFIG_DATADIRECTORY_ROOT) and !is_writable($CONFIG_DATADIRECTORY_ROOT)) {
            $errors[] = array(
                'error' => 'Data directory (' . $CONFIG_DATADIRECTORY_ROOT . ') not writable by ownCloud<br/>',
                'hint' => $permissionsHint
            );
        }

        // check if all required php modules are present
        if (!class_exists('ZipArchive')) {
            $errors[] = array(
                'error' => 'PHP module zip not installed.<br/>',
                'hint' => 'Please ask your server administrator to install the module.'
            );
        }

        if (!function_exists('mb_detect_encoding')) {
            $errors[] = array(
                'error' => 'PHP module mb multibyte not installed.<br/>',
                'hint' => 'Please ask your server administrator to install the module.'
            );
        }
        if (!function_exists('ctype_digit')) {
            $errors[] = array(
                'error' => 'PHP module ctype is not installed.<br/>',
                'hint' => 'Please ask your server administrator to install the module.'
            );
        }

        if (file_exists(OC::$SERVERROOT . "/config/config.php") and !is_writeable(OC::$SERVERROOT . "/config/config.php")) {
            $errors[] = array(
                'error' => "Can't write into config directory 'config'",
                'hint' => "You can usually fix this by giving the webserver use write access to the config directory in owncloud"
            );
        }

        return $errors;
    }

    public static function displayLoginPage($parameters = array()) {
        if (isset($_COOKIE["username"])) {
            $parameters["username"] = $_COOKIE["username"];
        } else {
            $parameters["username"] = '';
        }
        OC_Template::printGuestPage("", "login", $parameters);
    }

    /**
     * Check if the app is enabled, send json error msg if not
     */
    public static function checkAppEnabled($app) {
        if (!OC_App::isEnabled($app)) {
            header('Location: ' . OC_Helper::linkTo('', 'index.php', true));
            exit();
        }
    }

    /**
     * Check if the user is logged in, redirects to home if not
     */
    public static function checkLoggedIn() {
        // Check if we are a user
        if (!OC_User::isLoggedIn()) {
            header('Location: ' . OC_Helper::linkTo('', 'index.php', true));
            exit();
        }
    }

    /**
     * Check if the user is a admin, redirects to home if not
     */
    public static function checkAdminUser() {
        // Check if we are a user
        self::checkLoggedIn();
        if (!OC_Group::inGroup(OC_User::getUser(), 'admin')) {
            header('Location: ' . OC_Helper::linkTo('', 'index.php', true));
            exit();
        }
    }

    /**
     * Redirect to the user default page
     */
    public static function redirectToDefaultPage() {
        if (isset($_REQUEST['redirect_url'])) {
            header('Location: ' . $_REQUEST['redirect_url']);
        } else {
            header('Location: ' . OC::$WEBROOT . '/' . OC_Appconfig::getValue('core', 'defaultpage', 'files/index.php'));
        }
        exit();
    }

    /**
     * 判斷是否強制開啟SSL, 否則重新導向
     * @author Caro Huang
     */
    static function forceSsl() {
        # redirect to https site if configured
        if (OC_Config::getValue("forcessl", false)) {
            ini_set("session.cookie_secure", "on");
            if (!isset($_SERVER['HTTPS']) or $_SERVER['HTTPS'] != 'on') {
                if (isset($_SERVER['SERVER_NAME']) && isset($_SERVER['REQUEST_URI'])) {
                    $url = "https://" . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'];
                    header("Location: $url");
                    exit();
                }
            }
        }
    }

    /**
     * 讀取更新檔(例如DB資料架構調整, config內容調整, 刪除特定檔案...esc),執行完畢後會自動刪除檔案
     * 要執行的檔案路徑為 server root/version/_update_xxxxxx.php
     * @author Caro Huang
     */
    static function loadUpdateFile() {
        $files = glob($_SERVER['DOCUMENT_ROOT'] . '/version/*', GLOB_MARK);
        foreach ($files as $updateFile) {
            # 如果是檔案的話
            if (substr($updateFile, strrpos($updateFile, '/'), 9) == '/_update_' && substr($updateFile, -4) == '.php') {
                require_once $updateFile;
                @unlink($updateFile);
            }
        }
    }

    /**
     * 更新安裝資訊(已不使用)
     * @author Caro Huang
     */
    static function updateInstallVersion() {
        if (OC_Config::getValue('installed', false)) {
            $installedVersion = OC_Config::getValue('version', '0.0.0');
            $currentVersion = implode('.', OC_Util::getVersion());
            if (version_compare($currentVersion, $installedVersion, '>')) {
                # db_structure.xml已廢棄不用
                // $result = OC_DB::updateDbFromStructure(OC::$SERVERROOT . '/db_structure.xml');
                if (!$result) {
                    echo 'Error while upgrading the database';
                    die();
                }
                OC_Config::setValue('version', implode('.', OC_Util::getVersion()));
            }
            OC_App::updateApps();
        }
    }

    /**
     * 利用Jmail寄發通知信
     * @param 發信者email,發信者名稱,目標email,標題,內容
     */
    static function sendJmail($adminEmail, $adminName, $email, $emailSubject, $emailBody) {
        if ($email) {
            # 引用Jmail原件
            if (!self::$requiredJmail) {
                define('_JEXEC', 1);
                #  Fix magic quotes.
                @ini_set('magic_quotes_runtime', 0);
                #  Maximise error reporting.
                @ini_set('zend.ze1_compatibility_mode', '0');
                error_reporting(E_ALL);
                ini_set('display_errors', 1);

                # configure from joomla for mailer use.
                require_once 'home/libraries/import.php';
                require_once 'home/configuration.php';
                self::$requiredJmail = true;
            }
            $return = JFactory::getMailer() -> sendMail($adminEmail, $adminName, $email, $emailSubject, $emailBody);
        }
    }

}
