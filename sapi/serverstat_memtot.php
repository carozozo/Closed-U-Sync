<?php

// Server limited
require_once('inc_server.php');

// notice: unit - k
$memory_total = exec('top -b -n 1 -d 1 -p 1 |grep \'Mem:\'|perl -ne \'/(\d+)k total/; print $1."\n";\'');

$result = array('memory_total' => $memory_total, 'status' => 'success');

echo json_encode($result);
