<?php
require_once ('../../../lib/base.php');

OC_JSON::checkLoggedIn();
OC_JSON::checkAppEnabled('contact');

try {
    $action = $_REQUEST['action'];

    switch($action) {
        case 'addContacts' :
            $contacts = $_REQUEST['contacts'];
            $contactArr = preg_split('#'.preg_quote(';').'#', $contacts, 0, PREG_SPLIT_NO_EMPTY);
            foreach ($contactArr as $index => $contactId) {
                # 將Id開頭轉大寫，設為nickname
                $nickName = OC_User::getUserNickname($contactId);
                $contact = new OC_Contact('', $contactId, $nickName);
                $result = $contact -> addContact();
            }
            OC_JSON::success(array('result' => $result));
            break;
        default :
            $groupName = NULL;
            $contactId = NULL;
            $contactNickname = NULL;
            if (isset($_REQUEST['groupName']))
                $groupName = $_REQUEST['groupName'];
            if (isset($_REQUEST['contactId']))
                $contactId = $_REQUEST['contactId'];
            if (isset($_REQUEST['contactNickname']))
                $contactNickname = $_REQUEST['contactNickname'];

            $contactObj = new OC_Contact($groupName, $contactId, $contactNickname);
            $result = $contactObj -> $action();
            OC_JSON::success(array('result' => $result));
            break;
    }

} catch(exception $e) {
    OC_JSON::error();
}
?>