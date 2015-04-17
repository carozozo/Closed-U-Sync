<?php
/**
 * ownCloud - Skytek plugin
 *
 * @author Caro Huang
 * @copyright 2013 www.u-sync.com
 *
 * 針對Skytek(天方科技)
 * 成員的新增/修改/刪除等操作
 * 回傳值 -
 * 1  = 成功
 * 0  = 寫入失敗
 * -1 = 學校單位ID已存在
 * -2 = 學校單位ID不存在
 * -6 = user已存在
 * -7 = user不存在
 * -10= 參數不正確
 */

class OC_SkytekUser {
    /**
     * 新增使用者到 OwnCloud 系統中
     * @param user id
     * @param nickname
     * @param 校務單位 id
     * @param quota
     * @return int
     */
    static function addUser($userId, $nickname, $dpId, $quota = 0) {
        # 如果user已經存在
        if (OC_User::userExists($userId)) {
            return -6;
        }
        # 如果傳送過來的校務單位id不存在
        if (!OC_SkytekSystemGrop::checkDpidExists($dpId)) {
            return -2;
        }
        # 如果user產生失敗(預設 id 和 pwd 相同)
        if (!OC_User::createUser($userId, $userId)) {
            return 0;
        }
        if ($quota) {
            OC_UserQuota::setUserQuota($userId, $quota);
        }
        if ($nickname) {
            # 設置使用者暱稱
            OC_User::setUserNickname($userId, $nickname);
        } else {
            OC_User::setUserNickname($userId, $userId);
        }
        # 將user加入指定的system group
        self::addUserToSystemGroup($userId, $dpId);
        return 1;
    }

    /**
     * 更新使用者
     * @param user id
     * @param nickname
     * @param 校務單位 id
     * @param quota
     * @param 是否為新增
     * @return int
     */

    static function updateUser($userId, $nickname, $dpId, $quota = 0, $isAdd = true) {
        # 如果user不存在
        if (!OC_User::userExists($userId)) {
            return -7;
        }
        # 如果傳送過來的校務單位id不存在
        if (!OC_SkytekSystemGrop::checkDpidExists($dpId)) {
            return -2;
        }
        if ($quota) {
            OC_UserQuota::setUserQuota($userId, $quota);
        }
        if ($nickname) {
            # 設置使用者暱稱
            OC_User::setUserNickname($userId, $nickname);
        }
        # 更改user所屬的系統群組
        if ($isAdd and self::addUserToSystemGroup($userId, $dpId)) {
            return 1;
        }
        if (!$isAdd and self::removeUserFromSystemGroup($userId, $dpId)) {
            return 1;
        }
        return 0;
    }

    /**
     * 刪除使用者
     * @param user id
     * @return int
     */
    static function deleteUser($userId) {
        if (OC_User::deleteUser($userId)) {
            return 1;
        }
        return 0;
    }

    /**
     * 將user加入指定的system group
     * @param user id
     * @param 校務單位 id
     */
    private static function addUserToSystemGroup($userId, $dpId) {
        $systemGroupId = OC_SkytekSystemGrop::getSystemGroupIdByDpid($dpId);
        return OC_Contact_System_Group::addContactToSystemGroup($systemGroupId, $userId);
    }

    /**
     * 新曾user到指定的的系統群組
     * @param user id
     * @param 校務單位 id
     */
    private static function removeUserFromSystemGroup($userId, $dpId) {
        $systemGroupId = OC_SkytekSystemGrop::getSystemGroupIdByDpid($dpId);
        return OC_Contact_System_Group::removeContactFromSystemGroup($systemGroupId, $userId);
    }

}
?>