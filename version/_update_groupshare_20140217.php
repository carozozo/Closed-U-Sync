<?php
try {
    $quStr = 'ALTER TABLE `*PREFIX*groupshare` CHANGE COLUMN `gids` `gids` VARCHAR(800) NULL DEFAULT NULL AFTER `source`, CHANGE COLUMN `uids` `uids` VARCHAR(800) NULL DEFAULT NULL AFTER `gids`;';
    $query = OC_DB::prepare($quStr);
    $query -> execute();
} catch(exception $e) {
}
