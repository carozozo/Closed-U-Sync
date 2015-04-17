$(document).ready(function() {
    ContactGroup.setDefault();
    ContactGroup.setDefaultHandler();
    $("#groupMainTable").hide();
});
/**
 * GroupShare中，群組相關操作
 */
var ContactGroup = {
    /**
     * 設置初始狀態
     */
    setDefault : function() {
        GroupTab.setStyle();
        GroupMainTable.setStyle();
        SelectAllGroup.unSelectAll();
        GroupMainTable.setGroupList();
    },
    /**
     * 指定操作
     */
    setDefaultHandler : function() {
        $("#groupTab").on('click', GroupTab.click);
        $("#selectAllGroup").on('click', SelectAllGroup.click);
    },
    /**
     * 取得系統群組列表
     * @return array
     */
    getGroupList : function() {
        var groupList;
        $.ajax({
            type : 'POST',
            url : OC.filePath("contact", "ajax", "contact_group.php"),
            data : {
                action : "getGroupList",
            },
            async : false,
            success : function(data) {
                if (data.status == "success") {
                    groupList = data.result;
                }
            }
        });
        return groupList;
    },
    /**
     * 取得所有的被選取的system group id
     * @return array
     */
    getSelectedGidsArray : function() {
        var gids = Array();
        $("#groupListTable .beSelected").each(function() {
            var val = $(this).find("#groupId").val();
            gids.push(val);
        });
        return gids;
    },
};

/**
 * 系統群組樣式
 */
var ContactGroupStyle = {
    /**
     * 樣式顏色
     */
    defaultBgColor : '#CBD4B3',
    activeBgColor : '#FFFFFF',
    selectedBgColor : '#99A77E',
    titleColor : '#566635',
    /**
     * 設置背景色
     */
    setBgColor : function(obj) {
        if (obj.hasClass("beSelected")) {
            obj.css("background-color", ContactGroupStyle.selectedBgColor);
        } else if (obj.hasClass("beOvered")) {
            obj.css("background-color", ContactGroupStyle.activeBgColor);
        } else {
            obj.css("background-color", ContactGroupStyle.defaultBgColor);
        }
    },
};

/**
 * 系統群組Tab標籤
 */
var GroupTab = {
    /**
     * 設置滑鼠移按下時的動作
     */
    click : function() {
        if ($("#groupMainTable").is(':hidden')) {
            ContactGroup.setDefault();
            $("#groupMainTable").show();
            $("#systemGroupMainTable").hide();
            $("#contactMainTable").hide();
            $("#contactInGroupMainTable").hide();
        }
    },
    /**
     * 設置Tab樣式
     */
    setStyle : function() {
        $('#groupTab').css('background-color', ContactGroupStyle.defaultBgColor);
    },
};

/**
 * 群組主要視窗
 */
var GroupMainTable = {
    /**
     * 將取得的群組列表，放到tabe中
     */
    setGroupList : function() {
        GroupMainTable.showLoaging();
        $("#groupListTable tr:gt(0)").remove();
        var groupList = ContactGroup.getGroupList();
        $.each(groupList, function(key, val) {
            var name = val['groupName'];
            var id = val['groupId'];
            var cloneTr = $("#groupListTable tr:first").clone();
            var btn = cloneTr.find('.groupContentBtn');
            cloneTr.find("#groupId").val(id);
            cloneTr.find("#groupName").html(name);
            cloneTr.find('.groupContentBtn').hide();
            cloneTr.appendTo("#groupListTable");
            cloneTr.on('mouseover', GroupTr.mouseover);
            cloneTr.on('mouseout', GroupTr.mouseout);
            cloneTr.on('click', GroupTr.click);
            btn.on('click', GroupContentBtn.click);
        });
        GroupMainTable.showList();
    },
    /**
     * 設置視窗樣式
     */
    setStyle : function() {
        $("#groupMainTable").css('background-color', ContactGroupStyle.defaultBgColor);
        $("#groupMainTable .titleTr").css('color', ContactGroupStyle.titleColor);
    },
    /**
     * 顯示loading畫面
     */
    showLoaging : function() {
        $("#groupListLoaginImg").show();
        $("#groupListTable").hide();
    },
    /**
     * 顯示列表
     */
    showList : function() {
        $("#groupListTable tr:first").hide();
        $("#groupListTable tr:gt(0)").show();
        $("#groupListTable").show();
        $("#groupListLoaginImg").hide();
    },
};

