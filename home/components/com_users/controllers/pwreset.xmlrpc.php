<?php

class UsersControllerPwreset extends JController
{
    
    private $hash = 'default'; // post, get...


    /**
     *  Method to remind user password, send mail.
     *
     *  index.php?option=com_users&format=xmlrpc&task=pwreset.RemindSend&email=charles@u-sync.com
     */
    function RemindSend()
    {
        $hash = $this->hash;
        $email = JRequest::getVar('email','no email var',$hash);

        $config = JFactory::getConfig();

        // Find the user id for the given email address.
        $db = JFactory::getDBO();
        //$db = $this->getDbo();
        $query  = $db->getQuery(true);
        $query->select('id');
        $query->select('username');
        $query->from($db->quoteName('#__users'));
        $query->where($db->quoteName('email').' = '.$db->Quote($email));

        // Get the user object.
        $db->setQuery((string) $query);
        $user = $db->loadObject();
        $userId = $user->id;
        $username = $user->username;

        // Check for an error.
        if ($db->getErrorNum()) {
            $this->setError(JText::sprintf('COM_USERS_DATABASE_ERROR', $db->getErrorMsg()), 500);
            return false;
        }

        // Check for a user.
        if (empty($userId)) {
            $this->setError(JText::_('COM_USERS_INVALID_EMAIL'));
            return false;
        }

        // Get the user object.
        $user = JUser::getInstance($userId);

        // Make sure the user isn't blocked.
        if ($user->block) {
            $this->setError(JText::_('COM_USERS_USER_BLOCKED'));
            return false;
        }

        // Make sure the user isn't a Super Admin.
        if ($user->authorise('core.admin')) {
            $this->setError(JText::_('COM_USERS_REMIND_SUPERADMIN_ERROR'));
            return false;
        }
        
        // // Make sure the user has not exceeded the reset limit
        // if (!$this->checkResetLimit($user)) {
            // $resetLimit = (int) JFactory::getApplication()->getParams()->get('reset_time');
            // $this->setError(JText::plural('COM_USERS_REMIND_LIMIT_ERROR_N_HOURS', $resetLimit));
            // return false;
        // }
        // Set the confirmation token.
        $token = JApplication::getHash(JUserHelper::genRandomPassword());
        $salt = JUserHelper::getSalt('crypt-md5');
        $hashedToken = md5($token.$salt).':'.$salt;

        $user->activation = $hashedToken;

        // Save the user to the database.
        if (!$user->save(true)) {
            return new JException(JText::sprintf('COM_USERS_USER_SAVE_FAILED', $user->getError()), 500);
        }

        // Assemble the password reset confirmation link.
        $mode = $config->get('force_ssl', 0) == 2 ? 1 : -1;
        $itemid = UsersHelperRoute::getLoginRoute();
        $itemid = $itemid !== null ? '&Itemid='.$itemid : '';
//        $link = 'index.php?option=com_users&view=reset&layout=complete'.$itemid;
        $link = 'index.php?option=com_users&format=xmlrpc&task=pwreset.RemindConfirm'.$itemid;

        // Put together the email template data.
        $data = $user->getProperties();
        $data['fromname']   = $config->get('fromname');
        $data['mailfrom']   = $config->get('mailfrom');
        $data['sitename']   = $config->get('sitename');
        $data['link_text']  = JURI::base().$link;  //JRoute::_($link, false, $mode);
        $data['link_html']  = JRoute::_($link, true, $mode);
        $data['token']      = $token;

        $subject = JText::sprintf(
            'COM_USERS_EMAIL_PASSWORD_RESET_SUBJECT',
            $data['sitename']
        );

        $body = JText::sprintf(
            'COM_USERS_EMAIL_PASSWORD_RESET_BODY',
            $username, //$data['sitename'],
            $data['token'],
            $data['link_text'].'&username='.$username.'&token='.$data['token']
        );

        // Send the password reset request email.
        $return = JFactory::getMailer()->sendMail($data['mailfrom'], $data['fromname'], $user->email, $subject, $body);
        // Check for an error.
        if ($return !== true) {
            // return new JException(JText::_('COM_USERS_MAIL_FAILED'), 500);
            echo -1;
        }

        // return true;
        echo 1;
        
    }


