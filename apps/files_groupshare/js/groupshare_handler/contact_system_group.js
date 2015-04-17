$(document).ready(function() {
    ContactSystemGroup.setDefault();
    ContactSystemGroup.setDefaultHandler();
    $('#systemGroupMainTable').hide();
});
/**
 * GroupShare中，系統群組相關操作
 */
var ContactSystemGroup = {
    /**
     * 設置初始狀態
     */
    setDefault : function() {
        SystemGroupTab.setStyle();
        SystemGroupMainTable.setStyle();
        SelectAllSystemGroup.unSelectAll();
        SystemGroupMainTable.setSystemGroupList();
    },
    /**
     * 指定操作
     */
    setDefaultHandler : function() {
        $("#systemGroupTab").on('click', SystemGroupTab.click);
        $("#selectAllSystemGroup").on('click', SelectAllSystemGroup.click);
    },
    /**
     * 取得系統群組列表
     * @return array
     */
    getSystemGroupList : function() {
        var systemGroupList;
        $.ajax({
            type : 'POST',
            url : OC.filePath("contact", "ajax", "contact_system_group.php"),
            data : {
                action : "getSystemGroupList",
            },
            async : false,
            success : function(data) {
                if (data.status == "success") {
                    systemGroupList = data.result;
                }
            }
        });
        return systemGroupList;
    },
    /**
     * 取得所有的被選取的system group id
     * @return array
     */
    getSelectedGidsArray : function() {
        var gids = Array();
        $("#systemGroupListTable .beSelected").each(function() {
            var val = $(this).find("#systemGroupId").val();
            gids.push(val);
        });
        return gids;
    },
};

/**
 * 系統群組樣式
 */
var ContactSystemGroupStyle = {
    /**
     * 樣式顏色
     */
    defaultBgColor : '#9BC11F',
    activeBgColor : '#FFFFFF',
    selectedBgColor : '#92A847',
    titleColor : '#4B5427',
    /**
     * 設置背景色
     */
    setBgColor : function(obj) {
        if (obj.hasClass("beSelected")) {
            obj.css("background-color", ContactSystemGroupStyle.selectedBgColor);
        } else if (obj.hasClass("beOvered")) {
            obj.css("background-color", ContactSystemGroupStyle.activeBgColor);
        } else {
            obj.css("background-color", ContactSystemGroupStyle.defaultBgColor);
        }
    },
};

/**
 * GroupShare中，系統群組上方的Tab標籤
 */
var SystemGroupTab = {
    /**
     * 設置滑鼠移按下時的動作
     */
    click : function() {
        if ($("#systemGroupMainTable").is(':hidden')) {
            ContactSystemGroup.setDefault();
            $("#systemGroupMainTable").show();
            $("#groupMainTable").hide();
            $("#contactMainTable").hide();
            $("#contactInGroupMainTable").hide();
        }
    },
    /**
     * 設置Tab樣式
     */
    setStyle : function() {
        $('#systemGroupTab').css('background-color', ContactSystemGroupStyle.defaultBgColor);
    },
};

/**
 * 系統群組主要視窗
 */
var SystemGroupMainTable = {
    /**
     * 將取得的系統群組列表，放到tabe中
     */
    setSystemGroupList : function() {
        SystemGroupMainTable.showLoaging();
        $("#systemGroupListTable tr:gt(0)").remove();
        var systemGroupList = ContactSystemGroup.getSystemGroupList();
        $.each(systemGroupList, function(key, val) {
            var name = val['systemGroupName'];
            var id = val['systemGroupId'];
            var cloneTr = $("#systemGroupListTable tr:first").clone();
            var btn = cloneTr.find('.systemGroupContentBtn');
            // 在system group id前面加上字串's'
            cloneTr.find("#systemGroupId").val('s' + id);
            cloneTr.find("#systemGroupName").html(name);
            cloneTr.find('.systemGroupContentBtn').hide();
            cloneTr.appendTo("#systemGroupListTable");
            cloneTr.on('mouseover', SystemGroupTr.mouseover);
            cloneTr.on('mouseout', SystemGroupTr.mouseout);
            cloneTr.on('click', SystemGroupTr.click);
            btn.on('click', SystemGroupContentBtn.click);
        });
        SystemGroupMainTable.showList();
    },
    /**
     * 設置視窗樣式
     */
    setStyle : function() {
        $("#systemGroupMainTable").css('background-color', ContactSystemGroupStyle.defaultBgColor);
        $("#systemGroupMainTable .titleTr").css('color', ContactSystemGroupStyle.titleColor);
    },
    /**
     * 顯示loading畫面
     */
    showLoaging : function() {
        $("#systemGroupListLoaginImg").show();
        $("#systemGroupListTable").hide();
    },
    /**
     * 顯示列表
     */
    showList : function() {
        $("#systemGroupListTable tr:first").hide();
        $("#systemGroupListTable tr:gt(0)").show();
        $("#systemGroupListTable").show();
        $("#systemGroupListLoaginImg").hide();
    },
};

