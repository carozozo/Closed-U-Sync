<?php
$path = OC::$SERVERROOT . '/apps/files_mv/ajax/autocompletedir.php';
unlink($path);

$path = OC::$SERVERROOT . '/apps/files_mv/ajax/move.php';
unlink($path);

$path = OC::$SERVERROOT . '/apps/files_mv/css/mv.css';
unlink($path);

$path = OC::$SERVERROOT . '/apps/files_mv/js/move.js';
unlink($path);