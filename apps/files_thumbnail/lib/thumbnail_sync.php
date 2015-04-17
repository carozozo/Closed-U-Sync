<?php
/**
 * ownCloud - Thumbnail plugin
 *
 * @author Caro Huang
 * @copyright 2013 www.u-sync.com
 *
 * 縮圖同步處理
 */
class OC_Thumbnail_Sync extends OC_Thumbnail {

    /**
     * 縮圖同步
     */
    static function thumbsSync() {
        # 先比對資料夾再比對資料
        if (self::compareFolder() && self::compareFile()) {
            $thumbFileList = OC_Thumbnail_DB::selectThumbDataInDir(self::$localUserId, self::$localDirPath);
            return $thumbFileList;
        }
    }

    /**
     * 比對資料夾和DB的縮圖資料(清除不需要的縮圖資料)
     */
    private static function compareFolder() {
        $folderList = self::getFolderList(self::$localDirFullPath);
        $thumbsInDB = OC_Thumbnail_DB::selectThumbDataInSubDir(self::$localUserId, self::$localDirPath);
        foreach ($thumbsInDB as $thumb) {
            $path = $thumb['path'];
            $dirPath = dirname($path);
            $dir = substr($dirPath, strrpos($dirPath, '/') + 1);
            if (!in_array($dir, $folderList)) {
                OC_Thumbnail_DB::deleteThumbDataByDir(self::$localUserId, self::$localDirPath);
            }
        }
        return true;
    }

    /**
     * 比對使用者檔案和縮圖的所有資料
     */
    private static function compareFile() {
        if (self::deleteFileAfterCompare() && self::createFileAfterCompare()) {
            return true;
        }
        return false;
    }

    /**
     * 比對後，清除不需要的縮圖
     */
    private static function deleteFileAfterCompare() {
        $thumbFileList = OC_Thumbnail_DB::selectThumbDataInDir(self::$localUserId, self::$localDirPath);
        $fileList = self::getFileList();
        $fileNeedDeleteArray = self::findFileThatNeedDelete($fileList, $thumbFileList);
        foreach ($fileNeedDeleteArray as $thumbFile) {
            $path = $thumbFile['path'];
            $dirPath = dirname($path);
            $fileName = basename($path);
            $thumbnailObj = new OC_Thumbnail($dirPath, $fileName);
            $thumbnailObj -> deleteThumb('file');
        }
        return true;
    }

    /**
     * 比對後，新增需要的縮圖
     */
    private static function createFileAfterCompare() {
        $thumbFileList = OC_Thumbnail_DB::selectThumbDataInDir(self::$localUserId, self::$localDirPath);
        $fileList = self::getFileList(true);
        $fileNeedCreateArray = self::findFileThatNeedCreate($fileList, $thumbFileList);
        foreach ($fileNeedCreateArray as $file) {
            self::createThumb($file);
        }
        return true;
    }

    /**
     * 找出需要刪除的縮圖資料
     * @param 實體檔案array,縮圖資料array
     */
    private static function findFileThatNeedDelete($fileList, $thumbFileList) {
        # 移除和資料列表相同的縮圖資料，剩下的就是多餘的縮圖
        foreach ($thumbFileList as $index => $thumbFile) {
            $path = $thumbFile['path'];
            $name = basename($path);
            if (in_array($name, $fileList)) {
                unset($thumbFileList[$index]);
            }
        }
        return $thumbFileList;
    }

    /**
     * 找出需要新增的縮圖資料
     * @param 實體檔案array,縮圖資料array
     */
    private static function findFileThatNeedCreate($fileList, $thumbFileList) {
        # 使用者資料夾內的資料列表，只要和縮圖資料相同的就移除(剩下的就是需要新增的)
        foreach ($fileList as $key => $file) {
            foreach ($thumbFileList as $thumbFile) {
                $path = $thumbFile['path'];
                $name = basename($path);
                $size = $thumbFile['size'];
                if ($file['name'] == $name && $file['size'] == $size) {
                    unset($fileList[$key]);
                }
            }
        }
        return $fileList;
    }

    /**
     * 指定資料夾底下的所有資料夾
     * @param 資料夾完整路徑
     */
    private static function getFolderList($localDirFullPath) {
        $folderList = array();
        $files = glob($localDirFullPath . '/*', GLOB_MARK);
        foreach ($files as $file) {
            # 如果是資料夾的話
            if (substr($file, -1) == '/') {
                $folderList[] = basename($file);
            }
        }
        return $folderList;
    }

    /**
     * 指定資料夾底下的所有檔案
     * @param 是否取得詳細內容
     */
    private static function getFileList($withDetail = false) {
        $files = OC_Files::getDirectoryContent(self::$dirPath);
        $fileList = array();
        foreach ($files as $key => $file) {
            $newFile = array();
            $mime = $file -> mime;
            if (self::ifNeedToCreateThumbs($mime)) {
                if ($withDetail) {
                    $newFile['name'] = $file -> basename;
                    $newFile['size'] = $file -> size;
                    $newFile['date'] = $file -> mtimeHuman;
                    $newFile['mime'] = $mime;
                    $fileList[] = $newFile;
                } else {
                    $fileList[] = $file -> basename;
                }
            }
        }
        return $fileList;
    }

}
