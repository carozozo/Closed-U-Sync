<?php

OC_Util::addScript('getfile','control');
OC_Util::addStyle('getfile','getfile');
OC_APP::register(array(
	'order' => 90,
	'id' => 'getfile',
	'name' => 'Get File'
	));
?>