    /**
     *  Method to remind user password, confirm mail.
     *
     *  index.php?option=com_users&format=xmlrpc&task=cloud.RemindConfirm&username=charles&token=
     */
    function RemindConfirm()
    {
        $hash = $this->hash;
        $username = JRequest::getVar('username','no username var',$hash);
        $token = JRequest::getVar('token','no token var',$hash);

        // Find the user id for the given token.
        $db = JFactory::getDBO();
        $query  = $db->getQuery(true);
        $query->select('activation');
        $query->select('id');
        $query->select('block');
        $query->from($db->quoteName('#__users'));
        $query->where($db->quoteName('username').' = '.$db->Quote($username));

        // Get the user id.
        $db->setQuery((string) $query);
        $user = $db->loadObject();

        // Check for an error.
        if ($db->getErrorNum()) {
            return new JException(JText::sprintf('COM_USERS_DATABASE_ERROR', $db->getErrorMsg()), 500);
        }

        // Check for a user.
        if (empty($user)) {
            $this->setError(JText::_('COM_USERS_USER_NOT_FOUND'));
            return false;
        }

        $parts  = explode( ':', $user->activation );
        $crypt  = $parts[0];
        if (!isset($parts[1])) {
            $this->setError(JText::_('COM_USERS_USER_NOT_FOUND'));
            return false;
        }
        $salt   = $parts[1];
        $testcrypt = JUserHelper::getCryptedPassword($token, $salt);

        // Verify the token
        if (!($crypt == $testcrypt))
        {
            $this->setError(JText::_('COM_USERS_USER_NOT_FOUND'));
            return false;
        }

        // Make sure the user isn't blocked.
        if ($user->block) {
            $this->setError(JText::_('COM_USERS_USER_BLOCKED'));
            return false;
        }

        // Push the user data into the session.
        $app = JFactory::getApplication();
        $app->setUserState('com_users.reset.token', $crypt.':'.$salt);
        $app->setUserState('com_users.reset.user', $user->id);
        
        // Check for a hard error.
        if ($return instanceof Exception)
        {
            // Get the error message to display.
            if ($app->getCfg('error_reporting')) {
                $message = $return->getMessage();
            } else {
                $message = JText::_('COM_USERS_RESET_CONFIRM_ERROR');
            }

            // Get the route to the next page.
            $itemid = UsersHelperRoute::getResetRoute();
            $itemid = $itemid !== null ? '&Itemid='.$itemid : '';
            $route  = 'index.php?option=com_users&view=reset&layout=confirm'.$itemid;

            // Go back to the confirm form.
            $this->setRedirect(JRoute::_($route, false), $message, 'error');
            return false;
        } elseif ($return === false) {
            // Confirm failed.
            // Get the route to the next page.
            $itemid = UsersHelperRoute::getResetRoute();
            $itemid = $itemid !== null ? '&Itemid='.$itemid : '';
            $route  = 'index.php?option=com_users&view=reset&layout=confirm'.$itemid;

            // Go back to the confirm form.
            $message = JText::sprintf('COM_USERS_RESET_CONFIRM_FAILED', $model->getError());
            $this->setRedirect(JRoute::_($route, false), $message, 'notice');
            return false;
        } else {
            // Confirm succeeded.
            // Get the route to the next page.
            $itemid = UsersHelperRoute::getResetRoute();
            $itemid = $itemid !== null ? '&Itemid='.$itemid : '';
            $route  = 'index.php?option=com_users&view=reset&layout=complete'.$itemid;

            // Proceed to step three.
            $this->setRedirect(JRoute::_($route, false));
            $this->redirect();
        }
    }


}