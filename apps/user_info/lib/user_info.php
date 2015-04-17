<?php
/**
 * ownCloud - U-Sync User Info
 *
 * @author Caro Huang
 * @copyright 2013 www.u-sync.com
 *
 * Joomla 使用者相關資訊處理(只適用於p開頭的server type)
 */

class OC_UserInfo {
    const appId = 'user_info';

    /***************************** 暱稱相關 *****************************/
    /**
     * 更新使用者暱稱
     * @param 舊密碼,新密碼
     * @return String
     */
    static function changeNickname($nickname = null) {
        if ($nickname) {
            $userId = OC_User::getUser();
            $updateUserNickname = self::updateUserNickname($userId, $nickname);
            if ($updateUserNickname) {
                return 'Update succeed';
            }
            return 'Update failed';
        }
        return 'Please insert nickname';
    }

    /**
     * 更新 Joomla / OwnCloud 使用者暱稱
     * @param user id nickname
     * @return boolean
     */
    private static function updateUserNickname($userId, $nickname) {
        $updateUserNickname = 1;
        # 先取得原先的nickname，做為資料回復用
        $oldNickname = OC_User::getUserNickname();
        # 如果server包含Joomla,則呼叫Joomla更改密碼
        $serverMainType = OC_Helper::serverMainType();
        if ($serverMainType == 'p') {
            # Joomla 的 user nickname 更新成功
            $updateUserNickname = self::curlJoomlaUpdateUserNickname($userId, $nickname);
        }
        # OwnCloud 的 user nickname 更新成功
        if ($updateUserNickname == 1) {
            if (OC_User::setUserNickname($userId, $nickname)) {
                return true;
            }
            if ($serverMainType == 'p') {
                # OwnCloud 的 user nickname 更新失敗, 回復 Joomla 的 user nickname
                self::curlJoomlaUpdateUserNickname($userId, $oldNickname);
            }
        }
        return false;
    }

    /**
     * 呼叫修改暱稱API
     * @param user id, nickname
     * @return string
     */
    private static function curlJoomlaUpdateUserNickname($userId, $nickname) {
        $protocol = OC_Helper::getProtocol();
        $toURL = $protocol . $_SERVER['HTTP_HOST'] . "/home/index.php?option=com_users&format=xmlrpc&task=cloud.AccountNickNameUpdate&server_name=key1&server_password=dad87jfoda342rfd&name=$userId&nick=$nickname";
        return $result = OC_Helper::curlToServer($toURL, true, false);
    }

    /***************************** 密碼相關 *****************************/
    /**
     * 驗證並更新使用者密碼
     * @param 舊密碼,新密碼
     * @return String
     */
    static function changePwd($oldPwd = null, $newPwd = null) {
        $oldPwd = trim($oldPwd);
        $newPwd = trim($newPwd);
        if ($oldPwd && $newPwd) {
            if (self::checkPwdSymbol($newPwd)) {
                $userId = OC_User::getUser();
                if (self::checkPwd($userId, $oldPwd)) {
                    $updateUserPwd = self::updateUserPwd($userId, $oldPwd, $newPwd);
                    if ($updateUserPwd) {
                        return 'Update succeed';
                    }
                    return 'Update failed';
                }
                return 'Wrong old password';
            }
            return 'Password has not allow symbol';
        }
        return 'Please insert password';
    }

    /**
     * 確認新密碼中是否有不符合規定的符號
     * @param pwd
     * @return bool
     */
    private static function checkPwdSymbol($pwd) {
        $fc = OC_Filesystem::$forbiddenCharArray;
        foreach ($fc as $index => $char) {
            if (preg_match('/' . preg_quote($char) . '+/', $pwd)) {
                return false;
            }
        }
        return true;
    }

    /**
     * 確認舊密碼是否正確
     * @param pwd
     * @return bool
     */
    private static function checkPwd($userId, $pwd) {
        $serverMainType = OC_Helper::serverMainType();
        # 如果server是包含Joomla的，則和Joomla確認
        if ($serverMainType == 'p') {
            if (self::curlJoomlaCheckUserPwd($userId, $pwd) == 1) {
                return true;
            }
        } else {
            # 呼叫OwnCloud本身的確認密碼
            if (OC_User::checkPassword($userId, $pwd)) {
                return true;
            }
        }
        return false;
    }

    /**
     * 更新 Joomla / OwnCloud 使用者密碼
     * @param 帳號,密碼
     * @return boolean
     */
    private static function updateUserPwd($userId, $oldPwd, $newPwd) {
        $updateUserPwd = 1;
        # 如果server包含Joomla,則呼叫Joomla更改密碼
        $serverMainType = OC_Helper::serverMainType();
        if ($serverMainType == 'p') {
            # Joomla 的 user pwd 更新成功
            $updateUserPwd = self::curlJoomlaUpdateUserPwd($userId, $newPwd);
        }

        if ($updateUserPwd == 1) {
            # OwnCloud 的 user pwd 更新成功
            if (OC_User::setPassword($userId, $newPwd)) {
                return true;
            }
            if ($serverMainType == 'p') {
                # OwnCloud 的 user pwd 更新失敗, 回復 Joomla 的 user pwd
                self::curlJoomlaUpdateUserPwd($userId, $oldPwd);
            }
        }
        return false;
    }

