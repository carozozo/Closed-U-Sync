$(document).ready(function() {
    Shared.setDefault();
    Shared.setDefaultHandler();
});
/**
 * GroupShare中，被分享清單相關操作
 */
var Shared = {
    /**
     * 設置初始狀態
     */
    setDefault : function() {
        SharedTab.setStyle();
        SharedMainTable.setStyle();
        SelectAllShared.unSelectAll();
        SharedMainTable.setGroupShareList();
    },
    /**
     * 指定操作
     */
    setDefaultHandler : function() {
        $("#sharedTab").on('click', SharedTab.click);
        $("#selectAllShared").on('click', SelectAllShared.click);
    },
    /**
     * 取得被分享清單
     * @return array
     */
    getGroupShareList : function() {
        var groupShareList;
        var source = $("#source").val();
        $.ajax({
            type : 'POST',
            url : OC.filePath("files_groupshare", "ajax", "groupshare_handler.php"),
            data : {
                action : "getGroupShareList",
                source : source,
            },
            async : false,
            success : function(data) {
                if (data.status == "success") {
                    groupShareList = data.result;
                }
            }
        });
        return groupShareList;
    },
    // 移除空的群組
    filterEmptyGroup : function(gids) {
        var newGids = new Array();
        var emptyGroupNameArray = new Array();
        $.each(gids, function(key, val) {
            // 如果gid的前面有帶s，代表為system group id
            if (val.indexOf('s') == 0) {
                systemGroupId = val.replace(/s/, '');
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
                            var contactArr = data.result;
                            if (contactArr && contactArr.length > 0)
                                newGids.push(val);
                            else {
                                emptyGroupNameArray.push($('#systemGroupId[value="' + val + '"]').parent().find('#systemGroupName').html());
                            }
                        }
                    }
                });
            } else {
                $.ajax({
                    type : 'POST',
                    url : OC.filePath("contact", "ajax", "contact_group.php"),
                    data : {
                        action : "getContactListByGroupId",
                        groupId : val,
                    },
                    async : false,
                    success : function(data) {
                        if (data.status == "success") {
                            var contactArr = data.result;
                            if (contactArr && contactArr.length > 0)
                                newGids.push(val);
                            else {
                                emptyGroupNameArray.push($('#groupId[value="' + val + '"]').parent().find('#groupName').html());
                            }
                        }
                    }
                });
            }
        });
        if (emptyGroupNameArray.length > 0) {
            emptyGroupStr = emptyGroupNameArray.join(',');
            alert('[' + emptyGroupStr + ']' + t('files_groupshare', 'no contact in Group'));
        }
        return newGids;
    },
    getSharedGidsArray : function() {
        var gids = Array();
        $(".sharedGroupTd").each(function() {
            var val = $(this).find("#sharedGroupId").val();
            if (val && !$(this).hasClass("titleClass"))
                gids.push(val);
        });
        return gids;
    },
    getSharedUidsArray : function() {
        var uids = Array();
        $(".sharedContactTd").each(function() {
            var val = $(this).find("#sharedContactId").val();
            if (val && !$(this).hasClass("titleClass")) {
                uids.push($.base64.decode(val));
            }
        });
        return uids;
    },
};

/**
 * 分享清單樣式
 */
var SharedStyle = {
    /**
     * 樣式顏色
     */
    defaultBgColor : '#B6CADE',
    activeBgColor : '#FFFFFF',
    selectedBgColor : '#698099',
    titleColor : '#193756',
    /**
     * 設置背景色
     */
    setBgColor : function(obj) {
        if (obj.hasClass("beSelected")) {
            obj.css("background-color", SharedStyle.selectedBgColor);
        } else if (obj.hasClass("beOvered")) {
            obj.css("background-color", SharedStyle.activeBgColor);
        } else {
            obj.css("background-color", SharedStyle.defaultBgColor);
        }
    },
};

/**
 * 聯絡人Tab標籤
 */
var SharedTab = {
    /**
     * 設置滑鼠移按下時的動作
     */
    click : function() {
    },
    /**
     * 設置Tab樣式
     */
    setStyle : function() {
        $('#sharedTab').css('background-color', SharedStyle.defaultBgColor);
    },
};
/**
 * 被分享清單主要視窗
 */
