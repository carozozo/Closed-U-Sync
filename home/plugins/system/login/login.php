<?php
/**
 *
 * System Login
 * A tool to automatically SystemLogin a user
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access.
defined('_JEXEC') or die;

/**
 * Class for System Login
 * @package		Joomla.Plugin
 * @subpackage	User.ucloudregistration
 * @version		2.5
 */
class plgSystemLogin extends JPlugin
{

	public function onAfterDispatch()
	{
        $db = JFactory::getDBO();
		$app = JFactory::getApplication();
		$user = JFactory::getUser();
		if ($_SERVER["SERVER_PORT"]=="443")
		{
			$url = 'https://'.$_SERVER["SERVER_NAME"];
		}
		else
		{
			$url = 'http://'.$_SERVER["SERVER_NAME"].':'.$_SERVER["SERVER_PORT"];
		}

        /**
         *  When user login, redirect to cloud once
         */
        $query = "SELECT username" .
                "  FROM #__user_redir_cloud" .
                " WHERE username='".strtolower($user->username)."'";
        $db->setQuery( $query );
        
        if (!(strtolower($user->username) == strtolower($db->loadResult())))
        {
            return;
        }
        
        $query = "DELETE FROM #__user_redir_cloud WHERE username = '".strtolower($user->username)."'";
        $db->setQuery($query);
        $db->query();
		
        /**
         * redirect to cloud
         */
		if ((!$app->isAdmin()) and ($user->id <> 0))
		{
			//request token from server
			$strOnlineSend = $url . "/sapi/directloginreq.php";
			$para = array('username' => strtolower($user->username));
			$token = json_decode($this->CurlCall($strOnlineSend, $para), 1);
 
            // redirect to cloud
            header("Location: " . $url ."/api/directlogin.php?token=" . $token['token'] );
			exit;
		}
	}
	
	
	public function CurlCall($strOnlineSend, $data)
	{
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_URL, $strOnlineSend);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
	
		$file = curl_exec($ch);
		curl_close($ch);
		
		return $file;
	}
	
}
