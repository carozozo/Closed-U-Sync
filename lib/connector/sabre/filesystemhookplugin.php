<?php

/**
 * hook copy 和 rename 到 Web DAV
 * @author Caro Huang
 */
class OC_Connector_Sabre_FileSystemHookPlugin extends Sabre_DAV_ServerPlugin {

    /**
     * Reference to main server object
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
    function initialize(Sabre_DAV_Server $server) {
        $this -> server = $server;
        $this -> server -> subscribeEvent('beforeUnbind', array(
            $this,
            'hooksBeforeDelete'
        ), 30);
        $this -> server -> subscribeEvent('beforeBind', array(
            $this,
            'hooksBeforeCopyAndMove'
        ), 20);

    }

    /**
     * 停止 Sebre Dav 中的 delete 並執行 OC 本身的 unlink
     * 註： Sebre Dav 本身的 delete 是逐一刪除底下的節點，會造成 Recycle APP 的資料結構不同
     * @param string $destination
     * @throws Sabre_DAV_Exception
     * @return bool
     */
    function hooksBeforeDelete($destination) {
        $server = $this -> server;
        $method = $server -> httpRequest -> getMethod();
        $method = strtoupper($method);
        if ($method == 'DELETE') {
            # 取得目標路徑
            $destination = OC_Helper::pathForbiddenChar($destination);
            OC_Filesystem::unlink($destination);
            return false;
        }
        return true;
    }

    /**
     * 停止 Sebre Dav 中的 copy 和 move(rename)
     * 並執行 OC 本身的 copy/rename，才能正常觸發相關的 hooks
     * 註： Sebre Dav 本身的  move 其實是執行 copy then delete ，會影響相關的 hooks
     * @param string $destination
     * @throws Sabre_DAV_Exception
     * @return bool
     */
    function hooksBeforeCopyAndMove($destination) {
        $server = $this -> server;
        $method = $server -> httpRequest -> getMethod();
        $method = strtoupper($method);
        if ($method == 'COPY' || $method == 'MOVE') {
            # 取得來源路徑($uri就是source path)
            $uri = $server -> getRequestUri();
            $uri = OC_Helper::pathForbiddenChar($uri);
            # 取得目標路徑
            $destination = OC_Helper::pathForbiddenChar($destination);
            if ($method == 'COPY') {
                OC_Filesystem::copy($uri, $destination);
            } else {
                OC_Filesystem::rename($uri, $destination);
            }
            return false;
        }
    }

}
