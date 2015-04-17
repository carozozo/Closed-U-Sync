<?php
@unlink(OC::$SERVERROOT . '/apps/files_publicshare/img/publicshare_manager.png');
@unlink(OC::$SERVERROOT . '/apps/files_publicshare/img/publicshare_manager.svg');

@unlink(OC::$SERVERROOT . '/apps/files_publicshare/img/public.png');
@unlink(OC::$SERVERROOT . '/apps/files_publicshare/img/public.svg');

@unlink(OC::$SERVERROOT . '/apps/files_publicshare/img/share.svg');
@unlink(OC::$SERVERROOT . '/apps/files_publicshare/img/shared.svg');
@unlink(OC::$SERVERROOT . '/apps/files_publicshare/img/manager.svg');

@unlink(OC::$SERVERROOT . '/apps/files_publicshare/ad/img/ad.png');
@unlink(OC::$SERVERROOT . '/apps/files_publicshare/ad/img/get_link.png');
@unlink(OC::$SERVERROOT . '/apps/files_publicshare/ad/img/icon_blog.png');
@unlink(OC::$SERVERROOT . '/apps/files_publicshare/ad/img/icon_fb.png');


@unlink(OC::$SERVERROOT . '/apps/files_publicshare/ajax/publicshare_manager.php');
@unlink(OC::$SERVERROOT . '/apps/files_publicshare/ajax/publicshare_settings.php');

@unlink(OC::$SERVERROOT . '/apps/files_publicshare/lib/publicshare_db.php');
@unlink(OC::$SERVERROOT . '/apps/files_publicshare/lib/publicshare_hooks.php');
@unlink(OC::$SERVERROOT . '/apps/files_publicshare/lib/publicshare_settings.php');

@unlink(OC::$SERVERROOT . '/apps/files_publicshare/css/publicshare_manager.css');
@unlink(OC::$SERVERROOT . '/apps/files_publicshare/css/publicshare_settings.css');

@unlink(OC::$SERVERROOT . '/apps/files_publicshare/js/publicshare_manager.js');
@unlink(OC::$SERVERROOT . '/apps/files_publicshare/js/publicshare_settings.js');

@unlink(OC::$SERVERROOT . '/apps/files_publicshare/templates/publicshare_manager.php');
@unlink(OC::$SERVERROOT . '/apps/files_publicshare/templates/publicshare_settings.php');

@unlink(OC::$SERVERROOT . '/apps/files_publicshare/publicshare_manager.php');
@unlink(OC::$SERVERROOT . '/apps/files_publicshare/publicshare_settings.php');

@unlink(OC::$SERVERROOT . '/apps/files_publicshare/get_file.php');


OC_Helper::deleteDirByFullPath(OC::$SERVERROOT . '/apps/files_publicshare/img/u-sync_ad');


try {
    $quStr = "ALTER TABLE `*PREFIX*files_publicshare` CHANGE COLUMN `uid_owner` `uid` VARCHAR(64) NOT NULL DEFAULT '' FIRST;";
    $query = OC_DB::prepare($quStr);
    $query -> execute();
} catch(exception $e) {
}

try {
    $quStr = 'ALTER TABLE `*PREFIX*files_publicshare` CHANGE COLUMN `source` `source_path` VARCHAR(800) NOT NULL AFTER `uid`;';
    $query = OC_DB::prepare($quStr);
    $query -> execute();
} catch(exception $e) {
}

try {
    $quStr = 'ALTER TABLE `*PREFIX*files_publicshare` ADD COLUMN `pwd` VARCHAR(12) NULL AFTER `token`;';
    $query = OC_DB::prepare($quStr);
    $query -> execute();
} catch(exception $e) {
}

try {
    $quStr = 'ALTER TABLE `*PREFIX*files_publicshare` CHANGE COLUMN `createDate` `insert_time` DATETIME NOT NULL AFTER `pwd`;';
    $query = OC_DB::prepare($quStr);
    $query -> execute();
} catch(exception $e) {
}

try {
    $quStr = 'ALTER TABLE `*PREFIX*files_publicshare` ADD COLUMN `update_time` DATETIME NULL AFTER `insert_time`;';
    $query = OC_DB::prepare($quStr);
    $query -> execute();
} catch(exception $e) {
}
