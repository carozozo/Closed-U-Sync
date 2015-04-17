<?php

/**
 * ownCloud
 *
 * @author Frank Karlitschek
 * @copyright 2010 Frank Karlitschek karlitschek@kde.org
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU AFFERO GENERAL PUBLIC LICENSE
 * License as published by the Free Software Foundation; either
 * version 3 of the License, or any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU AFFERO GENERAL PUBLIC LICENSE for more details.
 *
 * You should have received a copy of the GNU Affero General Public
 * License along with this library.  If not, see <http://www.gnu.org/licenses/>.
 *
 */

$RUNTIME_NOAPPS = TRUE;
//no apps, yet

require_once ('lib/base.php');

// check if database structure need to be updated, after update then delete sqlupdate.php
$files = glob(__DIR__.'/version/*', GLOB_MARK);
foreach ($files as $updateFile) {
	//如果是檔案的話
	if (substr($updateFile, strrpos($updateFile, '/'), 9) == '/_update_' && substr($updateFile, -4) == '.php') {
		require_once $updateFile;
		unlink($updateFile);
	}
}

if ($_SERVER["SERVER_PORT"] == "443") {
	$default_return_url = 'https://' . $_SERVER["SERVER_NAME"];
} else {
	$default_return_url = 'http://' . $_SERVER["SERVER_NAME"] . ':' . $_SERVER["SERVER_PORT"];
}

if (OC_Helper::serverMainType() == 'p') {
	$default_return_url .= '/home';
}

// Setup required :
$not_installed = !OC_Config::getValue('installed', false);

if ($not_installed) {
	// Check for autosetup:
	$autosetup_file = OC::$SERVERROOT . "/config/autoconfig.php";
	if (file_exists($autosetup_file)) {
		OC_Log::write('core', 'Autoconfig file found, setting up owncloud...', OC_Log::INFO);
		include ($autosetup_file);
		$_POST['install'] = 'true';
		$_POST = array_merge($_POST, $AUTOCONFIG);
		unlink($autosetup_file);
	}
	OC_Util::addScript('setup');
	require_once ('setup.php');
	exit();
}

// Handle WebDAV
if ($_SERVER['REQUEST_METHOD'] == 'PROPFIND') {
	header('location: ' . OC_Helper::linkTo('dav', 'webdav.php'));
	exit();
}

// Someone is logged in :
elseif (OC_User::isLoggedIn()) {
	if (isset($_GET["logout"]) and ($_GET["logout"])) {
		OC_User::logout();
		if (isset($_COOKIE['oc_login_referer_url'])) {
			header("Location: " . $_COOKIE['oc_login_referer_url']);
			exit ;
		} else {
			header("Location: " . $default_return_url);
			exit ;
		}
		header("Location: " . OC::$WEBROOT . '/');
		exit();
	} else {
		OC_Util::redirectToDefaultPage();
	}
}

// For all others cases, we display the guest page :
else {
	if (OC_Helper::serverMainType() == 'p') {
		header("Location: " . $default_return_url);
		exit ;
	}
	OC_App::loadApps();
	$error = false;

	// remember was checked after last login
	if (isset($_COOKIE["oc_remember_login"]) && isset($_COOKIE["oc_token"]) && isset($_COOKIE["oc_username"]) && $_COOKIE["oc_remember_login"]) {
		if (defined("DEBUG") && DEBUG) {
			OC_Log::write('core', 'Trying to login from cookie', OC_Log::DEBUG);
		}
		// confirm credentials in cookie
		if (isset($_COOKIE['oc_token']) && OC_User::userExists($_COOKIE['oc_username']) && OC_Preferences::getValue($_COOKIE['oc_username'], "login", "token") == $_COOKIE['oc_token']) {
			OC_User::setUserId($_COOKIE['oc_username']);
			OC_Util::redirectToDefaultPage();
		} else {
			OC_User::unsetMagicInCookie();
		}
	}

	// Someone wants to log in :
	elseif (isset($_POST["user"]) && isset($_POST['password'])) {
		$_POST["user"] = strtolower($_POST["user"]);
		if (OC_User::login($_POST["user"], $_POST["password"])) {
			setcookie('oc_login_referer_url', $default_return_url);
			if (!empty($_POST["remember_login"])) {
				if (defined("DEBUG") && DEBUG) {
					OC_Log::write('core', 'Setting remember login to cookie', OC_Log::DEBUG);
				}
				$token = md5($_POST["user"] . time() . $_POST['password']);
				OC_Preferences::setValue($_POST['user'], 'login', 'token', $token);
				OC_User::setMagicInCookie($_POST["user"], $token);
			} else {
				OC_User::unsetMagicInCookie();
			}
			OC_Util::redirectToDefaultPage();
		} else {
			$error = true;
		}
	}
	// The user is already authenticated using Apaches AuthType Basic... very usable in combination with LDAP
	elseif (isset($_SERVER["PHP_AUTH_USER"]) && isset($_SERVER["PHP_AUTH_PW"])) {
		if (OC_User::login($_SERVER["PHP_AUTH_USER"], $_SERVER["PHP_AUTH_PW"])) {
			//OC_Log::write('core',"Logged in with HTTP Authentication",OC_Log::DEBUG);
			OC_User::unsetMagicInCookie();
			OC_Util::redirectToDefaultPage();
		} else {
			$error = true;
		}
	}
	
	OC_Template::printGuestPage('', 'login', array(
		'error' => $error,
		'redirect' => isset($_REQUEST['redirect_url']) ? $_REQUEST['redirect_url'] : ''
	));

}
