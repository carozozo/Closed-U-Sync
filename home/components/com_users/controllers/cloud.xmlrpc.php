<?php

class UsersControllerCloud extends JController
{

	private $hash = 'default'; // post, get...

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
	 * The Server Auth
	 *
	 * Method to  call curl function
	 *
	 * @param	string		$server_name, $server_password
	 *
	 * @return	boolean
	 */
	private function ServerAuth($server_name, $server_password)
	{
		
		$request_key["$server_name"]=$server_password;

		// for local maintain use
		if ($_SERVER['REMOTE_ADDR']==$_SERVER['SERVER_ADDR'])
		{
			return true;
		}
		
		$key['key1']='dad87jfoda342rfd';
		$key['testtest']='testtest';
			
		$AuthResult=false;
		if (array_key_exists($server_name,$key))
		{
			if ($request_key["$server_name"]==$key["$server_name"])
			{
				$AuthResult=true;
			}
		}

		return $AuthResult;
	}
	
	
	/**
	 * The curl function call
	 *
	 * Method to  call curl function
	 *
	 * @param	string		$strOnlineSend	URI to call
	 * @param	array		$para			post values
	 *
	 * @return	depends on $data format, might be string, json...
	 */
	private function CurlCall($strOnlineSend, $para)
	{
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_URL, $strOnlineSend);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $para);

		$file = curl_exec($ch);
		curl_close($ch);
		return $file;
	}


	/**
	 * Account List
	 * 
	 * Example
	 * index.php?option=com_users&format=xmlrpc&task=cloud.QryAccountList
	 */
	function QryAccountList()
	{
		
		$hash = $this->hash;

		$server_name = JRequest::getVar('server_name','no name var',$hash);
		$server_password = JRequest::getVar('server_password','no name var',$hash);
		
		if (!$this->ServerAuth($server_name,$server_password))
		{
			return;
		}
		
		$db	= JFactory::getDbo();
		$query = $db->getQuery(true);

		$query->select('username');
		$query->from('#__users');
		$query->where('username <> '.$db->quote('admin'));
		$query->order('username');

		$db->setQuery($query);
		$usernames = $db->loadResultArray();

		if (JRequest::getVar('type', 'text', $hash)=='json')
		{
			$data['data']['users'] = $usernames;
			$data['status']='success';

			echo json_encode($data);
		}
		else
		{
			foreach ($usernames as $username)
			{
				echo $username.chr(13);
			}
		}
	}


	/**
	 * User's WebDAV Account
	 * 
	 * Example
	 * index.php?option=com_users&format=xmlrpc&task=cloud.QryWebDAVAccount&name=demo
	 */
	function QryWebDAVAccount()
	{
			
		$hash = $this->hash;

		$server_name = JRequest::getVar('server_name','no name var',$hash);
		$server_password = JRequest::getVar('server_password','no name var',$hash);
		
		if (!$this->ServerAuth($server_name,$server_password))
		{
			return;
		}
		
		$username = strtolower(JRequest::getVar('name','no name var',$hash));

		$strOnlineSend = $this->ucloud_url()."/sapi/userinfo.php";
		$para = array('username' => $username);
		$return = json_decode($this->CurlCall($strOnlineSend, $para), 1);

		if (JRequest::getVar('type', 'text', $hash)=='json')
		{
			if ($return['status']=='success')
			{
				$data['name'] = $return['name'];
				$data['webdav'] = $return['name'];
				$data['block'] =  $return['block'];
				$data['status']='success';
			}
			else
			{
				$data = $return;
			}

			echo json_encode($data);
		}
		else
		{
			if ($return['status']=='success')
			{
				echo $username;
			}
		}
	}


	/**
	 * User Current Storage Usage
	 * 
	 * Example
	 * index.php?option=com_users&format=xmlrpc&task=cloud.QryCurrentUsage&name=demo
	 */
	function QryCurrentUsage()
	{
				
		$hash = $this->hash;

		$server_name = JRequest::getVar('server_name','no name var',$hash);
		$server_password = JRequest::getVar('server_password','no name var',$hash);
		
		if (!$this->ServerAuth($server_name,$server_password))
		{
			return;
		}

		$username = strtolower(JRequest::getVar('name','no name var',$hash));

		$strOnlineSend = $this->ucloud_url()."/api/used.php";
		$para = array('username' => $username);
		$json = $this->CurlCall($strOnlineSend, $para);
		$data = json_decode($json, 1);

		if (JRequest::getVar('type', 'text', $hash)=='json')
		{
			echo $json;
		}
		else
		{
			echo $data['size'];
		}
	}


	/**
	 * User's Product Name
	 * 
	 * Example
	 * index.php?option=com_users&format=xmlrpc&task=cloud.QryProductName&name=demo
	 */
	function QryProductName()
	{
				
		$hash = $this->hash;

		$server_name = JRequest::getVar('server_name','no name var',$hash);
		$server_password = JRequest::getVar('server_password','no name var',$hash);
		
		if (!$this->ServerAuth($server_name,$server_password))
		{
			return;
		}

		$username = strtolower(JRequest::getVar('name','no name var',$hash));

		//$strOnlineSend = $this->ucloud_url()."/sapi/userinfo.php";
		//$para = array('username' => $username);
		//$return = json_decode($this->CurlCall($strOnlineSend, $para), 1);
		
		$db = JFactory::getDBO();
		$query = "SELECT productname " .
				 "  FROM #__user_product " .
				 " WHERE username='$username' ";
		$db->setQuery( $query );
		
		$result = ($productname = $db->loadResult());

		if (JRequest::getVar('type', 'text', $hash)=='json')
		{
			if ($result)
			{
				$data['name'] = $username;
				$data['productname'] = $productname;
				$data['status'] = 'success';
			}
			else
			{
				$data = array('message'=>'Could not get user product name', 'status'=>'error');
			}

			echo json_encode($data);
		}
		else
		{
			if ($result)
			{
				echo $productname;
			}
		}
	}


	/**
	 * User Direct Web Login, Use Token Machanism
	 * 
	 * Example
	 * index.php?option=com_users&format=xmlrpc&task=cloud.WebLogin&name=demo
	 */
	function WebLogin()
	{
				
		$hash = $this->hash;

		$server_name = JRequest::getVar('server_name','no name var',$hash);
		$server_password = JRequest::getVar('server_password','no name var',$hash);
		
		if (!$this->ServerAuth($server_name,$server_password))
		{
			return;
		}

		$username = strtolower(JRequest::getVar('name','no name var',$hash));

		$strOnlineSend = $this->ucloud_url()."/sapi/directloginreq.php";
		$para = array('username' => $username);
		$json = $this->CurlCall($strOnlineSend, $para);
		$return = json_decode($json, 1);

		if (JRequest::getVar('type', 'text', $hash)=='json')
		{
			if ($return['status']=='success')
			{
				$token = $return['token'];
				$data['username'] = $username;
				$data['url'] = $this->ucloud_url()."/api/directlogin.php?token=".$token;
				$data['status'] = 'success';
				echo json_encode($data);
			}
			else
			{
				echo $json;
			}
		}
		else
		{
			if ($return['status']=='success')
			{
				$token = $return['token'];
				$uri = $this->ucloud_url()."/api/directlogin.php?token=".$token;
				echo $uri;
			}
		}
		
		if ($return['status']=='success')
		{
			// update lastvisitDate.
			$date = JFactory::getDate();
			$db = JFactory::getDBO();
			$query	= $db->getQuery(true);
			$query->update('#__users');
			$query->set('lastvisitDate = '.$db->Quote($date->toSql()));
			$query->where($db->quoteName('username') . "='" . $username ."'");
			$db->setQuery((string) $query);
			$db->execute();
		}
		
	}

	/**
	 * User Direct Login Cloud Web UI when user is login to Joomla, Use Token Machanism, But Don't Generate token
	 *
	 * Example
	 * index.php?option=com_users&format=xmlrpc&task=cloud.UserLogin
	 */
	function UserLogin()
	{
		$app = JFactory::getApplication();
		$user = JFactory::getUser();
        
        /**
         * redirect to cloud
         */
        if (($user->id <> 0) and (!$app->isAdmin()))
        {
            //request token from server
            $strOnlineSend = $this->ucloud_url() . "/sapi/directloginreq.php";
            $para = array('username' => $user->username);
            $token = json_decode($this->CurlCall($strOnlineSend, $para), 1);
 
            $strLocation = $this->ucloud_url() ."/api/directlogin.php?token=" . $token['token'];

            // Detect cookie language
            $lang = JRequest::getString($app->getHash('language'), null , 'cookie');
            if ($lang)
            {
                $lang = str_replace('-', '_', $lang);
                $strLocation .= '&lang=' . $lang;
            }

            // redirect to cloud
            header("Location: " . $strLocation );
            exit;
        }
        
	}


	/**
	 * Create New Account
	 * 
	 * Example
	 * index.php?option=com_users&format=xmlrpc&task=cloud.AccountCreate&name=demo&password=xxxx&email=the@e.mail&productname=U-Sync-30GB
	 */
	function AccountCreate()
	{
				
		$hash = $this->hash;
		
		$server_name = JRequest::getVar('server_name','no name var',$hash);
		$server_password = JRequest::getVar('server_password','no name var',$hash);
		
		if (!$this->ServerAuth($server_name,$server_password))
		{
			return;
		}

		//// Get the user data.
		$username=strtolower(JRequest::getVar('name'));
		$password=JRequest::getVar('password');
		$email=JRequest::getVar('email');

		// check product name exist or not
		$productname = JRequest::getVar('productname','no productname var',$hash);
		
		$db = JFactory::getDBO();
		$query = "SELECT productname" .
				 "  FROM #__product" .
				 " WHERE productname='".$productname."'";
		$db->setQuery( $query );
		
		if (!($productname === $db->loadResult()))
		{
			return;
		}

		/**
		 *  set user's product
		 */
		$query = "DELETE FROM #__user_product WHERE username = '".$username."'";
		$db->setQuery($query);
		$db->query();

		$query = "INSERT INTO #__user_product (username, productname) VALUES ('$username','$productname')";
		$db->setQuery($query);
		$db->query();

		//// If registration is disabled
		//if(JComponentHelper::getParams('com_users')->get('allowUserRegistration') == 0)
		//{
		//	$return = false;
		//}
		//else
		//{
			// Initialise variables.
			$app	= JFactory::getApplication();
			$model	= $this->getModel('Registration', 'UsersModel');

			$requestData["name"]=$username;
			$requestData["username"]=$username;
			$requestData["password1"]=$password;
			$requestData["password2"]=$password;
			$requestData["email1"]=$email;
			$requestData["email2"]=$email;

			// Validate the posted data.
			$form	= $model->getForm();
			if (!$form) {
				JError::raiseError(500, $model->getError());
				$return = false;
			}
			$data	= $model->validate($form, $requestData);

			//$token = JApplication::getHash(JUserHelper::genRandomPassword());
			//$salt = JUserHelper::getSalt('crypt-md5');
			//$hashedToken = md5($token.$salt).':'.$salt;

			//$data["groups"]="['2']";
			//$data["name"]="clouduser";
			//$data["username"]="clouduser";
			//$data["password1"]="123456";
			//$data["password2"]="123456";
			//$data["email1"]="clouduser@charles-pc.net";
			//$data["email2"]="clouduser@charles-pc.net";
			//$data["email"]="clouduser@charles-pc.net";
			//$data["password"]="123456";
			//$data["activation"]="e16cedf90af4794d1b8623376b5aa41b"; //$hashedToken
			//$data["block"]=1;

			// Check for validation errors.
			if ($data === false) {

				// Get the validation messages.
				$errors	= $model->getErrors();

				// Push up to three validation messages out to the user.
				for ($i = 0, $n = count($errors); $i < $n && $i < 3; $i++) {
					if ($errors[$i] instanceof Exception) {
						$app->enqueueMessage($errors[$i]->getMessage(), 'warning');
					} else {
						$app->enqueueMessage($errors[$i], 'warning');
					}
				}

				$return = false;
			}

            // prevent to send active e-mail
            $uParams = JComponentHelper::getParams('com_users');
            $uParams->setValue('useractivation', 0);
            
            // Attempt to save the data.
            $return = $model->register($data);

        //}

//      if (JRequest::getVar('type', 'text', $hash)=='json')
//      {
//          if ($return==false)
//          {
//              $result['data']['message']='Unable to create user';
//              $result['status']='error';
//          }
//          else
//          {
//              $result['data']['name']=$username;
//              $result['data']['message']=$return;
//              $result['status']='success';
//          }
//          echo json_encode($result);
//      }
//      else
//      {
            if ($return==false)
            {
                echo -1;
            }
            else
            {
                // activate user
                $query = "SELECT id" .
                         "  FROM #__users" .
                         " WHERE username='".$username."'";
                $db->setQuery( $query );

                if ($userid = $db->loadResult()) {
                    if($instance = JUser::getInstance($userid))
                    {
                        $instance->block = 0;
                        $instance->save(true);
                        echo 1;
                    }
                }
            }
//      }

	}


	/**
	 * Active Account
	 * 
	 * Example
	 * index.php?option=com_users&format=xmlrpc&task=cloud.AccountEnable&name=demo
	 */
	function AccountEnable()
	{
				
		$hash = $this->hash;

		$server_name = JRequest::getVar('server_name','no name var',$hash);
		$server_password = JRequest::getVar('server_password','no name var',$hash);
		
		if (!$this->ServerAuth($server_name,$server_password))
		{
			return;
		}

		$username = JRequest::getVar('name','no name var',$hash);

		$db = JFactory::getDBO();
		$query = "SELECT id" .
				 "  FROM #__users" .
				 " WHERE username='".$username."'";
		$db->setQuery( $query );

		$return = false;

		if ($userid = $db->loadResult()) {
			if($instance = JUser::getInstance($userid))
			{
				$instance->block = 0;
				$instance->save(true);
				$return = true;
			}
		}

		if (JRequest::getVar('type', 'text', $hash)=='json')
		{
			if ($return==false)
			{
				$result['data']['message']='Could not Enable user';
				$result['status']='error';
			}
			else
			{
				$result['data']['name']=$username;
				$result['status']='success';
			}
			echo json_encode($result);
		}
		else
		{
			if ($return==false)
			{
				echo -1;
			}
			else
			{
				echo 1;
			}
		}

	}


	/**
	 * Block Account
	 * 
	 * Example
	 * index.php?option=com_users&format=xmlrpc&task=cloud.AccountDisable&name=demo
	 */
	function AccountDisable()
	{
				
		$hash = $this->hash;

		$server_name = JRequest::getVar('server_name','no name var',$hash);
		$server_password = JRequest::getVar('server_password','no name var',$hash);
		
		if (!$this->ServerAuth($server_name,$server_password))
		{
			return;
		}

		$username = JRequest::getVar('name','no name var',$hash);

		$db = JFactory::getDBO();
		$query = "SELECT id" .
				 "  FROM #__users" .
				 " WHERE username='".$username."'";
		$db->setQuery( $query );

		$return = false;

		if ($userid = $db->loadResult()) {
			if($instance = JUser::getInstance($userid))
			{
				$instance->block = 1;
				$instance->save(true);
				$return = true;
			}
		}

		if (JRequest::getVar('type', 'text', $hash)=='json')
		{
			if ($return==false)
			{
				$result['data']['message']='Could not Disable user';
				$result['status']='error';
			}
			else
			{
				$result['data']['name']=$username;
				$result['status']='success';
			}
			echo json_encode($result);
		}
		else
		{
			if ($return==false)
			{
				echo -1;
			}
			else
			{
				echo 1;
			}
		}

	}


	/**
	 * User Password Rest
	 * 
	 * Example
	 * index.php?option=com_users&format=xmlrpc&task=cloud.AccountPwdReset&name=demo&password=xxxx
	 */
	function AccountPwdReset()
	{
		jimport('joomla.user.helper');
		$return = false;

				
		$hash = $this->hash;

		$server_name = JRequest::getVar('server_name','no name var',$hash);
		$server_password = JRequest::getVar('server_password','no name var',$hash);
		
		if (!$this->ServerAuth($server_name,$server_password))
		{
			return;
		}

		$username = strtolower(JRequest::getVar('name','no name var',$hash));
		$password = JRequest::getVar('password','no password var',$hash);

		// 將新密碼混合原salt加密
		$db		= JFactory::getDbo();
		$query	= $db->getQuery(true);

		$query->select('id, password, email');
		$query->from('#__users');
		$query->where('username = '.$db->Quote($username));

		$db->setQuery($query);
		if ($user = $db->loadObject())
		{
			$parts = explode(':',$user->password);
			$crypt = $oldpassword = $parts[0];
			$salt = @$parts[1];
			$newcrypt = JUserHelper::getCryptedPassword($password, $salt);
			$newpassword = $newcrypt.':'.$salt;

			// 更新資料庫
			$query->clear();
			$query	= $db->getQuery(true);
			$query->update('#__users');
			$query->set('password = '.$db->Quote($newpassword));
			$query->where('username = '.$db->Quote($username));
			$db->setQuery((string) $query);

			if (!$db->query())
			{
				JError::raiseError(500, $db->getErrorMsg());
			}
			else
			{
				if($instance = JUser::getInstance($user->id))
				{
					$instance->save(true); // trigger ucloud change the password
					$return = true;
				}
			}
		}

		if (JRequest::getVar('type', 'text', $hash)=='json')
		{
			if ($return==false)
			{
				$result['data']['message']='Unable to change user password';
				$result['status']='error';
			}
			else
			{
				$result['data']['name']=$username;
				$result['status']='success';
			}
			echo json_encode($result);
		}
		else
		{
			if ($return==false)
			{
				echo -1;
			}
			else
			{
				echo 1;
			}
		}

	}

    
    /**
     * Email 修改 API
     * 
     * Example
     * index.php?option=com_users&format=xmlrpc&task=cloud.AccountEmailUpdate&server_name=testtest&server_password=testtest&name=charles&email=charles@u-sync.com
     * @param   string  name   使用者名稱
     * @param   string  email  使用者email
     * @return  integer 1 成功, -1 User不存在, -2 此email有他人使用
     */
    function AccountEmailUpdate()
    {
        jimport('joomla.user.helper');
        $return = false;
                
        $hash = $this->hash;

        $server_name = JRequest::getVar('server_name','no name var',$hash);
        $server_password = JRequest::getVar('server_password','no name var',$hash);
        
        if (!$this->ServerAuth($server_name,$server_password))
        {
            return;
        }

        $username = JRequest::getString('name', null);
        $email = JRequest::getString('email', null);
        if ((!$username) or (!$email))
        {
            echo -1;
            exit;
        }
        
        $db = JFactory::getDbo();
        $query = "SELECT id" .
                 "  FROM #__users" .
                 " WHERE username='$username'";
        $db->setQuery( $query );

        if ($userid = $db->loadResult())
        {
            $query = "SELECT count(1) cnt" .
                     "  FROM #__users" .
                     " WHERE username<>'$username'" .
                     "   AND email = '$email'";
            $db->setQuery( $query );
            
            if ($db->loadResult() > 1)
            {   // 此 email 有其他人使用
                echo -2;
            }
            else
            {   // 更新用戶 email
                $instance = new JUser($userid);
                $instance->email = $email;
                JPluginHelper::importPlugin('user');
                $dispatcher = JDispatcher::getInstance();
                
                $query = "UPDATE #__users" .
                         "   SET email = '$email'" .
                         " WHERE username='$username'";
                $db->setQuery( $query );
                
                $db->execute();
                
                $dispatcher->trigger('onUserAfterSave', array($instance->getProperties(), false, true, $instance->getError()));
                // 更新並觸發plugin完成
                echo 1;
            }
        }
        else
        {   // user 不存在
            echo -1;
        }
    }


    /**
     * 暱稱 修改 API
     * example  index.php?option=com_users&format=xmlrpc&task=cloud.AccountNickNameUpdate&server_name=testtest&server_password=testtest&name=charles&nick=Charles
     * @param   string  name   使用者名稱 (資料庫中為 username)
     * @param   string  nick   使用者暱稱 (資料庫中為 name)
     * @return  integer 1 成功, -1 失敗 
     */
    function AccountNickNameUpdate()
    {
        jimport('joomla.user.helper');
        $return = false;
                
        $hash = $this->hash;

        $server_name = JRequest::getVar('server_name','no name var',$hash);
        $server_password = JRequest::getVar('server_password','no name var',$hash);
        
        if (!$this->ServerAuth($server_name,$server_password))
        {
            return;
        }

        $username = JRequest::getString('name', null);
        $nickname = JRequest::getString('nick', null);
        // 防呆及 sql injection
        if ((!$username) or (!$nickname) or (mysql_real_escape_string($nickname)<>$nickname))
        {
            echo -1;
            exit;
        }
        
        $db = JFactory::getDbo();
        $query = "UPDATE #__users" .
                 "   SET name='$nickname'" .
                 " WHERE username='$username'";
        $db->setQuery( $query );

        if ($db->execute()) {
            echo 1;
        }
        else
        {
            echo -1;    
        }
    }


	/**
	 * Delete User Account
	 * 
	 * Example
	 * index.php?option=com_users&format=xmlrpc&task=cloud.AccountDelete&name=demo
	 */
	function AccountDelete()
	{
				
		$hash = $this->hash;

		$server_name = JRequest::getVar('server_name','no name var',$hash);
		$server_password = JRequest::getVar('server_password','no name var',$hash);
		
		if (!$this->ServerAuth($server_name,$server_password))
		{
			return;
		}

		$username = JRequest::getvar('name');

		$db = JFactory::getDBO();
		$query = "SELECT id" .
				 "  FROM #__users" .
				 " WHERE username='".$username."' and usertype <> 'deprecated'"; // admin => deprecated
		$db->setQuery( $query );

		$return = false;

		if ($userid = $db->loadResult())
		{
			if($instance = JUser::getInstance($userid))
			{
				if($instance->delete())
				{
					$return = true;
				}
			}
		}

		if (JRequest::getVar('type', 'text', $hash)=='json')
		{
			if ($return==false)
			{
				$result['data']['message']='Unable to delete user';
				$result['status']='error';
			}
			else
			{
				$result['data']['name']=$username;
				$result['status']='success';
			}
			echo json_encode($result);
		}
		else
		{
			if ($return==false)
			{
				echo -1;
			}
			else
			{
				echo 1;
			}
		}

	}
	
	
	/**
	 *  Check Password for AJAX
	 *  
	 *  index.php?option=com_users&format=xmlrpc&task=cloud.CheckPwd&username=xxx&password=xxx
	 */
	function CheckPwd()
	{
		$hash = $this->hash;
		$username = JRequest::getVar('username','no username var',$hash);
		$password = JRequest::getVar('password','no password var',$hash);
	
		$db		= JFactory::getDbo();
		$query	= $db->getQuery(true);
	
		$query->select('id, password');
		$query->from('#__users');
		$query->where('username=' . $db->Quote($username));
	
		$db->setQuery($query);
		$result = $db->loadObject();
	
		if ($result) {
			$parts	= explode(':', $result->password);
			$crypt	= $parts[0];
			$salt	= @$parts[1];
			$testcrypt = JUserHelper::getCryptedPassword($password, $salt);
	
			if ($crypt == $testcrypt) {
				echo '1';
			} else {
				echo '-1';
			}
		}
	}
	
	/**
	 *  Method to log out a user.
	 *
	 *  index.php?option=com_users&format=xmlrpc&task=cloud.LogOut&username=xxx
	 */
	function LogOut()
	{
		$hash = $this->hash;
		$username = JRequest::getVar('username','no username var',$hash);

//		JSession::checkToken('request') or jexit(JText::_('JInvalid_Token'));

		$app = JFactory::getApplication();
		$db = JFactory::getDBO();
		$query = "SELECT id" .
				 "  FROM #__users" .
				 " WHERE username='".$username."' and usertype <> 'deprecated'"; // admin => deprecated
		$db->setQuery( $query );

		$result = false;
		if ($userid = $db->loadResult())
		{
			$options = array(
				'clientid' => ($userid) ? 0 : 1
			);

			$result = $app->logout($userid, $options);
		}
	
		echo $result ? 1 : -1;
	}
}