/**
 * 系統群組[全選checkbox]
 */
var SelectAllSystemGroup = {
    /**
     * 設置點選時的動作
     */
    click : function() {
        if ($(this).prop("checked")) {
            SelectAllSystemGroup.selecteAll();
        } else {
            SelectAllSystemGroup.unSelectAll();
        }
    },
    /**
     * 全選系統群組
     */
    selecteAll : function() {
        $(".systemGroupTr:visible").each(function() {
            $(this).addClass("beSelected");
            ContactSystemGroupStyle.setBgColor($(this));
        });
    },
    /**
     * 不選所有系統群組
     */
    unSelectAll : function() {
        $("#selectAllSystemGroup").attr("checked", false);
        $(".systemGroupTr:visible").each(function() {
            $(this).removeClass("beSelected");
            ContactSystemGroupStyle.setBgColor($(this));
        });
    },
};

/**
 * 系統群組的列表操作
 */
var SystemGroupTr = {
    /**
     * 設置滑鼠移到上面時的狀態
     */
    mouseover : function() {
        $(this).addClass("beOvered");
        ContactSystemGroupStyle.setBgColor($(this));
        $(this).find('.systemGroupContentBtn').show();
    },
    /**
     * 設置滑鼠移開時的狀態
     */
    mouseout : function() {
        $(this).removeClass("beOvered");
        ContactSystemGroupStyle.setBgColor($(this));
        $(this).find('.systemGroupContentBtn').hide();
    },
    /**
     * 設置滑鼠移按下時的動作
     */
    click : function(event) {
        // 如果點選的目標是tr裡的按鈕
        if ($(event.target).attr('class') != 'systemGroupContentBtn') {
            if (!$(this).hasClass("beSelected")) {
                $(this).addClass("beSelected");
            } else {
                $(this).removeClass("beSelected");
            }
            ContactSystemGroupStyle.setBgColor($(this));
        }
    },
};

/**
 * 系統群組列表中，觀看指定群組內容的按鈕
 */
var SystemGroupContentBtn = {
    /**
     * 設置按鈕被按下時，要執行的動作
     */
    click : function() {
        SelectAllSystemGroup.unSelectAll();
        $('#systemGroupMainTable').hide();
        // 將選取的system group 資料帶到變數中
        var selectedTr = $(this).parents('.systemGroupTr');
        SystemGroupContentBtn.setSelectedSystemGroupData(selectedTr);
        // 取得裡面的成員
        ContactInGroupMainTable.setContactListByGroupId();
    },
    /**
     * 設置選取的系統群組資料
     */
    setSelectedSystemGroupData : function(selectedTr) {
        SystemGroupContentBtn.setSelectedSystemGroupId(selectedTr);
        SystemGroupContentBtn.setSelectedSystemGroupName(selectedTr);
        // 設置ContactInGroup要套用的style物件
        ContactInGroupParent.groupStyleObj = ContactSystemGroupStyle;
        // 設置ContactInGroup要套用的tab物件
        ContactInGroupParent.groupTabObj = SystemGroupTab;
        // 設置ContactInGroup要用來取得清單的funcation
        ContactInGroupParent.getContactListFun = ContactInGroup.getContactListBySystemGroupId;
    },
    /**
     * 將選取的system group id 帶到ContactInGroupParent的變數中
     */
    setSelectedSystemGroupId : function(selectedTr) {
        var systemGroupId = selectedTr.find('#systemGroupId').val();
        // 將system group id中開頭的「s」拿掉
        systemGroupId = systemGroupId.replace(/s/g, "");
        ContactInGroupParent.selectedGroupId = systemGroupId;
    },
    /**
     * 將選取的system group name 帶到ContactInGroupParent的變數中
     */
    setSelectedSystemGroupName : function(selectedTr) {
        var systemGroupName = selectedTr.find('#systemGroupName').html();
        ContactInGroupParent.selectedGroupName = systemGroupName;
    },
};
