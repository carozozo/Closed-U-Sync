<?php
/**
 * ownCloud - Media Streaming plugin
 *
 * @author Caro Huang
 * @copyright 2013 www.u-sync.com
 *
 * 檔案操作時，串流播放要連動執行的相關動作
 */
class OC_MediaStreaming_Hooks {

    /**
     * 檔案被rename前，Streaming要執行的動作
     * @param file system傳送過來的參數 array
     */
    static function renameItem($arguments) {
        $run = $arguments[OC_Filesystem::signal_param_run];
        try {
            self::postRenameItem($arguments);
        } catch(exception $e) {
            # streaming rename發生錯誤，設置不要處理檔案
            $run = false;
        }
    }

    /**
     * 檔案被rename後，Streaming要執行的動作
     * @param file system傳送過來的參數 array
     */
    static function postRenameItem($arguments) {
        $oldPath = $arguments[OC_Filesystem::signal_param_oldpath];
        $newPath = $arguments[OC_Filesystem::signal_param_newpath];
        if (OC_MediaStreaming::isMedia($oldPath) || OC_MediaStreaming::isMedia($newPath)) {
            $oldUserId = OC_LocalSystem::getLocalUserIdByPath($oldPath);
            $oldSourcePath = OC_LocalSystem::getLocalPath($oldPath);
            $newUserId = OC_LocalSystem::getLocalUserIdByPath($newPath);
            $newSourcePath = OC_LocalSystem::getLocalPath($newPath);
            $deviceTypeItemArr = OC_MediaStreaming_DB::getAllDeviceTypes();
            foreach ($deviceTypeItemArr as $deviceTypeItem) {
                $deviceType = $deviceTypeItem['device_type'];
                OC_MediaStreaming::renameStreaming($oldUserId, $oldSourcePath, $newUserId, $newSourcePath, $deviceType);
            }
        } else if (OC_Filesystem::is_dir($newPath)) {
            $content = OC_Files::getDirectoryContent($newPath);
            foreach ($content as $key => $file) {
                $fileName = $file -> basename;
                $oldFilePath = OC_Helper::pathForbiddenChar($oldPath . '/' . $fileName);
                $newFilePath = OC_Helper::pathForbiddenChar($newPath . '/' . $fileName);
                $arguments = array(
                    OC_Filesystem::signal_param_oldpath => $oldFilePath,
                    OC_Filesystem::signal_param_newpath => $newFilePath
                );
                self::renameItem($arguments);
            }
        }
    }

    /**
     * 檔案被copy前，Streaming要執行的動作
     * @param file system傳送過來的參數 array
     */
    static function copyItem($arguments) {
        $run = $arguments[OC_Filesystem::signal_param_run];
        try {
            self::postCopyItem($arguments);
        } catch(exception $e) {
            # streaming copy發生錯誤，設置不要處理檔案
            $run = false;
        }
    }

    /**
     * 檔案被copy後，Streaming要執行的動作
     * @param file system傳送過來的參數 array
     */
    static function postCopyItem($arguments) {
        $oldPath = $arguments[OC_Filesystem::signal_param_oldpath];
        $newPath = $arguments[OC_Filesystem::signal_param_newpath];
        if (OC_MediaStreaming::isMedia($oldPath)) {
            $oldUserId = OC_LocalSystem::getLocalUserIdByPath($oldPath);
            $oldSourcePath = OC_LocalSystem::getLocalPath($oldPath);
            $newUserId = OC_LocalSystem::getLocalUserIdByPath($newPath);
            $newSourcePath = OC_LocalSystem::getLocalPath($newPath);
            $deviceTypeItemArr = OC_MediaStreaming_DB::getAllDeviceTypes();
            foreach ($deviceTypeItemArr as $deviceTypeItem) {
                $deviceType = $deviceTypeItem['device_type'];
                OC_MediaStreaming::copyStreaming($oldUserId, $oldSourcePath, $newUserId, $newSourcePath, $deviceType);
            }
        } else if (OC_Filesystem::is_dir($oldPath)) {
            $content = OC_Files::getDirectoryContent($oldPath);
            foreach ($content as $key => $file) {
                $fileName = $file -> basename;
                $oldFilePath = OC_Helper::pathForbiddenChar($oldPath . '/' . $fileName);
                $newFilePath = OC_Helper::pathForbiddenChar($newPath . '/' . $fileName);
                $arguments = array(
                    OC_Filesystem::signal_param_oldpath => $oldFilePath,
                    OC_Filesystem::signal_param_newpath => $newFilePath
                );
                self::copyItem($arguments);
            }
        }
    }

    /**
     * 檔案要delete前，Streaming要執行的動作
     * @param file system傳送過來的參數 array
     */
    static function deleteItem($arguments) {
        $run = $arguments[OC_Filesystem::signal_param_run];
        try {
            self::postDeleteItem($arguments);
        } catch(exception $e) {
            # streaming delete發生錯誤，設置不要處理檔案
            $run = false;
        }
    }

    /**
     * 檔案delete後，Streaming要執行的動作
     * @param file system傳送過來的參數 array
     */
    static function postDeleteItem($arguments) {
        $path = $arguments[OC_Filesystem::signal_param_path];
        if (OC_MediaStreaming::isMedia($path)) {
            $userId = OC_LocalSystem::getLocalUserIdByPath($path);
            $path = OC_LocalSystem::getLocalPath($path);
            $deviceTypeItemArr = OC_MediaStreaming_DB::getAllDeviceTypes();
            foreach ($deviceTypeItemArr as $deviceTypeItem) {
                $deviceType = $deviceTypeItem['device_type'];
                OC_MediaStreaming::deleteStreaming($userId, $path, $deviceType);
            }
        } else if (OC_Filesystem::is_dir($path)) {
            $content = OC_Files::getDirectoryContent($path);
            foreach ($content as $key => $file) {
                $fileName = $file -> basename;
                $filePath = OC_Helper::pathForbiddenChar($path . '/' . $fileName);
                $arguments = array(OC_Filesystem::signal_param_path => $filePath);
                self::deleteItem($arguments);
            }
        }
    }

    /**
     * 當上傳同檔名的檔案，而舊檔被覆蓋時，則刪除舊檔的streaming資料
     * 目前被蓋檔時會執行Recycle(delete)的hooks動作
     * [OC_Recycle_Hooks::moveToRecycleBeforeFromUploadedFile]，所以此function會無作用
     * @param file system傳送過來的參數 array
     */
    static function deleteItemAfterUploadedFile($arguments) {
        $path = $arguments[OC_Filesystem::signal_param_path];
        if (OC_MediaStreaming::isMedia($path))
            OC_MediaStreaming::deleteStreaming($path);
    }

}
?>