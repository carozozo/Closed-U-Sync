<?php

/**
 * sync check API
 */

// security issue, if client is leak, put client's ip in $arrayBanIP to stop sync check.
$arrayBanIP = array();
if (in_array($_SERVER["REMOTE_ADDR"], $arrayBanIP))
{
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST')
{
	$postText = rawurldecode(file_get_contents('php://input'));
}
else
{
	exit;
}

//Note: sync can not work in https(ssl), so can not include OwnCloud Class [/lib/base.php]
require_once ($_SERVER['DOCUMENT_ROOT'] . '/config/config.php');
require_once "inc_db.php";
require_once "inc_func.php";

$debugMode = false;

$client = json_decode($postText,1);
$username = $client['userID'];
$syncTime = gmdate("Y-m-d H:i:s"); // current UTC time

mysql_query("SET NAMES 'utf8'");
mysql_query("SET CHARACTER_SET_CLIENT=utf8");
mysql_query("SET CHARACTER_SET_RESULTS=utf8"); 

$sql = "select configvalue from ".$prefix."appconfig where appId = 'u_drive' and configkey = 'dataDir' limit 1";
$dataset = mysql_query($sql);
$result = mysql_fetch_array($dataset);
if (isset($result['configvalue']))
{
    $dataDir = $result['configvalue'];
}
else
{   // if server not define the sync folder then stop syncing.
	exit;
}

if (    ($username == 'synchk')
    and ($debugMode == true) )
{
     writeLog($username.'-from-client', $postText);
}

/*
 * client information
 */

$hostname = isset($client['hostname']) ? $client['hostname'] : '';
$cpuid = isset($client['cupID']) ? $client['cupID'] : '';

if (   trim($hostname) == ''
    or trim($cpuid)    == ''
    or trim($username) == ''
   ) 
{
    exit;
}

$sql = "select chid, syncTime from $prefix"."fs_client where uid='".$username."' and hostname='".mysql_real_escape_string($hostname)."' and cpuid='".$cpuid."'";
$dataset = mysql_query($sql);
$chid = 0;
// if client's information not exist, then update or insert
if (!(list($chid, $last_syncTime) = mysql_fetch_array($dataset)))
{
	$sql = "select chid, hostname from $prefix"."fs_client where uid='".$username."' and cpuid='".$cpuid."'";
	$dataset = mysql_query($sql);

	// the hostname change by user
	if (list($chid, $oldhostname) = mysql_fetch_array($dataset))
	{
		$sql = "update $prefix"."fs_client set hostname='".mysql_real_escape_string($hostname)."' where uid='".$username."' and cpuid='".$cpuid."'";
        mysql_query($sql);
	}
	// first time sync
	else
	{
		$sql = "insert into $prefix"."fs_client (uid, hostname, cpuid, syncTime) values ('".$username."', '".mysql_real_escape_string($hostname)."', '".$cpuid."', '".$syncTime."')";
		if (mysql_query($sql))
		{
			$sql = "select chid from $prefix"."fs_client where uid='".$username."' and cpuid='".$cpuid."'";
			$dataset = mysql_query($sql);
			list($chid) = mysql_fetch_array($dataset);
		}

		if ($chid==0) exit;
	}
}

// update the client information
$sql = "update $prefix"."fs_client set ip='".mysql_real_escape_string($_SERVER["REMOTE_ADDR"])."', `interval`=timediff('".$syncTime."', '".$last_syncTime."'), syncTime='".$syncTime."' where chid='".$chid."'";
mysql_query($sql);


/*
 * the server side
 */

$dir = $CONFIG_MAIN["CONFIG"]["datadirectory"].'/'.$username.'/files/'.$dataDir;

// user's sync path exist?
if (!file_exists($dir))
{
	mkdir($dir);
}

$children = array();
// writeLog($username.'-hostname', $hostname.'-'.$cpuid);

function getDirectory( $path = '.' )
{
	global $dir;
	global $children;
	// Directories to ignore when listing output.
	$ignore = array( '.', '..', '.thumbs');

	// Open the directory to the handle $dh
	$dh = @opendir( $path );

	// Loop through the directory
	while( false !== ( $file = readdir( $dh ) ) )
	{
		// Check that this file is not to be ignored
		if( !in_array( $file, $ignore ) )
		{
			// delete upload temporary file else keep
			$ext = substr($file, -6);
			if ($ext == '.usync')
			{
				$tmp_file = $path.'/'.$file;
				//writeLog($username.'-tmpfile', strtotime(gmdate("Y-m-d H:i:s")).'-'.filemtime($tmp_file).'='.(strtotime(gmdate("Y-m-d H:i:s"))-filemtime($tmp_file)));
				// lastModified > 5 min. then delete
				if ( (strtotime(date("Y-m-d H:i:s"))-filemtime($tmp_file)) > 300 ) unlink($tmp_file);
			}
			else
			{
				if (!strpos($file, '.thumbs'))
				{
					// normal file & directory
					$child['filename'] = str_replace( $dir, '', "$path/$file");	// change to user's relative path
					$child['lastModified'] = date("Y-m-d H:i:s",filemtime($path.'/'.$file));

					if( is_dir( "$path/$file" ) )
					{
						getDirectory( "$path/$file" );
						$child['filename']  .= '/';
						$child['type'] = 'dir';
						$child['size'] = 0;
						$children[] = $child;
					}
					else
					{
						$child['type'] = 'file';
						$child['size'] = filesize($path.'/'.$file);
						$children[] = $child;
					}
				}
			}
		}
	}
	// Close the directory handle
	closedir( $dh );
}

function ExistPath($strFile)
{
	global $username;
	global $prefix;
	
	$tmp = dirname($strFile).'/';

	if ($tmp == '//') return '/';

	$sql = "select filename from $prefix"."fs where uid='".$username."' and filename='".$tmp."'";
	$dataset = mysql_query($sql);

	if (list($result) = mysql_fetch_array($dataset))
	{
		return $result;
	}
	else
	{
		return ExistPath($tmp);
	}
}

function rrmdir($dir) {
    if (is_dir($dir)) {
        $objects = scandir($dir);
        foreach ($objects as $object) {
            if ($object != "." && $object != "..") {
                if (filetype($dir."/".$object) == "dir") rrmdir($dir."/".$object); else unlink($dir."/".$object);
            }
        }
     reset($objects);
     rmdir($dir);
    }
}

if (is_dir($dir))
{
	// writeLog($username.'-synchk-get', $postText);
	
	date_default_timezone_set("UTC");
	$fileactsize=512000;	// local copy, rename or move file size limit
	
	$start_time = microtime(true);

	/*
	 * start build array $children from physical disk and update to database
	 */
	$children = array();
	getDirectory($dir);
	
	// check if sync before
	$sql = "select count(1) cnt from $prefix"."fs where uid='".$username."'";
	$dataset = mysql_query($sql);
	list($cnt) = mysql_fetch_array($dataset);
	
	mysql_query("SET NAMES 'utf8'");
	mysql_query("SET CHARACTER_SET_CLIENT=utf8");
	mysql_query("SET CHARACTER_SET_RESULTS=utf8"); 

	// update server side database
	if ($cnt == 0)
	{
		// insert init filetree to db
		if (is_array($children))
		{
			foreach($children as $child)
			{
				$key = $child['filename'];
				$sql = "insert into $prefix"."fs (uid, filename, lastModified, type, size, md5) values ('".$username."','".mysql_real_escape_string($key)."','".$child['lastModified']."','".$child['type'] ."','".$child['size'] ."','".md5_file($dir.$key)."')";
				mysql_query($sql);
			}
		}
	}
	else
	{
		// read last sync record from db, check which file record shoud be update(delete, lastModified, size).
		$sql = "select filename, lastModified, type, size, deleteDate from $prefix"."fs where uid='".$username."' order by `type`, filename";
		$dataset = mysql_query($sql);
		while ($row = mysql_fetch_assoc($dataset)) {
			$lastchildren[] = $row;
		}

		// change the array strucrure to have the key, in order to be controled. (db)
		$db_sets = array();
		$server_deleted_sets = array();
		foreach($lastchildren as $child)
		{
			$key = $child['filename'];
			$arraykey = strtolower($key);
			$db_sets["$arraykey"]['lastModified'] = $child['lastModified'];
			$db_sets["$arraykey"]['type'] = $child['type'];
			$db_sets["$arraykey"]['size'] = (int)$child['size'];
			$db_sets["$arraykey"]['displayName'] = $key;
			// use another array to cache the deleteDate from db
			if (!is_null($child['deleteDate']))
			{
				$server_deleted_sets["$arraykey"] = strtotime($child['deleteDate']);
			}
		}
		// writeLog($username.'-server_deleted_sets',json_encode($server_deleted_sets));

		// change the array strucrure (physical disk)
		$disk_sets = array();
		foreach($children as $child)
		{
			$key = $child['filename'];
			$arraykey = strtolower($key);
			$disk_sets["$arraykey"]['lastModified'] = $child['lastModified'];
			$disk_sets["$arraykey"]['type'] = $child['type'];
			$disk_sets["$arraykey"]['size'] = (int) $child['size'];
			$disk_sets["$arraykey"]['displayName'] = $key;
		}
		
		// user change the file case from server side
		// remove case diff files and update db to physical filename
		foreach($disk_sets as $filename => $value)
		{
			if (isset($db_sets["$filename"]) && (!isset($server_deleted_sets["$filename"])))
			{
				if ($db_sets["$filename"]['displayName'] <> $disk_sets["$filename"]['displayName'])
				{
					$sql = "update $prefix"."fs set filename='".mysql_real_escape_string($disk_sets["$filename"]['displayName'])."' where uid='".$username."' and filename='".mysql_real_escape_string($db_sets["$filename"]['displayName'])."'";
					mysql_query($sql);
					
					unset($db_sets["$filename"]);
					unset($disk_sets["$filename"]);
				}
			}
		}
		
		// compare database & current physical disk array to checkout differents
		foreach($db_sets as $filename => $value)
		{
			if (isset($db_sets["$filename"])&&isset($disk_sets["$filename"]))
			{
				// the value between db and physical disk were different
				if (count(array_diff_assoc($db_sets["$filename"],$disk_sets["$filename"]) ) <> 0)
				{
					// if the filetime were updated then it's a new file
					if ($db_sets["$filename"]['lastModified'] <> $disk_sets["$filename"]['lastModified'])
					{
						$sql = "update $prefix"."fs set lastModified='".$disk_sets["$filename"]['lastModified']."', size='".$disk_sets["$filename"]['size']."', md5='".md5_file($dir.$disk_sets["$filename"]['displayName'])."' where uid='".$username."' and filename='".mysql_real_escape_string($filename)."'";
						mysql_query($sql);
					}
				}
				// the value between db and physical disk are the same
				// else
				// {
				//		do nothing
				// }

				/*
				 *  if db show the file deleted but it exist on disk again, update db deleteDate to null
				 */
				if (isset($server_deleted_sets["$filename"]))
				{
					$sql = "update $prefix"."fs set deleteDate=null where uid='".$username."' and filename='".mysql_real_escape_string($filename)."'";
					mysql_query($sql);
					
					/*
					 * notice all the record about the file must be deleted
					 */
					$sql = "delete from $prefix"."fs_deleted where uid='".$username."' and filename='".mysql_real_escape_string($filename)."'";
					mysql_query($sql);
				}
				// db show the file not deleted and exist on disk, do nothing.
				// else
				// {
				//	
				// }
		
				// after compare delete the same filename keys.
				unset($disk_sets["$filename"]);
			}
			// file were deleted after last sync
			elseif (isset($db_sets["$filename"])&&!isset($disk_sets["$filename"]))
			{
				// db didn't mark the file were delete from the server side, mark delete
				if (!isset($server_deleted_sets["$filename"]))
				{
					$sql = "update $prefix"."fs set deleteDate='".date("Y-m-d H:i:s")."' where uid='".$username."' and (filename='".mysql_real_escape_string($filename)."' or (filename like '".mysql_real_escape_string($filename)."%' and type='dir')) and deleteDate is null";
					mysql_query($sql);
				}
				// db mark the file were delete from the server side, do nothing
				// else
				// {
				//	
				// }
			}
		}

		// the rest disk_sets are the new files
		foreach($disk_sets as $filename => $value)
		{
			// insert new record to database
			$lastModified = $disk_sets["$filename"]['lastModified'];
			$type = $disk_sets["$filename"]['type'];
			$size = $disk_sets["$filename"]['size'];
				
			$sql = "insert into $prefix"."fs (uid, filename, lastModified, type, size, md5) values ('".$username."','".mysql_real_escape_string($disk_sets["$filename"]['displayName'])."','".$lastModified."','".$type."','".$size."','".md5_file($dir.$disk_sets["$filename"]['displayName'])."')";
			mysql_query($sql);
		}
	}

	unset($lastchildren);
	unset($children);
	unset($server_deleted_sets);
	
	$update_sets['delete'] = null;
	$update_sets['upload'] = null;
	$update_sets['download'] = null;
	
	/*
	 *  compare client file
	 */
	 
	$sql = "select count(1) cnt from $prefix"."fs where uid='".$username."'";
	$dataset = mysql_query($sql);
	list($cnt) = mysql_fetch_array($dataset);

	mysql_query("SET NAMES 'utf8'");
	mysql_query("SET CHARACTER_SET_CLIENT=utf8");
	mysql_query("SET CHARACTER_SET_RESULTS=utf8"); 

	if ($cnt == 0)
	{
		// init sync, build client filetree and all the file should be upload.
		ksort($client['FilesDirList']);
		foreach($client['FilesDirList'] as $key  => $value)
		{
			if (substr($key, -1) == '/')
			{
				if (mkdir($dir.$key, 0755))
				{
					$lastModified = date("Y-m-d H:i:s");
					$type = 'dir';
					$size = 0;
				
					$sql = "insert into $prefix"."fs (uid, filename, lastModified, type, size, md5) values ('".$username."','".mysql_real_escape_string($key)."','".$lastModified."','".$type."','".$size."','".md5_file($dir.$key)."')";
					mysql_query($sql);
					$update_sets['synced']["$key"] = $value;
				}
			}
			else
			{
				$update_sets['upload']["$key"] = $value;
			}
		}
	}
	else
	{
		// read last sync record from db, check which file shoud be download, upload, delete.
		$sql = "select filename, lastModified, type, size, md5, deleteDate from $prefix"."fs where uid='".$username."' order by `type`, filename";
		$dataset = mysql_query($sql);
		while ($row = mysql_fetch_assoc($dataset))
        {
			$lastchildren[] = $row;
		}

		// change the array strucrure to have the key, in order to be controled. (db)
		// ksort($lastchildren);
		foreach ($lastchildren as $child)
		{
			$key = $child['filename'];
			$arraykey = strtolower($key);
			$server_sets["$arraykey"]['lastModified'] = $child['lastModified'];
			$server_sets["$arraykey"]['displayName'] = $key;
			$server_sets["$arraykey"]['type'] =  $child['type'];
			// $server_sets["$key"]['size'] = $child['size'];
			// $server_sets["$key"]['md5'] = $child['md5'];
			// use another array to cache the deleteDate from db
			$server_deleted_sets["$arraykey"]['deleteDate'] = $child['deleteDate'];
			$server_deleted_sets["$arraykey"]['displayName'] = $key;
		}

		// change the array strucrure (client)
		// change the filename to lowercase
		ksort($client['FilesDirList']);
		foreach($client['FilesDirList'] as $key  => $value)
		{
			$arraykey = strtolower($key);
			$client_sets["$arraykey"]['lastModified'] =  $value;
			$client_sets["$arraykey"]['displayName'] =  $key;
			//$client_sets["$arraykey"]['type'] = (substr($value, -1)=='/') ? 'dir' : 'file';
		}
		
		// the deleteFiles info from client, change the array structure (client delete files)
		krsort($client['deleteFiles']);
		$client_deleted_sets = array();
		foreach($client['deleteFiles'] as $key  => $value)
		{
			$arraykey = strtolower($key);
			$client_deleted_sets["$arraykey"]['lastModified'] = $value;
			$client_deleted_sets["$arraykey"]['displayName'] = $key;
		}
		// delete empty array delivered from client default value;
		unset($client_deleted_sets[""]);
		
		// move case different file ( if client change file case )
		//writeLog($username.'-client_deleted', json_encode($client_deleted_sets).chr(13).json_encode($client_sets));
		foreach ($client_deleted_sets as $filename => $value)
		{
			if (isset($client_sets["$filename"]))
			{
				if ($client_sets["$filename"]['lastModified'] == $client_deleted_sets["$filename"]['lastModified'])
				{
					$synced_filename = $client_deleted_sets["$filename"]['displayName'];
					$update_sets['synced']["$synced_filename"] = $value;
					unset($client_sets["$filename"]);
					unset($client_deleted_sets["$filename"]);
				}
			}
		}
		
		// compare to checkout differents for
		if (isset($client_sets))
		{
			foreach($client_sets as $filename => $value)
			{
				$clientdisplayName = $client_sets["$filename"]['displayName'];
				
				// both client & server exist the same file or folder
				if (isset($client_sets["$filename"]) && isset($server_sets["$filename"]))
				{
					$serverdisplayName = $server_sets["$filename"]['displayName'];

					// when the client & server are differnt LastModified file
					if ($client_sets["$filename"]['lastModified'] <> $server_sets["$filename"]['lastModified'])
					{
						// compare file
						if (substr($filename, -1) <> '/')
						{
							// if the file was marked deleted from server side, tell client to upload.
							if (!is_null($server_deleted_sets["$filename"]['deleteDate']))
							{
								$update_sets['upload']["$serverdisplayName"] = $client_sets["$filename"]['lastModified'];
							}
							// if the file still on server side.
							else
							{
								if ($client_sets["$filename"]['lastModified'] > $server_sets["$filename"]['lastModified'])
								{
									$update_sets['upload']["$serverdisplayName"] = $client_sets["$filename"]['lastModified'];
								}
								elseif ($client_sets["$filename"]['lastModified'] < $server_sets["$filename"]['lastModified'])
								{
									$update_sets['download']["$serverdisplayName"] = $server_sets["$filename"]['lastModified'];
								}
								// else
								//{
								//	the same filetime is imposible here, even exists, doing nothing	.
								//}
							}
						}
						// compare dir
						// both server & client exists the same folder.
						else
						{
							// if the same folder name (time different) was marked deleted (action from website) on server side.
							if (!is_null($server_deleted_sets["$filename"]['deleteDate']))
							{
								// client folder create time is newer than server delete time, then create the dir on server side
								if ($client_sets["$filename"]['lastModified'] > $server_deleted_sets["$filename"]['deleteDate']) //$server_sets["$filename"]['lastModified'])
								{
									// empty directory will cause the permission error and the time set to 1970-01-01, so don't change the directiory time
									// touch($dir.$filename, $client_sets["$filename"]['lastModified']);
									
									// because the full path name on server case may different from client,
									// so must get the real case name from server, and keep the server new create folder the case same as client.
									$existpath = ExistPath($clientdisplayName);
									$restpath = substr($clientdisplayName,-(strlen($clientdisplayName)-strlen($existpath)));
									$serverfilepathName = $existpath.$restpath;

									if (!file_exists($dir.$serverfilepathName))
									{
										if (mkdir($dir.$serverfilepathName, 0755))
										{
											$lastModified = date("Y-m-d H:i:s");
											$type = 'dir';
											$size = 0;

                                            $sql = "delete from $prefix"."fs  where uid='".$username."' and filename='".mysql_real_escape_string($filename)."'";
                                            mysql_query($sql);
                                            $sql = "delete from $prefix"."fs_deleted  where uid='".$username."' and filename='".mysql_real_escape_string($filename)."'";
                                            mysql_query($sql);
											$sql = "insert into $prefix"."fs (uid, filename, lastModified, type, size, md5) values ('".$username."','".mysql_real_escape_string($serverfilepathName)."','".$lastModified."','".$type."','".$size."','".md5_file($dir.$serverfilepathName)."')";
											mysql_query($sql);
										}
									}
									else
									{
										// same folder name (time different) was marked deleted, means the folder not exist on server.
									}
								}
								// client create time is earler than server side file delete time, delete the dir on client side
								//
								elseif ($client_sets["$filename"]['lastModified'] < $server_deleted_sets["$filename"]['deleteDate']) //$server_sets["$filename"]['lastModified'])
								{
                                    $sql = "select size, md5, lastModified, deleteDate from $prefix"."fs_deleted where chid = $chid and filename='".mysql_real_escape_string($filename)."'";
                                    $dataset = mysql_query($sql);
                                    if (list($size, $md5, $lastModified, $deleteDate) = mysql_fetch_array($dataset))
                                    {
                                        // the same folder deleted before, but appear client again !!!
                                        $existpath = ExistPath($clientdisplayName);
                                        $restpath = substr($clientdisplayName,-(strlen($clientdisplayName)-strlen($existpath)));
                                        $serverfilepathName = $existpath.$restpath;

                                        if (mkdir($dir.$serverfilepathName, 0755))
                                        {
                                            $lastModified = date("Y-m-d H:i:s");
                                            $type = 'dir';
                                            $size = 0;

                                            $sql = "delete from $prefix"."fs  where uid='".$username."' and filename='".mysql_real_escape_string($filename)."'";
                                            mysql_query($sql);
                                            $sql = "delete from $prefix"."fs_deleted  where uid='".$username."' and filename='".mysql_real_escape_string($filename)."'";
                                            mysql_query($sql);
                                            $sql = "insert into $prefix"."fs (uid, filename, lastModified, type, size, md5) values ('".$username."','".mysql_real_escape_string($serverfilepathName)."','".$lastModified."','".$type."','".$size."','".md5_file($dir.$serverfilepathName)."')";
                                            mysql_query($sql);
                                        }
                                    }
                                    else
                                    {
                                        $update_sets['delete']["$clientdisplayName"] = $server_deleted_sets["$filename"]['deleteDate'];
                                    }
								}
                                // elseif ($client_sets["$filename"]['lastModified'] = $server_sets["$filename"]['lastModified'])
                                // {
                                    // // this was filtered
                                // }
							}
							// if the same folder name (time different) was not marked deleted (action from website) on server side, means already synced.
							else
							{
								/*
								 * note: there must keep server's display name
								 */
								$synced_sets["$serverdisplayName"] =  $value['lastModified'];
							}
						}
					}
					// when the client & server are the same file
					else
					{
						// if the same file was marked deleted from server side, tell client to delete and **give the delete time.
						// if the client file create time is later than server delete time, client won't delete. **opposite, client shall upload the file.
						// * give deleteDate time to client!!!
						// * notice user's individual client
						if (!is_null($server_deleted_sets["$filename"]['deleteDate']))
						{
							$sql = "select size, md5, lastModified, deleteDate from $prefix"."fs_deleted where chid = $chid and filename='".mysql_real_escape_string($filename)."'";
							$dataset = mysql_query($sql);
							//writeLog($username.'-dataset', json_encode($dataset));
							if (list($size, $md5, $lastModified, $deleteDate) = mysql_fetch_array($dataset))
							{
								if ($deleteDate <> null)
								{
									/*
									 * the same file deleted before, but appear client again !!!
									 */
									// client file exist again, tell client to upload
									$update_sets['upload']["$serverdisplayName"] = $value['lastModified'];
								}
								else
								{
									// client didn't delete, tell client to delete again
									$update_sets['delete']["$clientdisplayName"] = $server_deleted_sets["$filename"]['deleteDate'];
								}
							}
							else
							{
								$sql  = "insert into $prefix"."fs_deleted (uid, chid, filename, lastModified, type, size, md5) ";
								$sql .= "select uid, $chid, filename, lastModified, 'file', size, md5 ";
								$sql .= "  from $prefix"."fs ";
								$sql .= " where uid ='".$username."' and filename ='".mysql_real_escape_string($filename)."'";
								mysql_query($sql);

								$update_sets['delete']["$clientdisplayName"] = $server_deleted_sets["$filename"]['deleteDate'];
							}
						}
						// the same files means already synced
						else
						{
							/*
							 * note: there must keep server's displayname
							 */
							$synced_sets["$serverdisplayName"] =  $value['lastModified'];

							$sql  = "delete from $prefix"."fs_deleted ";
							$sql .= " where chid ='".$chid."' and filename ='".mysql_real_escape_string($filename)."'";
							mysql_query($sql);
							//writeLog($username.'-sql1', $sql);
						}
					}
			
					// delete the keys of the same value
					unset($server_sets["$filename"]);
				}
				// new folder or file on client, server not exists.
				elseif (isset($client_sets["$filename"])&&!isset($server_sets["$filename"]))
				{
					// folder only exists on client, server don't have, tell server to create. 
					if (substr($filename, -1)=='/')
					{
						// empty directory will cause the permission error and the time set to 1970-01-01, so don't change the directiory time
						// touch($dir.$filename, $client_sets["$filename"]['lastModified']);
						$existpath = ExistPath($clientdisplayName);
						$restpath = substr($clientdisplayName,-(strlen($clientdisplayName)-strlen($existpath)));
						$serverfilepathName = $existpath.$restpath;

						if (!file_exists($dir.$serverfilepathName))
						{
							if (mkdir($dir.$serverfilepathName, 0755))
							{
								$lastModified = date("Y-m-d H:i:s");
								$type = 'dir';
								$size = 0;
							
								$sql = "insert into $prefix"."fs (uid, filename, lastModified, type, size, md5) values ('".$username."','".mysql_real_escape_string($serverfilepathName)."','".$lastModified."','".$type."','".$size."','".md5_file($dir.$serverfilepathName)."')";
								mysql_query($sql);
							}
						}
						else
						{
							$sql  = "update $prefix"."fs set filename='".mysql_real_escape_string($clientdisplayName)."', deleteDate=null";
							$sql .= " where uid ='".$username."' and filename ='".mysql_real_escape_string($filename)."'";
							mysql_query($sql);
							// writeLog($username.'-folder-rename', $dir.$clientdisplayName);
						}
					}
					// file only exists on client, server don't have, tell client to upload. 
					else
					{
						// server not exist the file so dont know the server path name, must compare to get.  
						$existpath = ExistPath($clientdisplayName);
						$restpath = substr($clientdisplayName,-(strlen($clientdisplayName)-strlen($existpath)));
						$serverfilepathName = $existpath.$restpath;
						$update_sets['upload']["$serverfilepathName"] = $client_sets["$filename"]['lastModified'];
					}
				}
			}
		}

		/**
		 *  the file that client :
		 *  1. never sync before
		 *  2. copy file on local.
		 *  3. delete, move or rename on local.
		 */

		setlocale(LC_ALL, 'zh_TW.UTF8');
		date_default_timezone_set("UTC");

		// the rest file are only in server, same meaning : if( isset($server_sets["$filename"]) && !isset($client_sets["$filename"]))
		// writeLog($username.'-server_sets', json_encode($server_sets));
		foreach($server_sets as $filename => $value)
		{
			$serverdisplayName = $server_sets["$filename"]['displayName'];
			
			// the file record was marked deteted on server & client no record
			if (!is_null($server_deleted_sets["$filename"]['deleteDate']))
			{
				// client already deleted, action triggered by other client or from server
				$sql = "select deleteDate from $prefix"."fs_deleted where chid = $chid and filename ='".mysql_real_escape_string($filename)."'";
				$dataset = mysql_query($sql);
				//writeLog($username.'-deleted-dataset'.rand(10000), $sql);
				// writeLog($username.'-deleted-dataset', json_encode($dataset));
				
				if (list($deleteDate) = mysql_fetch_array($dataset))
				{
					if ($deleteDate==null)
					{
						// client really deleted the file, confirm the delete time
						$sql  = "update $prefix"."fs_deleted set deleteDate='".date("Y-m-d H:i:s")."'";
						$sql .= " where chid = $chid and filename ='".mysql_real_escape_string($filename)."'";
						mysql_query($sql);
					}
					else
					{
					    if ( $debugMode==false )   // when debug, keep the deleted record on database for check.
					    {
    						// every clent already synced delete and the file and already confirm the delete time
    						// then delete all the deleted record from fs & fs deleted
    						$sql = "select count(1) cnt ".
                                   "  from oc_fs t1 ".
                                   "  left join oc_fs_deleted t2 ".
                                   "    on t1.uid=t2.uid ".
                                   "   and t1.filename=t2.filename ".
                                   " where t1.deleteDate is not null ".
                                   "   and t2.deleteDate is null ".
                                   "   and t1.uid='".$username."' ".
                                   "   and t1.filename='".mysql_real_escape_string($filename)."'";
    						$dataset = mysql_query($sql);
                            list($cnt) = mysql_fetch_array($dataset);
    
    						if ($cnt==0)
    						{
    							$sql = "delete from $prefix"."fs_deleted where uid='".$username."' and filename='".mysql_real_escape_string($filename)."'";
    							mysql_query($sql);
    							$sql = "delete from $prefix"."fs where uid='".$username."' and filename='".mysql_real_escape_string($filename)."'";
    							mysql_query($sql);
    						}
    				    }
					}
				}
				else
				{
                    $sql  = "insert into $prefix"."fs_deleted (uid, chid, filename, lastModified, type, size, md5) ";
                    $sql .= "select uid, $chid, filename, lastModified, '".$server_sets["$filename"]['type']."', size, md5 ";
					$sql .= "  from $prefix"."fs ";
					$sql .= " where uid ='".$username."' and filename ='".mysql_real_escape_string($filename)."'";
					mysql_query($sql);
				}

				// writeLog($username.'-file-delete', $filename);
			}
			// the file record was not marked deteted on server & client no record
			else
			{
				// server have the file but client already deleted, delete it from server.
				// the action triggred by client
				if (isset($client_deleted_sets["$filename"]))
				{
					$sql  = "insert into $prefix"."fs_deleted (uid, chid, filename, lastModified, type, size, md5) ";
					$sql .= "select uid, $chid, filename, lastModified, '".$server_sets["$filename"]['type']."', size, md5 ";
					$sql .= "  from $prefix"."fs ";
					$sql .= " where uid ='".$username."' and filename ='".$filename."'";
					mysql_query($sql);

					// skip download and put in unlink list
					$server_unlink_sets["$filename"] = $value;
					unset($client_deleted_sets["$filename"]);
				}
				// not exits in client, tell client to download
				else
				{
					$update_sets['download']["$serverdisplayName"] = $server_sets["$filename"]['lastModified'];
				}
			}
		}

		foreach($client_deleted_sets as $key  => $value)
		{
			$server_unlink_sets["$key"] = $value;
		}

		//writeLog($username.'-server_unlink_sets',json_encode($server_unlink_sets));
		// determind the file should be deleted, renamed ( or moved) on server. 
		if (isset($server_unlink_sets))
		{
			krsort($server_unlink_sets);
			
			// client file rename, compare the file that client tell server to delete and will be uploaded from client
			if (!is_null($update_sets['upload']))
			{
				/*
				 * $server_unlink_sets -> filename: lower case, the Source
				 * $update_sets['upload'] -> uploadfilename: display case, the Dest
				 */
				foreach($server_unlink_sets as $filename => $value)
				{
					// only file would do move & rename, because directory no need to download & upload
					if (substr($filename, -1)<>'/')
					{
						foreach($update_sets['upload'] as $uploadfilename => $uploadfilevalues)
						{
							// file's lastModified is the same
							if ($server_unlink_sets["$filename"]['lastModified']==$update_sets['upload']["$uploadfilename"])
							{
								// client rename or move
								if ($filename <> strtolower($uploadfilename) && substr($uploadfilename, -1) <> '/')
								{
									if (rename($dir.$server_unlink_sets["$filename"]['displayName'], $dir.$uploadfilename))
									{
										// correct the lastModifiedtime
										touch($dir.$uploadfilename, strtotime($server_unlink_sets["$filename"]['lastModified']));
										
										/*
										 * 1. delete the dest rec(fs & fs_deleted) before insert new dest rec
										 * 2. insert the new dest rec from src rec 
										 * 3. src mark the fs deleted
										 */
										$sql  = "delete from $prefix"."fs ";
										$sql .= " where uid ='".$username."' and filename ='".mysql_real_escape_string($uploadfilename)."'";
										mysql_query($sql);
										// writeLog($username.'-move-sql1', $sql);
											
										$sql  = "insert into $prefix"."fs (uid, filename, lastModified, type, size, md5) ";
										$sql .= "select uid, '".mysql_real_escape_string($uploadfilename)."', lastModified, 'file', size, md5 ";
										$sql .= "  from $prefix"."fs ";
										$sql .= " where uid ='".$username."' and filename ='".mysql_real_escape_string($filename)."'";
										mysql_query($sql);
										// writeLog($username.'-move-sql2', $sql);
											
										$sql  = "update $prefix"."fs set deleteDate='".date("Y-m-d H:i:s")."'";
										$sql .= " where uid ='".$username."' and filename ='".mysql_real_escape_string($filename)."'";
										mysql_query($sql);
										// writeLog($username.'-move-sql3', $sql);

										unset($update_sets['upload']["$uploadfilename"]);
										unset($server_unlink_sets["$filename"]);
									}
								}
							}
						}
					}
					// the directory
					else
					{
						foreach($update_sets['upload'] as $uploadfilename => $uploadfilevalues)
						{
							if (     ( $filename <> strtolower($uploadfilename) )
							     and ( substr($uploadfilename, -1) == '/') )
							{
								// when move, the dirs treatment
								rmdir($dir.$server_unlink_sets["$filename"]['displayName']);
								mkdir($dir.$update_sets['upload']["$uploadfilename"]);
								
								$sql  = "delete from $prefix"."fs ";
								$sql .= " where uid ='".$username."' and filename ='".mysql_real_escape_string($uploadfilename)."'";
								mysql_query($sql);
								// writeLog($username.'-folder-move-sql1', $sql);
									
								$sql  = "insert into $prefix"."fs (uid, filename, lastModified, type, size, md5) ";
								$sql .= "select uid, '".mysql_real_escape_string($uploadfilename)."', lastModified, 'file', size, md5 ";
								$sql .= "  from $prefix"."fs ";
								$sql .= " where uid ='".$username."' and filename ='".mysql_real_escape_string($filename)."'";
								mysql_query($sql);
								// writeLog($username.'-folder-move-sql2', $sql);
									
								$sql  = "update $prefix"."fs set deleteDate='".date("Y-m-d H:i:s")."'";
								$sql .= " where uid ='".$username."' and filename ='".mysql_real_escape_string($filename)."'";
								mysql_query($sql);
								// writeLog($username.'-folder-move-sql3', $sql);
									
								unset($update_sets['upload']["$uploadfilename"]);
								unset($server_unlink_sets["$filename"]);
							}
						}
					}
				}
			}
			// no rename or move, just only unlink
			else
			{
				foreach($server_unlink_sets as $filename => $value)
				{
					if (substr($filename, -1) <> '/')
					{
						$result = unlink($dir.$server_unlink_sets["$filename"]['displayName']);
					}
					else
					{
						$result = rrmdir($dir.$server_unlink_sets["$filename"]['displayName']);
					}
						
					if ($result)
					{
						// mark delete
						$sql = "update $prefix"."fs set deleteDate='".date("Y-m-d H:i:s")."' where uid='".$username."' and (filename='".mysql_real_escape_string($filename)."' or (filename like '".mysql_real_escape_string($filename)."%' and type='dir')) and deleteDate is null";
						mysql_query($sql);
						
						$sql  = "delete from $prefix"."fs_deleted ";
						$sql .= " where chid=$chid and filename ='".mysql_real_escape_string($filename)."'";
						mysql_query($sql);
						// writeLog($username.'-client-delete-sql1', $sql);

						$sql  = "insert into $prefix"."fs_deleted (chid, uid,  filename, lastModified, type, size, md5, deleteDate) "; // client already deleted, so give deleteDate
						$sql .= "select $chid, uid, '".mysql_real_escape_string($server_unlink_sets["$filename"]['displayName'])."', lastModified, 'file', size, md5, '".date("Y-m-d H:i:s")."'";
						$sql .= "  from $prefix"."fs ";
						$sql .= " where uid ='".$username."' and filename ='".mysql_real_escape_string($filename)."'";
						mysql_query($sql);
						// writeLog($username.'-client-delete-sql2', $sql);

						unset($update_sets['download']["$filename"]);
					}
				}
			}
		}
		
		// mapping client side copy action
		if (!is_null($update_sets['upload']))
		{
			// the copy dest
			foreach($update_sets['upload'] as $destFilename => $value)
			{   // foloder
                if (substr($destFilename, -1) == '/')
                {
                    if (mkdir($dir.$destFilename, 0755))
                    {
                        $lastModified = date("Y-m-d H:i:s");
                        $type = 'dir';
                        $size = 0;
                    
                        $sql = "delete from $prefix"."fs  where uid='".$username."' and filename='".mysql_real_escape_string($destFilename)."'";
                        mysql_query($sql);
                        $sql = "insert into $prefix"."fs (uid, filename, lastModified, type, size, md5) values ('".$username."','".mysql_real_escape_string($destFilename)."','".$lastModified."','".$type."','".$size."','".md5_file($dir.$destFilename)."')";
                        mysql_query($sql);

                        $update_sets['synced']["$destFilename"] = $value;
                        unset($update_sets['upload']["$destFilename"]);
                    }
                }
                else
                {   // the copy src
    				$sql = "select filename from $prefix"."fs where uid='".$username."' and type = 'file' and deleteDate is null and filename like '%".mysql_real_escape_string(basename($destFilename))."' and lastModified='".$value."' and size > " . $fileactsize . " LIMIT 1";
    				$dataset = mysql_query($sql);
    				// when src exists
    				if (list($srcFilename) = mysql_fetch_array($dataset))
    				{
    					if (copy($dir.$srcFilename, $dir.$destFilename))
    					{
    						// correct the lastModifiedtime
    						touch($dir.$destFilename, strtotime($value));
    						
    						$sql  = "delete from $prefix"."fs ";
    						$sql .= " where uid ='".$username."' and filename ='".mysql_real_escape_string($destFilename)."'";
    						mysql_query($sql);
    						// writeLog($username.'-copy-sql1', $sql);
    
    						$sql  = "insert into $prefix"."fs (uid, filename, lastModified, type, size, md5) ";
    						$sql .= "select uid, '".mysql_real_escape_string($destFilename)."', lastModified, 'file', size, md5 ";
    						$sql .= "  from $prefix"."fs ";
    						$sql .= " where uid ='".$username."' and filename ='".mysql_real_escape_string($srcFilename)."'";
    						mysql_query($sql);
    						// writeLog($username.'-copy-sql2', $sql);
    
    						unset($update_sets['upload']["$destFilename"]);
    					}
    				}
                }
			}
		}
	}

	// remove fail moved file record
	// $sql  = "delete from $prefix"."fs_deleted ";
	// $sql .= " where chid ='".$chid."' and deleteDate is null";
	// mysql_query($sql);

	// remove fail moved file record
	// $sql  = "delete from $prefix"."fs ";
	// $sql .= " where uid ='".$username."' ";
	// $sql .= "   and deleteDate is not null ";
	// $sql .= "   and filename not in (select distinct filename from $prefix"."fs_deleted where uid ='".$username."' )";
	// mysql_query($sql);

	mysql_close($link);
	
	// remove case diff file from $update_sets['download'], that was already synced.
	if (isset($update_sets['synced']) && isset($update_sets['download']))
	{
		foreach($update_sets['synced'] as $filename => $value)
		{
			$synced[]=strtolower($filename);
		}
		
		foreach($update_sets['download'] as $clientdownload => $value)
		{
			if (in_array(strtolower($clientdownload), $synced))
			{
				unset($update_sets['download']["$clientdownload"]);
			}
		}
	}
}
else
{
	// sync folder not exist then stop
	exit;
}

$update_sets['path'] = '/dav/webdav.php/'.$dataDir;
$update_sets['synced'] = isset($synced_sets) ? $synced_sets : array('' => '');
$update_sets['delete'] = EmptyJsonFormat($update_sets['delete']);
$update_sets['upload'] = EmptyJsonFormat($update_sets['upload']);
$update_sets['download'] = EmptyJsonFormat($update_sets['download']);

if (    ($username == 'synchk') 
    and ($debugMode == true) )
{
    writeLog($username.'-to-client', json_encode($update_sets));
}

header('Content-Type: application/json; charset=utf-8');
echo json_encode($update_sets);

if ( ($debugMode == true) )
{
    writeLog($username.'-QueryTime', 'Query time: ' . (microtime(true) - $start_time) . ' sec.');
}