<?php
class OC_U_Drive_Hooks {

    static function post_createItem($argument) {
        $path = $argument[OC_Filesystem::signal_param_path];
        // OC_Log::write('OC_U_Drive_Hooks createItem', '$path=' . $path, 1);
        $userId = OC_USER::getUser();
        $fileName = basename($path);
        $extenstion = OC_Filesystem::getExtension($path);
        // OC_Log::write('underDataDir', OC_U_Drive::ifUnderDataDir($path), 1);
        if (OC_U_Drive::ifUnderDataDir($path) && $extenstion != 'usync') {
            OC_U_Drive::createItem($userId, $path);
        }
        return true;
    }

    static function post_writeItem($argument) {
        $path = $argument[OC_Filesystem::signal_param_path];
        $userId = OC_USER::getUser();
        if (OC_U_Drive::ifUnderDataDir($path)) {
            OC_U_Drive::writeItem($userId, $path);
        }
        return true;
    }

    static function post_deleteItem($argument) {
        $path = $argument[OC_Filesystem::signal_param_path];
        $fileType = $argument[OC_Filesystem::signal_param_fileType];
        $userId = OC_USER::getUser();
        // OC_Log::write('OC_U_Drive_Hooks deleteItem', '$path=' . $path.', $fileType=' . $fileType . ', $userId=' . $userId, 1);OC_Log::write('OC_U_Drive_Hooks deleteItem', '$path=' . $path.', $fileType=' . $fileType . ', $userId=' . $userId, 1);
        if (OC_U_Drive::ifUnderDataDir($path)) {
            OC_U_Drive::deleteItem($userId, $path, $fileType);
        }
        return true;
    }

    # 更名/移動前,要執行的動作
    static function renameItem($argument) {
        $oldPath = $argument[OC_Filesystem::signal_param_oldpath];
        $userId = OC_USER::getUser();
        if (OC_U_Drive::ifUnderDataDir($oldPath)) {
            OC_U_Drive::deleteItem($userId, $oldPath);
        }
        return true;
    }

    # 更名/移動後,要執行的動作
    static function post_renameItem($argument) {
        $newPath = $argument[OC_Filesystem::signal_param_newpath];
        $userId = OC_USER::getUser();
        if (OC_U_Drive::ifUnderDataDir($newPath)) {
            OC_U_Drive::createItem($userId, $newPath);
        }
        return true;
    }

    # 複製後,要執行的動作
    static function post_copyItem($argument) {
        $path = $argument[OC_Filesystem::signal_param_newpath];
        $userId = OC_USER::getUser();
        if (OC_U_Drive::ifUnderDataDir($path)) {
            OC_U_Drive::createItem($userId, $path);
        }
        return true;
    }

}
