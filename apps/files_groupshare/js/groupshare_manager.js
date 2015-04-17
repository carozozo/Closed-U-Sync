$(document).ready(function() {
    GroupShare_Manager.getGroupShareManagerList();
    GroupShare_Manager.setDefaultHandler();
});

var GroupShare_Manager = {
    setDefault : function() {
        $(".groupShareTr:even").css("background-color", "#F7F3F3");
        $(".groupShareTr:odd").css("background-color", "#FFFFFF");
    },
    getGroupShareManagerList : function(sortBy, sort) {
        $("#sharedListLoaginImg").show();
        $("#groupShareListTable").hide();
        $("#emptyMessage").hide();
        $.post(OC.filePath("files_groupshare", "ajax", "groupshare_manager.php"), {
            action : "getGroupShareManagerList",
            sortBy : sortBy,
            sort : sort,
        }, function(data) {
            $("#groupShareListTable .groupShareTr:gt(0)").remove();
            if (data.status == "success") {
                if (data.result.length <= 0) {
                    $("#emptyMessage").show();
                }
                $.each(data.result, function(key, val) {
                    var source = val.source;
                    var groupName = "";
                    var nickname = "";
                    var permission = val.permission;
                    $.each(val.gids, function(key, val) {
                        groupName += (groupName) ? ";" : t('files_groupshare', 'Group') + "：";
                        groupName += val.name;
                    });
                    var nicknameCount = 0;
                    $.each(val.uids, function(key, val) {
                        // 縮減被分享名單
                        nicknameCount++;
                        if (nicknameCount <= 10) {
                            nickname += (nickname) ? ";" : t('files_groupshare', 'Member') + "：";
                            nickname += (val.nickname) ? val.nickname : val.uid;
                        } else {
                            nickname += '...';
                            return false;
                        }
                    });

                    var cloneTr = $("#groupShareListTable .groupShareTr:first").clone();
                    // 縮減路徑
                    var sourcePath = source;
                    if (source.length >= 10) {
                        sourcePath = source.substr(0, 10) + '...';
                    }
                    cloneTr.find("#source").html(sourcePath);
                    cloneTr.find("#uid_shared_with").html(groupName + "<br/>" + nickname);
                    // cloneTr.find("#permissionCK").val(source);
                    cloneTr.find("#permission1").val(source);
                    cloneTr.find("#permission2").val(source);
                    /*
                     if (permission == "1") {
                     cloneTr.find("#permissionCK").prop("checked", true);
                     }*/
                    if (permission == "1" || permission == "3") {
                        cloneTr.find("#permission1").prop("checked", true);
                    }
                    if (permission == "2" || permission == "3") {
                        cloneTr.find("#permission2").prop("checked", true);
                    }
                    cloneTr.find("#updateGroupShareButton").attr("alt", source);
                    cloneTr.find("#removeGroupShareButton").attr("alt", source);
                    cloneTr.appendTo("#groupShareListTable");

                });
                $("#groupShareListTable .groupShareTr:first").hide();
                $("#groupShareListTable .groupShareTr:gt(0)").show();
                $("#sharedListLoaginImg").hide();
                $("#groupShareListTable").show();
                GroupShare_Manager.setDefault();
            } else {
                //alert("process error");
            }
        });
    },
    updatePermission : function(source, permission, successFun) {
        $.post(OC.filePath("files_groupshare", "ajax", "groupshare_manager.php"), {
            action : "updatePermission",
            source : source,
            permission : permission
        }, function(data) {
            if (data.status == "success") {
                if ( typeof (successFun) == 'function') {
                    successFun();
                }
            }
        });    },
    removeGroupShare : function(source) {
        $.post(OC.filePath("files_groupshare", "ajax", "groupshare_manager.php"), {
            action : "removeGroupShare",
            source : source
        }, function(data) {
            if (data.status == "success") {
                GroupShare_Manager.getGroupShareManagerList();
            }
        });
    },
    selectAll : function() {

    },
    unSelectAll : function() {

    },
    setDefaultHandler : function() {
        GroupShare_Manager.setPathSort();
        GroupShare_Manager.setRefreshBtn();
        GroupShare_Manager.setPermissionBtn();
        GroupShare_Manager.setUpdateBtn();
        GroupShare_Manager.setRemoveBtn();
    },
    setPathSort : function() {
        $('#pathSortTh').on('click', function() {
            var sortBy = 'path';
            var sort = $(this).attr('alt');
            GroupShare_Manager.getGroupShareManagerList(sortBy, sort);
            if (sort == 'desc') {
                $(this).attr('alt', 'asc');
                $('#pathSort').html('▼');
            } else {
                $(this).attr('alt', 'desc');
                $('#pathSort').html('▲');
            }
        });
    },
    setRefreshBtn : function() {
        $("#refreshGroupShareButton").click(function() {
            GroupShare_Manager.getGroupShareManagerList();
        });
    },
    setPermissionBtn : function() {
        $(document).on('click', '#permission1,#permission2', function() {
            var source = $(this).val();
            var tdObj = $(this).closest('td');
            GroupShare_Manager.sumPermission(source, tdObj);
        });
    },
    setUpdateBtn : function() {
        $(document).on('click', '#updateGroupShareButton', function() {
            var source = $(this).attr("alt");
            GroupShare.addGroupShare(source, true);
        });
    },
    setRemoveBtn : function() {
        $(document).on('click', '#removeGroupShareButton', function() {
            if (confirm(t('files_groupshare', 'Are you sure') + "?")) {
                var source = $(this).attr("alt");
                GroupShare_Manager.removeGroupShare(source);
            }
        });
    },
    sumPermission : function(source, tdObj) {
        var permission1 = tdObj.find('#permission1').prop('checked');
        var permission2 = tdObj.find('#permission2').prop('checked');
        var permission = "0";
        if (permission1 && permission2) {
            permission = "3";
        } else if (!permission1 && permission2) {
            permission = "2";
        } else if (permission1 && !permission2) {
            permission = "1";
        } else if (!permission1 && !permission2) {
            //
        }
        GroupShare_Manager.updatePermission(source, permission, GroupShare_Manager.getGroupShareManagerList);
    },
};
