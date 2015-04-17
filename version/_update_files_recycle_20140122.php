<?php
$path = OC::$SERVERROOT . '/apps/files_recycle/recycle_manager.php';
@unlink($path);

$path = OC::$SERVERROOT . '/apps/files_recycle/ajax/recycle.php';
@unlink($path);

$path = OC::$SERVERROOT . '/apps/files_recycle/ajax/recycle_manager.php';
@unlink($path);

$path = OC::$SERVERROOT . '/apps/files_recycle/css/recycle_manager.css';
@unlink($path);

$path = OC::$SERVERROOT . '/apps/files_recycle/js/recycle.js';
@unlink($path);

$path = OC::$SERVERROOT . '/apps/files_recycle/js/recycle_manager.js';
@unlink($path);

$path = OC::$SERVERROOT . '/apps/files_recycle/lib/recycle_hooks.php';
@unlink($path);

$path = OC::$SERVERROOT . '/apps/files_recycle/lib/recycle_manager.php';
@unlink($path);

try {
    $query = OC_DB::prepare("ALTER TABLE `*PREFIX*recycle` DROP PRIMARY KEY;");
    $result = $query -> execute();
} catch(exception $e) {
}
$path = OC::$SERVERROOT . '/apps/files_recycle/templates/recycle_manager.php';
@unlink($path);

try {
    $query = OC_DB::prepare("ALTER TABLE `*PREFIX*recycle` ADD COLUMN `sn` INT NOT NULL AUTO_INCREMENT FIRST, ADD PRIMARY KEY (`sn`);");
    $result = $query -> execute();
} catch(exception $e) {
}

try {
    $query = OC_DB::prepare("ALTER TABLE *PREFIX*recycle CHANGE COLUMN `userId` `uid` VARCHAR(50) NOT NULL AFTER `sn`;");
    $result = $query -> execute();
} catch(exception $e) {
}

try {
    $query = OC_DB::prepare("ALTER TABLE *PREFIX*recycle CHANGE COLUMN `origin_path` `source_path` VARCHAR(800) NOT NULL AFTER `uid`;");
    $result = $query -> execute();
} catch(exception $e) {
}

try {
    $query = OC_DB::prepare("ALTER TABLE *PREFIX*recycle CHANGE COLUMN `recycled_date` `recycle_time` DATETIME NOT NULL AFTER `source_path`;");
    $result = $query -> execute();
} catch(exception $e) {
}
