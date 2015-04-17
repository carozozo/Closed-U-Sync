$(document).ready(function() {
    // ContactInGroup.setDefault();
    $("#contactInGroupMainTable").hide();
    ContactInGroup.setDefaultHandler();
});
/**
 * GroupShare中，群組底下的聯絡人清單
 */
var ContactInGroup = {
    /**
     * 設置初始狀態
     */
    setDefault : function() {
        SelectAllContactInGroup.unSelectAll();
    },
    /**
     * 設置操作
     */
    setDefaultHandler : function() {
        $("#selectAllContactInGroup").on('click', SelectAllContactInGroup.click);
        $("#contactInGroupCloseSpan").on('click', ContactInGroupCloseSpan.click);
    },
    /**
     * 設置清單的背景顏色
     */
    setBgColor : function(obj) {
        if (obj.hasClass("beSelected")) {
            obj.css("background-color", ContactInGroupParent.groupStyleObj.selectedBgColor);
        } else if (obj.hasClass("beOvered")) {
            obj.css("background-color", ContactInGroupParent.groupStyleObj.activeBgColor);
        } else {
            obj.css("background-color", ContactInGroupParent.groupStyleObj.defaultBgColor);
        }
    },
    /**
     * 取得群組裡的成員清單
     * @return array
     */
    getContactListByGroupId : function() {
        var contactList;
        var groupId = ContactInGroupParent.selectedGroupId;
        ContactInGroupMainTable.showLoading();
        $.ajax({
            type : 'POST',
            url : OC.filePath("contact", "ajax", "contact_group.php"),
            data : {
                action : "getContactListByGroupId",
                groupId : groupId,
            },
            async : false,
            success : function(data) {
                if (data.status == "success") {
                    contactList = data.result;
                }
            }
        });
        return contactList;
    },
    /**
     * 取得系統群組裡的成員清單
     * @return array
     */
    getContactListBySystemGroupId : function() {
        var contactList;
        var systemGroupId = ContactInGroupParent.selectedGroupId;
        ContactInGroupMainTable.showLoading();
        $.ajax({
            type : 'POST',
            url : OC.filePath("contact", "ajax", "contact_system_group.php"),
            data : {
                action : "getContactListBySystemGroupId",
                systemGroupId : systemGroupId,
            },
            async : false,
            success : function(data) {
                if (data.status == "success") {
                    contactList = data.result;
                }
            }
        });
        return contactList;
    },
    /**
     * 取得所有的被選取的聯絡人 id
     * @return array
     */
    getSelectedUidsArray : function() {
        var uids = Array();
        $("#contactInGroupListTable .beSelected").each(function() {
            var contactId = $(this).find("#contactId").val();
            contactId = $.base64.decode(contactId);
            uids.push(contactId);
        });
        return uids;
    },
};

/**
 * 存放該清單所屬的group/system group相關資料
 */
var ContactInGroupParent = {
    /**
     * 被選取的group id
     */
    selectedGroupId : null,
    /**
     * 被選取的group name
     */
    selectedGroupName : null,
    /**
     * 存放要顯示的style物件
     */
    groupStyleObj : null,
    /**
     * 存放上一層的tab物件
     */
    groupTabObj : null,
    /**
     * 要取得清單的function
     */
    getContactListFun : null,
};

/**
 * 成員清單主視窗
 */
var ContactInGroupMainTable = {
    /**
     * 將取得的群組成員清單放到主視窗
     */
    setContactListByGroupId : function() {
        // 取得該列表所屬的群組名稱
        var groupName = ContactInGroupParent.selectedGroupName;
        var contactList = ContactInGroupParent.getContactListFun();
        // 套入視窗樣式
        ContactInGroupMainTable.setStyle();
        // 將所屬的群組名稱寫入
        $('#groupNameSpan').html(groupName);
        // 除了樣版之外其它的tr都刪除
        $("#contactInGroupListTable tr:gt(0)").remove();
        $.each(contactList, function(index, val) {
            var contact = val['contact'];
            var nickname = val['nickname'];
            if (contact.trim() != "") {
                var cloneTr = $("#contactInGroupListTable tr:first").clone();
                // 將聯絡人id做加密
                contact = $.base64.encode(contact);
                cloneTr.find("#contactId").val(contact);
                cloneTr.find("#nickname").html(nickname);
                cloneTr.appendTo("#contactInGroupListTable");
                cloneTr.on('mouseout', ContactInGroupTr.mouseout);
                cloneTr.on('mouseover', ContactInGroupTr.mouseover);
                cloneTr.on('click', ContactInGroupTr.click);
            }
        });
        ContactInGroupMainTable.showList();
    },
    /**
     * 設置樣式
     */
    setStyle : function() {
        $("#contactInGroupMainTable tr").css('background-color', ContactInGroupParent.groupStyleObj.defaultBgColor);
        $("#contactInGroupMainTable .titleTr").css('color', ContactInGroupParent.groupStyleObj.titleColor);
    },
    /**
     * 顯示loading狀態
     */
    showLoading : function() {
        SelectAllContactInGroup.unSelectAll();
        $("#contactInGroupMainTable").show();
        $("#contactInGroupLoaginImg").show();
        $("#contactInGroupListTable").hide();
    },
    /**
     * 顯示列表
     */
    showList : function() {
        // 隱藏樣版
        $("#contactInGroupListTable tr:first").hide();
        $("#contactInGroupListTable tr:gt(0)").show();
        $("#contactInGroupListTable").show();
        $("#contactInGroupLoaginImg").hide();
    },
};

/**
 * 成員清單[全選checkbox]
 */
var SelectAllContactInGroup = {
    /**
     * 設置點選時的動作
     */
    click : function() {
        if ($(this).prop("checked")) {
            SelectAllContactInGroup.selectAll();
        } else {
            SelectAllContactInGroup.unSelectAll();
        }
    },
    /**
     * 全選成員
     */
    selectAll : function() {
        //樣版是隱藏的，所以選擇看的到的列表
        $(".contactInGroupTr:visible").each(function(index) {
            $(this).addClass("beSelected");
            ContactInGroup.setBgColor($(this));
        });
    },
    /**
     * 不選所有成員
     */
    unSelectAll : function() {
        $("#selectAllContactInGroup").attr("checked", false);
        $(".contactInGroupTr:visible").each(function(index) {
            $(this).removeClass("beSelected");
            ContactInGroup.setBgColor($(this));
        });
    },
};

/**
 * 成員清單裡面的列表操作
 */
var ContactInGroupTr = {
    /**
     * 設置滑鼠移到上面時的狀態
     */
    mouseover : function() {
        $(this).addClass("beOvered");
        ContactInGroupParent.groupStyleObj.setBgColor($(this));
    },
    /**
     * 設置滑鼠移開時的狀態
     */
    mouseout : function() {
        $(this).removeClass("beOvered");
        ContactInGroupParent.groupStyleObj.setBgColor($(this));
    },
    /**
     * 設置滑鼠移按下時的動作
     */
    click : function(event) {
        if ($(this).hasClass("beSelected"))
            $(this).removeClass("beSelected");
        else
            $(this).addClass("beSelected");
        ContactInGroupParent.groupStyleObj.setBgColor($(this));
    },
};
/**
 * 成員清單裡面的關閉按鈕
 */
var ContactInGroupCloseSpan = {
    click : function() {
        ContactInGroupParent.groupTabObj.click();
    },
};
