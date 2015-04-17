<?php

// Server limited
require_once('inc_server.php');

//exec('sar -P ALL 2 1|grep Average|cut -c 74-79');

$arrayCPUs = explode("\t",exec(' sar -P ALL 2 1|grep Average|cut  -c 74-79 |grep -v \'%idle\'| awk \'{printf $1 "\t"}\''));

echo json_encode($arrayCPUs);