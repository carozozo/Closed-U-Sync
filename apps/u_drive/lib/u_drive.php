<?php

class OC_U_Drive {
    static function getDataDir() {
        $dataDir = '/' . OC_Appconfig::getValue('u_drive', 'dataDir', 'U-DRIVE');
        return $dataDir = OC_Helper::pathForbiddenChar($dataDir);
    }

    static function createDataDir() {
        try {
            $dataDir = self::getDataDir();
            if (!OC_Filesystem::file_exists($dataDir)) {
                $createDir = preg_replace('#\/#', '', $dataDir);
                OC_Files::newFile('', $createDir, 'dir');
            }

            # 指定同步資料夾只在 copy/move 的選單內才顯示
            OC_Files::addShowPath($dataDir, array(
                'FilesCopy',
                'FilesMv',
            ));

            # 自訂要顯示的目錄名稱
            $l = new OC_L10N('u_drive');
            $dataDirName = $l -> t('U-Drive Folder');
            OC_Files::addMarkFileName($dataDir, $dataDirName, array(
                'FilesCopy',
                'FilesMv',
                'Breadcrumb',
            ));

            //在webDav中，設定同步資料夾無法更名/移動
            $webDav_FS_Plugin = new OC_Connector_Sabre_FileSystemPlugin();
            $webDav_FS_Plugin -> addRejectMovePath($dataDir);
            return $dataDir;
        } catch(exception $e) {
            OC_Log::writeException('OC_U_Drive', 'createDataDir', $e);
            return false;
        }
    }

    static function ifUnderDataDir($path) {
        try {
            $dataDir = self::getDataDir();
            //如果傳進來的路徑不是u-drive的資料根目錄
            if ($path != $dataDir) {
                $pos = stripos($path, $dataDir);
                if ($pos === 0)
                    return true;
            }
            return false;
        } catch(exception $e) {
            OC_Log::writeException('OC_U_Drive', 'ifUnderDataDir', $e);
            return false;
        }
    }

    static function getFileInfo($path) {
        try {
            $file = array();
            // $fileFullPath = OC_LocalSystem::getLocalFullPath($path);
            $file['filename'] = preg_replace('#' . self::getDataDir() . '#', '', $path, 1);
            // if (OC_Filesystem::file_exists($path)) {
            $file['type'] = OC_Filesystem::filetype($path);
            if ($file['type'] == 'dir') {
                //是資料夾的話，則檔名後面加上斜線
                $file['filename'] = $file['filename'] . '/';
            }
            $file['size'] = OC_Filesystem::filesizeWithoutFolder($path);
            $file['md5'] = md5_file(OC_LocalSystem::getLocalFullPath($path));
            $filemtime = OC_Filesystem::filemtime($path);
            $date = new DateTime("@$filemtime", new DateTimeZone('UTC'));
            $file['mdate'] = $date -> format('Y-m-d H:i:s');
            // OC_Log::write('filename', $file['filename'], 1);
            // OC_Log::write('type', $file['type'], 1);
            // OC_Log::write('size', $file['size'], 1);
            // OC_Log::write('md5', $file['md5'], 1);
            // OC_Log::write('mdate', $file['mdate'], 1);
            // }
            return $file;
        } catch(exception $e) {
            OC_Log::writeException('OC_U_Drive', 'getFileInfo', $e);
            return false;
        }
    }

    static function createItem($userId = null, $path) {
        try {
            $userId = OC_User::getUserByUserInput($userId);
            $file = self::getFileInfo($path);
            if ($file) {
                OC_U_Drive_DB::insertItem($userId, $file['filename'], $file['type'], $file['size'], $file['md5'], $file['mdate']);
                if ($file['type'] == 'dir') {
                    $files = OC_Files::getDirectoryContent($path);
                    foreach ($files as $file) {
                        $filePath = $file -> path;
                        self::createItem($userId, $filePath);
                    }
                }
                return true;
            }
            return false;
        } catch(exception $e) {
            OC_Log::writeException('OC_U_Drive', 'createItem', $e);
            return false;
        }
    }

    static function writeItem($userId = null, $path) {
        try {
            $userId = OC_User::getUserByUserInput($userId);
            $file = self::getFileInfo($path);
            if ($file) {
                OC_U_Drive_DB::updateItem($file['type'], $file['size'], $file['md5'], $file['mdate'], $userId, $file['filename']);
                return true;
            }
            return false;
        } catch(exception $e) {
            OC_Log::writeException('OC_U_Drive', 'createItem', $e);
            return false;
        }
    }

    static function deleteItem($userId = null, $path, $fileType = 'dir') {
        try {
            $userId = OC_User::getUserByUserInput($userId);
            // $file = self::getFileInfo($path);
            $fileName = preg_replace('#' . self::getDataDir() . '#', '', $path, 1);
            if ($fileType == 'dir') {
                $fileName .= '/';
            }
            // OC_Log::write('OC_U_Drive deleteItem', '$path=' . $path . ', $fileName=' . $fileName, 1);
            $deleteDate = OC_Helper::formatDateTimeLocalToUTC();
            OC_U_Drive_DB::deleteItem($deleteDate, $userId, $fileName);
            return true;
        } catch(exception $e) {
            OC_Log::writeException('OC_U_Drive', 'deleteItem', $e);
            return false;
        }
    }

}
