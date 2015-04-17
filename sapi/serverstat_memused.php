<?php

// Server limited
require_once('inc_server.php');

// notice: unit - k
$memory_used = exec('top -b -n 1 -d 1 -p 1 |grep \'Mem:\'|perl -ne \'/(\d+)k used/; print $1."\n";\'');

$result = array('memory_used' => $memory_used, 'status' => 'success');

echo json_encode($result);
