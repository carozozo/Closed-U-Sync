<?php

/**
 * 線上同步用戶列表
 * 
 * 分頁參數: limit (與 sql command 同用法)
 */

// Server limited
require_once('inc_server.php');

// Init owncloud
require_once('../lib/base.php');

$sql = "
    select t1.uid userName,
           t1.hostname hostName,
           t1.chid serialNo,
           t1.syncTime lastSyncTime,
           ifnull(t1.interval, '00:00:00') intervals,
           t1.ip, count(1) counts,
           sum(ifnull(t2.`size`, 0)) totalSize
      from *PREFIX*fs_client t1
      left join *PREFIX*fs t2
        on t1.uid=t2.uid
     where t1.syncTime is not null
       and t2.deleteDate is null
     group by t1.uid, t1.hostname, t1.chid, t1.syncTime, t1.interval, t1.ip
     order by t1.syncTime desc";

if (isset($_REQUEST['limit'])) {
    $sql = $sql.' limit '.$_REQUEST['limit'];
}

$query = OC_DB::prepare($sql);
$results = $query -> execute(array());
$data = $results -> fetchAll();

echo OC_JSON::success($data);
