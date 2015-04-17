<?php
/**
 *
 * Contact Creator
 * A tool to automatically create and synchronise contacts with a user
 * @copyright   Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access.
defined('_JEXEC') or die;

/**
 * Class for Registration
 * @package     Joomla.Plugin
 * @subpackage  User.ucloudregistration
 * @version     2.5
 */
class plgUserUCloudRegistration extends JPlugin
{

    /**
     * ucloud url
     *
     */
    private function ucloud_url()
    {
        if ($_SERVER["SERVER_PORT"]=="443")
        {
            $ucloud_url = 'https://'.$_SERVER["SERVER_NAME"];
        }
        else
        {
            $ucloud_url = 'http://'.$_SERVER["SERVER_NAME"].':'.$_SERVER["SERVER_PORT"];
        }
        
        return $ucloud_url;
    }

    /**
     *  Joomla 端變更密碼時將 owncloud 密碼打亂，再讓 user 由 webdav 登入時讓授權程式更正之
     */
    function onUserAfterSave($user, $isnew, $success, $msg)
    {
        if(!$success)
        {
            return false; // if the user wasn't stored
        }

        $username = strtolower($user['username']);
        $email = $user['email'];
        $ucloud_url = $this->params->get('ucloud_url', $this->ucloud_url());
        
        // get user quota
        $db = JFactory::getDBO();
        $query = "SELECT b.quota" .
                "  FROM #__user_product a" .
                "  join #__product b" .
                "    on a.productname=b.productname" .
                " WHERE a.username='".$username."'";

        $db->setQuery( $query );

        if (!($quota = $db->loadResult()))
        {
            $quota = $this->params->get('quota', 0);
        }

        if(!$isnew) // if the user isn't new
        {
            $query = "SELECT block" .
                     "  FROM #__users" .
                     " WHERE email='".$email."'";
            $db->setQuery( $query );

            $block = $db->loadResult();

            // sync user block status,
            if ($block==1)
            {
                $strOnlineSend = $ucloud_url."/sapi/blockuser.php";
                $data = array('username' => strtolower($username));
                $this->CurlCall($strOnlineSend, $data);
            }
            else
            {
                $strOnlineSend = $ucloud_url."/sapi/unblockuser.php";
                $data = array('username' => strtolower($username));
                $this->CurlCall($strOnlineSend, $data);
            }

            // then change user password to random.
            if ($block==1)
            {
                $strOnlineSend = $ucloud_url."/sapi/modifyuser.php";
                $data = array('username' => '##'.strtolower($username), 'password' => md5(date("YmdHis")));
                $this->CurlCall($strOnlineSend, $data);
            }
            else
            {
                $strOnlineSend = $ucloud_url."/sapi/modifyuser.php";
                $data = array('username' => strtolower($username), 'password' => md5(date("YmdHis")));
                $this->CurlCall($strOnlineSend, $data);
            }
        }
        else
        {
            $strOnlineSend = $ucloud_url."/sapi/createuser.php";
            $data = array('username' => strtolower($username), 'password' => md5(date("YmdHis")), 'quota' => $quota);
            $this->CurlCall($strOnlineSend, $data);
            
            // when user email activate or create user from backend
            if (in_array('block',$user)) {
                if ($user['block']==1) 
                {
                    $strOnlineSend = $ucloud_url."/sapi/blockuser.php";
                    $data = array('username' => strtolower($username));
                    $this->CurlCall($strOnlineSend, $data);
                }
                 else
                {
                    $strOnlineSend = $ucloud_url."/sapi/unblockuser.php";
                    $data = array('username' => strtolower($username));
                    $this->CurlCall($strOnlineSend, $data);
                }
            }
        }
    }
    
    function onUserAfterDelete($user, $success, $msg)
    {
        if(!$success) {
            return false; // if the user wasn't stored
        }

        $username = strtolower($user['username']);
        $ucloud_url = $this->params->get('ucloud_url', $this->ucloud_url());
        $strOnlineSend = $ucloud_url."/sapi/removeuser.php";
        $data = array('username' => $username);
        $this->CurlCall($strOnlineSend, $data);
        // $strOnlineSend = $ucloud_url."/sapi/removeuser.php";
        // $data = array('username' => '##'.$username);
        // $this->CurlCall($strOnlineSend, $data);
        
        $db = JFactory::getDBO();
        $query = "DELETE FROM #__user_product WHERE username = '".$username."'";
        $db->setQuery($query);
        $db->query();
        
    }
    
    public function onUserLogin($user, $options = array())
    {
        /**
         *  When user login, redirect to cloud once
         */
        $username = strtolower($user['username']);
        
        $db = JFactory::getDBO();
        $query = "INSERT INTO #__user_redir_cloud(username) VALUES ('$username')";
        $db->setQuery($query);
        $db->query();
        
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
    }

}