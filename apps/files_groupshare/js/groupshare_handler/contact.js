$(document).ready(function() {
    Contact.setDefault();
    Contact.setDefaultHandler();
    // $("#contactMainTable").hide();
});

var Contact = {
    /**
     * 設置初始狀態
     */
    setDefault : function() {
        ContactTab.setStyle();
        ContactMainTable.setStyle();
        SelectAllContact.unSelectAll();
        ContactMainTable.setContactList();
    },
    /**
     * 指定操作
     */
    setDefaultHandler : function() {
        $("#contactTab").on('click', ContactTab.click);
        $("#selectAllContact").on('click', SelectAllContact.click);
    },
    getContactList : function() {
        var contactList;
        $.ajax({
            type : 'POST',
            url : OC.filePath("contact", "ajax", "contact.php"),
            data : {
                action : "getContactList",
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
    sortNickname : function(result) {
        var resultArray = new Array();
        $.each(result, function(index, val) {
            var contact = val.contact;
            var nickname = (val.nickname) ? val.nickname : contact;
            resultArray[index] = new Array();
            resultArray[index][0] = nickname;
            resultArray[index][1] = contact;
        });
        return resultArray.sort();
    },
    /**
     * 取得所有的被選取的contact id
     * @return array
     */
    getSelectedUidsArray : function() {
        var uids = Array();
        $("#contactListTable .beSelected").each(function() {
            var val = $.base64.decode($(this).find("#contactId").val());
            uids.push(val);
        });
        return uids;
    },
};

/**
 * 聯絡人樣式
 */
var ContactStyle = {
    /**
     * 樣式顏色
     */
    defaultBgColor : '#EDC9A7',
    activeBgColor : '#FFFFFF',
    selectedBgColor : '#CEA683',
    titleColor : '#774713',
    /**
     * 設置背景色
     */
    setBgColor : function(obj) {
        if (obj.hasClass("beSelected")) {
            obj.css("background-color", ContactStyle.selectedBgColor);
        } else if (obj.hasClass("beOvered")) {
            obj.css("background-color", ContactStyle.activeBgColor);
        } else {
            obj.css("background-color", ContactStyle.defaultBgColor);
        }
    },
};

/**
 * 聯絡人Tab標籤
 */
var ContactTab = {
    /**
     * 設置滑鼠移按下時的動作
     */
    click : function() {
        if ($("#contactMainTable").is(':hidden')) {
            Contact.setDefault();
            $("#contactMainTable").show();
            $("#systemGroupMainTable").hide();
            $("#groupMainTable").hide();
            $("#contactInGroupMainTable").hide();
        }
    },
    /**
     * 設置Tab樣式
     */
    setStyle : function() {
        $('#contactTab').css('background-color', ContactStyle.defaultBgColor);
    },
};

/**
 * 聯絡人清單主要視窗
 */
var ContactMainTable = {
    /**
     * 將取得的聯絡人列表，放到tabe中
     */
    setContactList : function() {
        ContactMainTable.showLoaging();
        $("#contactListTable tr:gt(0)").remove();
        var contactList = Contact.getContactList();
        $.each(contactList, function(index, val) {
            var contact = val['contact'];
            var nickname = val['nickname'];
            var cloneTr = $("#contactListTable tr:first").clone();
            cloneTr.find(".contactTd #contactId").val($.base64.encode(contact));
            cloneTr.find(".contactTd #nickname").html(nickname);
            cloneTr.appendTo("#contactListTable");
            cloneTr.on('mouseover', ContactTr.mouseover);
            cloneTr.on('mouseout', ContactTr.mouseout);
            cloneTr.on('click', ContactTr.click);
        });
        ContactMainTable.showList();
    },
    /**
     * 設置視窗樣式
     */
    setStyle : function() {
        $("#contactMainTable").css('background-color', ContactStyle.defaultBgColor);
        $("#contactMainTable .titleTr").css('color', ContactStyle.titleColor);
    },
    /**
     * 顯示loading畫面
     */
    showLoaging : function() {
        $("#contactListLoaginImg").show();
        $("#contactListTable").hide();
    },
    /**
     * 顯示列表
     */
    showList : function() {
        $("#contactListTable tr:first").hide();
        $("#contactListTable tr:gt(0)").show();
        $("#contactListTable").show();
        $("#contactListLoaginImg").hide();
    },
};

/**
 * 聯絡人[全選checkbox]
 */
var SelectAllContact = {
    /**
     * 設置點選時的動作
     */
    click : function() {
        if ($(this).prop("checked")) {
            SelectAllContact.selecteAll();
        } else {
            SelectAllContact.unSelectAll();
        }
    },
    /**
     * 全選聯絡人
     */
    selecteAll : function() {
        $(".contactTr:visible").each(function() {
            $(this).addClass("beSelected");
            ContactStyle.setBgColor($(this));
        });
    },
    /**
     * 不選所有聯絡人
     */
    unSelectAll : function() {
        $("#selectAllContact").attr("checked", false);
        $(".contactTr:visible").each(function() {
            $(this).removeClass("beSelected");
            ContactStyle.setBgColor($(this));
        });
    },
};

/**
 * 聯絡人的列表操作
 */
var ContactTr = {
    /**
     * 設置滑鼠移到上面時的狀態
     */
    mouseover : function() {
        $(this).addClass("beOvered");
        ContactStyle.setBgColor($(this));
    },
    /**
     * 設置滑鼠移開時的狀態
     */
    mouseout : function() {
        $(this).removeClass("beOvered");
        ContactStyle.setBgColor($(this));
    },
    /**
     * 設置滑鼠移按下時的動作
     */
    click : function(event) {
        if (!$(this).hasClass("beSelected")) {
            $(this).addClass("beSelected");
        } else {
            $(this).removeClass("beSelected");
        }
        ContactStyle.setBgColor($(this));
    },
};