    /**
     * 呼叫確認密碼是否正確API(API變數用post以確保安全性)
     * 修改成功會回傳1,失敗會回傳-1
     * @param user id, password
     * @return string
     */
    private static function curlJoomlaCheckUserPwd($userId, $pwd) {
        # Joomla 的 user pwd 更新之後, 不會更新 OwnCloud 的 user pwd
        $protocol = OC_Helper::getProtocol();
        $toURL = $protocol . $_SERVER['HTTP_HOST'] . "/home/index.php";
        # 因 Joomla 更新後， postField 的方式會產生不明錯誤，所以暫時不使用
        // $postFields = array(
            // 'option' => 'com_users',
            // 'format' => 'xmlrpc',
            // 'task' => 'cloud.CheckPwd',
            // 'username' => $userId,
            // 'password' => $pwd,
        // );
        // return $result = OC_Helper::curlToServer($toURL, true, false, $postFields);
        $toURL = $protocol . $_SERVER['HTTP_HOST'] . "/home/index.php?option=com_users&format=xmlrpc&task=cloud.CheckPwd&username=$userId&password=$pwd";
        return $result = OC_Helper::curlToServer($toURL, true, false);
    }

    /**
     * 呼叫修改密碼API
     * 修改成功會回傳1,失敗會回傳-1
     * @param user id, password
     * @return string
     */
    private static function curlJoomlaUpdateUserPwd($userId, $pwd) {
        # Joomla 的 user pwd 更新之後, 不會更新 OwnCloud 的 user pwd
        $protocol = OC_Helper::getProtocol();
        $toURL = $protocol . $_SERVER['HTTP_HOST'] . "/home/index.php?option=com_users&format=xmlrpc&task=cloud.AccountPwdReset&name=$userId&password=$pwd";
        return $result = OC_Helper::curlToServer($toURL, true, false);
    }

    /***************************** 密碼相關 *****************************/
    /**
     * 確認email是否已驗證(for U-Sync Server)
     * @param email
     * @return String
     */
    static function emailIsVaildated() {
        $l = new OC_L10N(self::appId);
        $userId = OC_User::getUser();
        # 如果server是U-Sync,則呼叫Joomla確認email是否已驗證
        $serverType = OC_Helper::serverType();
        if ($serverType == 'p2') {
            $vaildateUserEmail = self::curlJoomlaVaildateUserEmail($userId);
            if ($vaildateUserEmail == 1) {
                return 'Vaildated';
            }
            if ($vaildateUserEmail == -1) {
                return 'Vaildating';
            }
            return 'Not vaildated';
        }
        # 不是U-Sync系統，不需要驗證
        return '';
    }

    /**
     * 呼叫修改email API
     * email已驗證會回傳1，驗證中(已寄信，但user沒收信)會回傳-1，未驗證會回傳0
     * @param user id, password
     * @return string
     */
    private static function curlJoomlaVaildateUserEmail($userId) {
        $protocol = OC_Helper::getProtocol();
        $toURL = $protocol . $_SERVER['HTTP_HOST'] . "/home/index.php?option=com_usync&format=xmlrpc&task=emailvalidate.emailValidated&name=$userId";
        return $result = OC_Helper::curlToServer($toURL, true, false);
    }

    /**
     * 更新使用者email
     * U-Sync環境(server type = p2)時，還會寄發驗證信給user，所以回覆的成功訊息不同
     * @param email
     * @return String
     */
    static function changeEmail($email = null, $vaildateMessColor = 'red') {
        if ($email) {
            if (self::checkEmailSymbol($email)) {
                $userId = OC_User::getUser();
                # 新email和舊emil相同，而且已驗證，直接回傳更新成功
                $oldEmail = OC_User::getUserEmail();
                if ($oldEmail == $email && $vaildateMessColor == 'green') {
                    return 'Email not changed';
                }

                $updateUserEmail = self::updateUserEmail($userId, $email);
                if ($updateUserEmail) {
                    $serverType = OC_Helper::serverType();
                    if ($serverType == 'p2') {
                        # U-Sync要回覆的成功訊息
                        $mess = 'Please check your Email to vaildate';
                        return $mess;
                    }
                    # 其它server回覆的成功訊息
                    return 'Update succeed';
                }
                return 'Update failed';
            }
            return 'Email has not allow symbol';
        }
        return 'Please insert email';
    }

    /**
     * 確認email中是否有不符合規定的符號
     * @param pwd
     * @return bool
     */
    private static function checkEmailSymbol($email) {
        $fc = OC_Filesystem::$forbiddenCharArray;
        foreach ($fc as $index => $char) {
            if (preg_match('/' . preg_quote($char) . '+/', $email)) {
                return false;
            }
        }
        # email中沒有「@」或「.」
        if (strpos($email, '@') === false || strpos($email, '.') === false) {
            return false;
        }
        return true;
    }

    /**
     * 更新 Joomla or OwnCloud email
     * 在server main type 為 p 的情形下，user email不需要存放在OwnCloud中
     * 而U-Sync環境(server type = p2)時，還會寄發驗證信給user
     * @param user id, email
     * @return boolean
     */
    private static function updateUserEmail($userId, $email) {
        # 如果server包含Joomla,則呼叫Joomla更改email
        $serverMainType = OC_Helper::serverMainType();
        if ($serverMainType == 'p') {
            if (self::curlJoomlaUpdateUserEmail($userId, $email) == 1) {
                return true;
            }
            return false;
        } else {
            # 不含Joomla系統
            return OC_User::setUserEmail($userId, $email);
        }
    }

    /**
     * 呼叫修改 joomla email API
     * 修改成功會回傳1,失敗會回傳-1
     * @param user id, password
     * @return string
     */
    private static function curlJoomlaUpdateUserEmail($userId, $email) {
        $protocol = OC_Helper::getProtocol();
        $toURL = $protocol . $_SERVER['HTTP_HOST'] . "/home/index.php?option=com_users&format=xmlrpc&task=cloud.AccountEmailUpdate&server_name=key1&server_password=dad87jfoda342rfd&name=$userId&email=$email";
        $result = OC_Helper::curlToServer($toURL, true, false);
        return $result;
    }

}
?>