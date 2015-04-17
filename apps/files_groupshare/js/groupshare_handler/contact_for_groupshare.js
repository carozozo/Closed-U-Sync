$(document).ready(function() {
    ContactForGroupShare.setDefault();
    ContactForGroupShare.setDefaultHandler();
});

var ContactForGroupShare = {
    setDefault : function() {
        AddContactIdInp.setStyle();
    },
    setDefaultHandler : function() {
        $("#addContactIdInp").on('focus', AddContactIdInp.focus);
        $("#addContactIdInp").on('blur', AddContactIdInp.blur);
        $("#addContactIdInp").on('keyup', AddContactIdInp.keyup);
        $("#addContactBtn").on('click', AddContactBtn.click);
    },
    addContact : function() {
        var contactId = $("#addContactIdInp").val().toLowerCase();
        //如果沒輸入暱稱，則預設為Id
        if (!contactId || contactId == $("#addContactIdInp").attr("alt")) {
            alert(t('contact', 'Please input userId/email'));
            return false;
        }
        $.post(OC.filePath("contact", "ajax", "contact.php"), {
            action : "addContact",
            contactId : contactId,
        }, function(data) {
            if (data.status == "success") {
                if (data.result == true) {
                    ContactMainTable.setContactList();
                    ContactForGroupShare.setDefault();
                    ContactForGroupShare.addNewContactToShared(contactId);
                } else {
                    // 如果要新增的聯絡人已在清單裡，則直接加到分享
                    if (data.result == 'User already in your contact list') {
                        ContactForGroupShare.addNewContactToShared(contactId);
                    } else {
                        alert(t('contact', data.result));
                    }
                }
            }
        });
    },
    addNewContactToShared : function(contactId) {
        var gids = Shared.getSharedGidsArray();
        var uids = Shared.getSharedUidsArray();
        if ($.inArray(contactId, uids) <= -1) {
            uids.push(contactId);
        }
        uids.sort(function(a, b) {
            return a > b;
        });
        ShareBtn.updateGroupShare(gids, uids);
    },
};

var AddContactIdInp = {
    focus : function() {
        if (!$(this).val() || $(this).val() == $(this).attr('alt')) {
            $(this).val('');
        } else {
            $(this).select();
        }
        $(this).css('color', "#000");
    },
    blur : function() {
        if (!$(this).val() || $(this).val() == $(this).attr('alt')) {
            $(this).val($(this).attr('alt'));
            $(this).css('color', "#ccc");
        }
    },
    keyup : function(e) {
        if (e.which == 13) {
            if ($(this).val() != $(this).attr("alt")) {
                ContactForGroupShare.addContact();
            } else {
                ContactForGroupShare.setDefault();
            }
        }
    },
    /**
     * 設置輸入欄位樣式
     */
    setStyle : function() {
        $("#addContactIdInp").val($("#addContactIdInp").attr('alt'));
        $("#addContactIdInp").css('color', "#ccc");
    },
};

var AddContactBtn = {
    click : function() {
        ContactForGroupShare.addContact();
    },
    /**
     * 設置輸入按鈕樣式
     */
    setStyle : function() {
        $("#addContactBtn").css('color', "#ccc");
    },
};
