<?php

// Server limited
require_once('inc_server.php');

$cpu_idle = exec('top -b -n 2 -d 1 -p 1 |grep \'%id\'|perl -ne \'if ($.==2){/(\d+\.\d)%id/; print $1."\n";}\'');

$result = array('cpu_idle' => $cpu_idle, 'status' => 'success');

echo json_encode($result);