/**
 * 群組[全選checkbox]
 */
var SelectAllGroup = {
    /**
     * 設置點選時的動作
     */
    click : function() {
        if ($(this).prop("checked")) {
            SelectAllGroup.selecteAll();
        } else {
            SelectAllGroup.unSelectAll();
        }
    },
    /**
     * 全選群組
     */
    selecteAll : function() {
        $(".groupTr:visible").each(function() {
            $(this).addClass("beSelected");
            ContactGroupStyle.setBgColor($(this));
        });
    },
    /**
     * 不選所有群組
     */
    unSelectAll : function() {
        $("#selectAllGroup").attr("checked", false);
        $(".groupTr:visible").each(function() {
            $(this).removeClass("beSelected");
            ContactGroupStyle.setBgColor($(this));
        });
    },
};

/**
 * 群組的列表操作
 */
var GroupTr = {
    /**
     * 設置滑鼠移到上面時的狀態
     */
    mouseover : function() {
        $(this).addClass("beOvered");
        ContactGroupStyle.setBgColor($(this));
        $(this).find('.groupContentBtn').show();
    },
    /**
     * 設置滑鼠移開時的狀態
     */
    mouseout : function() {
        $(this).removeClass("beOvered");
        ContactGroupStyle.setBgColor($(this));
        $(this).find('.groupContentBtn').hide();
    },
    /**
     * 設置滑鼠移按下時的動作
     */
    click : function(event) {
        // 如果點選的目標是tr裡的按鈕
        if ($(event.target).attr('class') != 'groupContentBtn') {
            if (!$(this).hasClass("beSelected")) {
                $(this).addClass("beSelected");
            } else {
                $(this).removeClass("beSelected");
            }
            ContactGroupStyle.setBgColor($(this));
        }
    },
};

/**
 * 群組列表中，觀看指定群組內容的按鈕
 */
var GroupContentBtn = {
    /**
     * 設置按鈕被按下時，要執行的動作
     */
    click : function() {
        SelectAllGroup.unSelectAll();
        $('#groupMainTable').hide();
        // 將選取的 group 資料帶到變數中
        var selectedTr = $(this).parents('.groupTr');
        GroupContentBtn.setSelectedGroupData(selectedTr);
        // 取得裡面的成員
        ContactInGroupMainTable.setContactListByGroupId();
    },
    /**
     * 設置選取的群組資料
     */
    setSelectedGroupData : function(selectedTr) {
        GroupContentBtn.setSelectedGroupId(selectedTr);
        GroupContentBtn.setSelectedGroupName(selectedTr);
        // 設置ContactInGroup要套用的style物件
        ContactInGroupParent.groupStyleObj = ContactGroupStyle;
        // 設置ContactInGroup要套用的tab物件
        ContactInGroupParent.groupTabObj = groupTab;
        // 設置ContactInGroup要用來取得清單的funcation
        ContactInGroupParent.getContactListFun = ContactInGroup.getContactListByGroupId;
    },
    /**
     * 將選取的group id帶到變數中
     */
    setSelectedGroupId : function(selectedTr) {
        var groupId = selectedTr.find('#groupId').val();
        ContactInGroupParent.selectedGroupId = groupId;
    },
    /**
     * 將選取的group name帶到變數中
     */
    setSelectedGroupName : function(selectedTr) {
        var groupName = selectedTr.find('#groupName').html();
        ContactInGroupParent.selectedGroupName = groupName;
    },
};
