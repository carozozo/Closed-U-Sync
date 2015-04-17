<?php
$path = OC::$SERVERROOT . '/files/settings.php';
unlink($path);

$path = OC::$SERVERROOT . '/files/ajax/list.php';
unlink($path);