var SharedMainTable = {
    /**
     * 將取得的被分享列表，放到tabe中
     */
    setGroupShareList : function() {
        SharedMainTable.showLoaging();
        SelectAllShared.unSelectAll();
        $(".sharedGroupTr:gt(0)").remove();
        $(".sharedContactTr:gt(0)").remove();
        var groupShareList = Shared.getGroupShareList();
        SharedMainTable.setSharedGroupList(groupShareList);
        SharedMainTable.setSharedContactList(groupShareList);
        // 新建立的group share，取得的permission會是false
        if (!groupShareList.permission || groupShareList.permission == "1" || groupShareList.permission == "3") {
            $("#permissionChBox1").attr("checked", true);
        }
        if (groupShareList.permission == "2" || groupShareList.permission == "3") {
            $("#permissionChBox2").attr("checked", true);
        }
        SharedMainTable.showList();
    },
    /**
     * 設置被分享的群組
     */
    setSharedGroupList : function(groupShareList) {
        $.each(groupShareList.groupNameArray, function(index, val) {
            var gid = val.gid;
            var name = val.name;
            var cloneTr = $(".sharedGroupTr:first").clone();
            cloneTr.find("#sharedGroupId").val(gid);
            cloneTr.find("#sharedGroupName").html(name);
            cloneTr.appendTo("#sharedGroupListTable");
            cloneTr.on('mouseover', SharedTr.mouseover);
            cloneTr.on('mouseout', SharedTr.mouseout);
            cloneTr.on('click', SharedTr.click);
        });
    },
    /**
     * 設置被分享的聯絡人
     */
    setSharedContactList : function(groupShareList) {
        $.each(groupShareList.userArray, function(index, val) {
            var contact = val.contact;
            var nickname = val.nickname;
            var cloneTr = $(".sharedContactTr:first").clone();
            // 將id做base64加密
            cloneTr.find("#sharedContactId").val($.base64.encode(contact));
            cloneTr.find("#sharedNickname").html(nickname);
            cloneTr.appendTo("#sharedContactListTable");
            cloneTr.on('mouseover', SharedTr.mouseover);
            cloneTr.on('mouseout', SharedTr.mouseout);
            cloneTr.on('click', SharedTr.click);
        });
    },
    /**
     * 設置視窗樣式
     */
    setStyle : function() {
        $("#sharedMainTable").css('background-color', SharedStyle.defaultBgColor);
        $("#sharedMainTable .titleTr").css('color', SharedStyle.titleColor);
    },
    /**
     * 顯示loading畫面
     */
    showLoaging : function() {
        $("#sharedListLoaginImg").show();
        $("#sharedListTable").hide();
    },
    /**
     * 顯示列表
     */
    showList : function() {
        $(".sharedGroupTr:first").hide();
        $(".sharedGroupTr:gt(0)").show();
        $(".sharedContactTr:first").hide();
        $(".sharedContactTr:gt(0)").show();
        $("#sharedListTable").show();
        $("#sharedListLoaginImg").hide();
    },
};

/**
 * 被分享清單[全選checkbox]
 */
var SelectAllShared = {
    /**
     * 設置點選時的動作
     */
    click : function() {
        if ($(this).prop("checked")) {
            SelectAllShared.selectAll();
        } else {
            SelectAllShared.unSelectAll();
        }
    },
    /**
     * 全選被分享清單
     */
    selectAll : function() {
        $(".sharedGroupTr,.sharedContactTr").each(function() {
            $(this).addClass("beSelected");
            SharedStyle.setBgColor($(this));
        });
    },
    /**
     * 不選所有被分享清單
     */
    unSelectAll : function() {
        $("#selectAllShared").attr("checked", false);
        $(".sharedGroupTr,.sharedContactTr").each(function(index) {
            $(this).removeClass("beSelected");
            SharedStyle.setBgColor($(this));
        });
    },
};

/**
 * 被分享清單的列表操作
 */
var SharedTr = {
    /**
     * 設置滑鼠移到上面時的狀態
     */
    mouseover : function() {
        $(this).addClass("beOvered");
        SharedStyle.setBgColor($(this));
    },
    /**
     * 設置滑鼠移開時的狀態
     */
    mouseout : function() {
        $(this).removeClass("beOvered");
        SharedStyle.setBgColor($(this));
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
        SharedStyle.setBgColor($(this));
    },
};
