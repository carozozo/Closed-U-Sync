<?php
/**
 * @version		$Id: email.php 20196 2011-03-04 02:40:25Z mrichey $
 * @package		plg_auth_email
 * @copyright	Copyright (C) 2005 - 2011 Michael Richey. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined('_JEXEC') or die;

jimport('joomla.plugin.plugin');

class plgAuthenticationEmail extends JPlugin
{
	/**
	 * This method should handle any authentication and report back to the subject
	 */
	function onUserAuthenticate($credentials, $options, &$response)
	{
		jimport('joomla.user.helper');

                jimport('joomla.version');
                $version = new JVersion;
                $version = explode('.',$version->getShortVersion());
                if($version[0] == 3) {
                    $success = JAuthentication::STATUS_SUCCESS;
                    $failure = JAuthentication::STATUS_FAILURE;
                } else {
                    $success = JAUTHENTICATE_STATUS_SUCCESS;
                    $failure = JAUTHENTICATE_STATUS_FAILURE;
                }
                
		$response->type = 'Joomla';
		// Joomla does not like blank passwords
		if (empty($credentials['password'])) {
			$response->status = $failure;
			$response->error_message = JText::_('JGLOBAL_AUTH_EMPTY_PASS_NOT_ALLOWED');
			return false;
		}

		// Initialise variables.
		$conditions = '';

		// Get a database object
		$db		= JFactory::getDbo();
		$query	= $db->getQuery(true);

		$query->select('id, password');
		$query->from('#__users');
		$query->where('email=' . $db->Quote($credentials['username']));

		$db->setQuery($query);
		$result = $db->loadObject();

		if ($result) {
			$parts	= explode(':', $result->password);
			$crypt	= $parts[0];
			$salt	= @$parts[1];
			$testcrypt = JUserHelper::getCryptedPassword($credentials['password'], $salt);
			if ($crypt == $testcrypt) {
				$user = JUser::getInstance($result->id); // Bring this in line with the rest of the system
                                $response->username = $user->username;
				$response->email = $user->email;
				$response->fullname = $user->name;
				if (JFactory::getApplication()->isAdmin()) {
					$response->language = $user->getParam('admin_language');
				}
				else {
					$response->language = $user->getParam('language');
				}
				$response->status = $success;
				$response->error_message = '';
			} else {
				$response->status = $failure;
				$response->error_message = JText::_('JGLOBAL_AUTH_INVALID_PASS');
			}
		} else {
			$response->status = $failure;
			$response->error_message = JText::_('JGLOBAL_AUTH_NO_USER');
		}
	}
}
