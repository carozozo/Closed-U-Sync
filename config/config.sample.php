<?php
$CONFIG = array(
"datadirectory" => '/var/www/html/s1/data',
"dbtype" => 'mysql',
"version" => '3.0.2',
"installedat" => '1331789098.0487',
"lastupdatedat" => '1332485928.3059',
"dbname" => 'clouds1caro',
"dbhost" => 'localhost',
"dbtableprefix" => 'oc_',
"dbuser" => 'oc_usync',
"dbpassword" => 'xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx',
"installed" => true,
"loglevel" => '0',
"maxZipInputSize" => '0',
"allowZipDownload" => true,
"forcessl" => false,
);

$CONFIG_CUSTOM = array(
"serverType" =>"s1",
"siteTitle" => 'Caro_S1', // 網站抬頭
"defaultLdapQuota" => 6442450944,
"defaultLanguage" => 'en',
"defaultTimezone" => 'Asia/Taipei',
"publicBoard" => array('最新消息' => '/var/www/html/s1/data/info',
                       '影音頻道' => '/var/www/html/s1/data/videos'
                       ),
"mediaConverterLimitTimes" => '20',//影片格式精靈預設轉檔限制次數
);
?>