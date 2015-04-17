<?php
/**
 * ownCloud - 公佈欄
 *
 * @author Caro Huang
 * @copyright 2013 U-Sync
 */
OC::$CLASSPATH['OC_PublicBoard'] = "apps/public_board/lib/publicboard.php";
OC::$CLASSPATH['OC_PublicBoard_Hooks'] = "apps/public_board/lib/publicboard_hooks.php";

OC_Hook::connect('OC_Filesystem', 'post_filesize', 'OC_PublicBoard_Hooks', 'filesizeWithoutPublicBoard');
OC_Hook::connect('OC_Filesystem', 'post_free_space', 'OC_PublicBoard_Hooks', 'freeSpaceWithoutPublicBoard');

# 產新公佈欄連結
OC_PublicBoard::createPublicBoardLink();