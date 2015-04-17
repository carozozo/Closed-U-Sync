<?php
/**
 * ownCloud
 *
 * @author Caro Huang
 * @copyright 2013 www.u-sync.com
 *
 * 負責檔案的壓縮操作
 */
class OC_Files_Zip {

    public static function zipAddDir($dir, $zip, $internalDir = '') {
        $dirname = basename($dir);
        $zip -> addEmptyDir(iconv('UTF-8', 'big5', $internalDir . $dirname));
        $internalDir .= $dirname .= '/';
        $files = OC_Files::getdirectorycontent($dir);
        foreach ($files as $file) {
            $fileName = $file -> basename;
            $filePath = $file -> path;
            if (OC_Filesystem::is_file($filePath)) {
                $tmpFile = OC_Filesystem::toTmpFile($filePath);
                OC_Files::$tmpFiles[] = $tmpFile;
                $zip -> addFile($tmpFile, iconv('UTF-8', 'big5', $internalDir . $fileName));
            } elseif (OC_Filesystem::is_dir($filePath)) {
                self::zipAddDir($filePath, $zip, $internalDir);
            }
        }
    }

    /**
     * checks if the selected files are within the size constraint. If not, outputs an error page.
     *
     * @param dir   $dir
     * @param files $files
     */
    static function validateZipDownload($dir, $files) {
        if (!OC_Config::getValue('allowZipDownload', true)) {
            $l = new OC_L10N('files');
            header("HTTP/1.0 409 Conflict");
            $tmpl = new OC_Template('', 'error', 'user');
            $errors = array( array(
                    'error' => $l -> t('ZIP download is turned off.'),
                    'hint' => $l -> t('Files need to be downloaded one by one.') . '<br/><a href="javascript:history.back()">' . $l -> t('Back to Files') . '</a>',
                ));
            $tmpl -> assign('errors', $errors);
            $tmpl -> printPage();
            exit ;
        }

        $zipLimit = OC_Config::getValue('maxZipInputSize', OC_Helper::computerFileSize('800 MB'));
        if ($zipLimit > 0) {
            $totalsize = 0;
            if (is_array($files)) {
                foreach ($files as $file) {
                    $totalsize += OC_Filesystem::filesize($dir . '/' . $file);
                }
            } else {
                $totalsize += OC_Filesystem::filesize($dir . '/' . $files);
            }
            if ($totalsize > $zipLimit) {
                $l = new OC_L10N('files');
                header("HTTP/1.0 409 Conflict");
                $tmpl = new OC_Template('', 'error', 'user');
                $errors = array( array(
                        'error' => $l -> t('Selected files too large to generate zip file.'),
                        'hint' => 'Download the files in smaller chunks, seperately or kindly ask your administrator.<br/><a href="javascript:history.back()">' . $l -> t('Back to Files') . '</a>',
                    ));
                $tmpl -> assign('errors', $errors);
                $tmpl -> printPage();
                exit ;
            }
        }
    }

}
