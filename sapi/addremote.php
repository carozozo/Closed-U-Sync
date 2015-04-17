<?php
// Server limited
require_once('inc_server.php');

// Do not load FS ...
$RUNTIME_NOSETUPFS = true;

require_once('../lib/base.php');
//OC_Util::checkAppEnabled('remoteStorage');
//require_once('../3rdparty/Sabre/autoload.php');
require_once('../apps/remoteStorage/lib_remoteStorage.php');
require_once('../apps/remoteStorage/oauth_ro_auth.php');

ini_set('default_charset', 'UTF-8');
@ob_clean();

//allow use as remote storage for other websites
if(isset($_SERVER['HTTP_ORIGIN'])) {
	header('Access-Control-Allow-Origin: ' . $_SERVER['HTTP_ORIGIN']);
	header('Access-Control-Max-Age: 3600');
	header('Access-Control-Allow-Methods: OPTIONS, GET, PUT, DELETE, PROPFIND');
  	header('Access-Control-Allow-Headers: Authorization, Content-Type');
} else {
	header('Access-Control-Allow-Origin: *');
}

$path = substr($_SERVER["REQUEST_URI"], strlen($_SERVER["SCRIPT_NAME"]));
$pathParts =  explode('/', $path);

if(count($pathParts) == 2 && $pathParts[0] == '') {
	//TODO: input checking. these explodes may fail to produces the desired arrays:
	$subPathParts = explode('?', $pathParts[1]);
	$ownCloudUser = $subPathParts[0];
	foreach($_GET as $k => $v) {
		if($k=='user_address'){
			$userAddress=$v;
		} else if($k=='redirect_uri'){
			$appUrl=$v;
		} else if($k=='scope'){
			$category=$v;
		}
	}
	if(isset($_REQUEST['allow'])) {
		$tokens=OC_remoteStorage::getValidTokens($ownCloudUser, $category);
		foreach($tokens as $tmpvar => $value)
		{
			$token=$tmpvar;
			break;
		}
		if (!$token) {
			//TODO: check if this can be faked by editing the cookie in firebug!
			$token=uniqid();
			$query=OC_DB::prepare("INSERT INTO *PREFIX*authtoken (`token`,`appUrl`,`user`,`category`) VALUES(?,?,?,?)");
			$result=$query->execute(array($token,$appUrl,$ownCloudUser,$category));
			//TODO: input checking on $category
		}
        OC_Util::setupFS($ownCloudUser);
        $scopePathParts = array('Course', $category);
        for($i=0;$i<=count($scopePathParts);$i++){
            $thisPath = '/'.implode('/', array_slice($scopePathParts, 0, $i));
            if(!OC_Filesystem::file_exists($thisPath)) {
                OC_Filesystem::mkdir($thisPath);
            }
        }
		//echo base64_encode($token);
		OC_JSON::success(array("data" => array( "username" => $ownCloudUser, "scope" => $category, "token" => $token)));
	} else {
		echo '<form method="POST"><input name="allow" type="submit" value="Allow this web app to store stuff on your owncloud."></form>';
	}
} else {
	//echo 'usage => https://hostname/sapi/addremote.php/username?user_address=local&redirect_uri=local&scope=test&allow=true'.chr(13);
	//echo 'param: hostname, username, scope.'.chr(13);
	//echo 'Will create /remoteStorage/scope/ folder in users storage'.chr(13);
	OC_JSON::error(array("data" => array( "message" => "Unable to add token" )));
}
