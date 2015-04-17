<?php

/**
 * This plugin check user permission that if can download/copy and update in GroupShared folders
 * @author Caro Huang
 */
class OC_Connector_Sabre_GroupSharePlugin extends Sabre_DAV_ServerPlugin {

	/**
	 * Reference to main server object
	 *
	 * @var Sabre_DAV_Server
	 */
	private $server;

	/**
	 * This initializes the plugin.
	 *
	 * This function is called by Sabre_DAV_Server, after
	 * addPlugin is called.
	 *
	 * This method should set up the requires event subscriptions.
	 *
	 * @param Sabre_DAV_Server $server
	 * @return void
	 */
	public function initialize(Sabre_DAV_Server $server) {
		$this -> server = $server;
		$this -> server -> subscribeEvent('beforeMethod', array(
			$this,
			'checkGroupSharePermission'
		), 10);
	}

	/**
	 * This method is called before any HTTP method and check the operate permission in GroupShared folder
	 *
	 * @param string $method
	 * @throws Sabre_DAV_Exception
	 * @return bool
	 */
	public function checkGroupSharePermission($method) {
		$path = '/' . $this -> server -> getRequestUri();
		// OC_Log::write('checkGroupSharePermission', '$method=' . $method, 1);
		// OC_Log::write('checkGroupSharePermission', '$path=' . $path, 1);
		if (strpos($path, '/' . OC_GroupShare::groupSharedDir() . '/') === 0) {
			if ($method == 'MKCOL' || $method == 'PUT') {
				//MKCOL = upload dir, PUT = upload file
				$is_writeable = OC_Filesystem::is_writeable($path);
				if (!$is_writeable) {
					throw new Sabre_DAV_Exception_Forbidden();
				}
			} else if ($method == 'GET' || $method == 'COPY') {
				//GET = download, COPY = copy
				$is_readable = OC_Filesystem::is_readable($path);
				$dir = dirname($path);
				$lastDir = substr($dir, strrpos($dir, '/'));
				//如果是不可讀的，而且不是縮圖資料夾底下的縮圖
				if (!$is_readable && $lastDir != '/.thumbs') {
					throw new Sabre_DAV_Exception_Forbidden();
				}
			} else if ($method == 'MOVE') {
				// throw new Sabre_DAV_Exception_Forbidden();
			}
		}
		return TRUE;
	}

}
