<?php

/**
 * This plugin check user if can move the target
 * @author Caro Huang
 */
class OC_Connector_Sabre_FileSystemPlugin extends Sabre_DAV_ServerPlugin {

    /**
     * Reference to main server object
     * @var Sabre_DAV_Server
     */
    private $server;
    private static $rejectMovePath = array();

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
    function initialize(Sabre_DAV_Server $server) {
        $this -> server = $server;
        $this -> server -> subscribeEvent('beforeMethod', array(
            $this,
            'rejectMovePath'
        ), 10);
    }

    /**
     * This method is called before any HTTP method and reject to move path
     * @param string $method
     * @throws Sabre_DAV_Exception
     * @return bool
     */
    function rejectMovePath($method) {
        $path = '/' . $this -> server -> getRequestUri();
        // OC_Log::write('sysemFolderPermission', '$method=' . $method, 1);
        // OC_Log::write('sysemFolderPermission', '$path=' . $path, 1);
        if ($method == 'MOVE' && in_array($path, self::$rejectMovePath)) {
            throw new Sabre_DAV_Exception_Forbidden();
        }
        return TRUE;
    }

    /**
     * Add the path that can not rename/move
     * @param string or array $path
     */
    function addRejectMovePath($pathArr) {
        if (is_string($pathArr)) {
            $pathArr = array($pathArr);
        }
        foreach ($pathArr as $key => $path) {
            $path = OC_Files_Helper::normalizePath($path);
            $path = rtrim($path, '/');
            if (!in_array($path, self::$rejectMovePath)) {
                self::$rejectMovePath[] = $path;
            }
        }
    }

}
