$(document).ready(function() {
    GroupShare.getActionIcons();
    GroupShare.setDefault();
    GroupShare.checkEmptyFolder();
});

var GroupShare = {
    iconMapping : {},
    sharedFolder : '/GroupShared',
    defaultActionIcon : OC.imagePath('files_groupshare', 'groupshare.png'),
    sharedActionIcon : OC.imagePath('files_groupshare', 'groupshared.png'),
    ifUnderGroupShare : function() {
        if ( typeof ($("#dir")) != undefined && $("#dir").val() != undefined) {
            var dirArray = $("#dir").val().split("/");
            //目前所在資料夾是在GroupShared底下
            if (dirArray[1] == "GroupShared") {
                //目前所在的資料夾剛好是GroupShared
                if (dirArray.length == 2)
                    return 1;
                else
                    return 2;
            } else {
                //如果不是在GroupShared底下，而且不是在第一層folder
                if (dirArray.length >= 2)
                    return 3;
            }
        }
        return 0;
    },
    setDefault : function() {
        if ( typeof FileActions !== 'undefined') {
            FileActions.register('dir', t('files_groupshare', 'Group Share'), GroupShare.showActionIcon, GroupShare.addGroupShare, 1, true);
            FileActions.extend.getAfter['newActionsUnderGroupShare'] = GroupShare.newActionsUnderGroupShare;
            var ifUnderGroupShare = GroupShare.ifUnderGroupShare();
            GroupShare.setSelectedActions(ifUnderGroupShare);
            if (ifUnderGroupShare == "1") {
                GroupShare.showInfoLink();
            }
        }
    },
    getActionIcons : function() {
        $('#fileList tr[data-type="dir"]').each(function() {
            var fileName = decodeURIComponent($(this).attr('data-file'));
            GroupShare.iconMapping[fileName] = GroupShare.getActionIcon(fileName);
        });
    },
    getActionIcon : function(fileName) {
        var icon = GroupShare.defaultActionIcon;
        var source = $("#dir").val() + "/" + fileName;
        $.ajax({
            type : 'GET',
            url : OC.linkTo('files_groupshare', 'ajax/groupshare_handler.php'),
            dataType : 'json',
            data : {
                action : "getGroupShareCount",
                source : source
            },
            async : false,
            success : function(data) {
                if (data.status == "success") {
                    if (data.result > 0) {
                        icon = GroupShare.sharedActionIcon;
                    }
                }
            }
        });
        return icon;
    },
    showActionIcon : function(fileName) {
        if (GroupShare.iconMapping[fileName]) {
            return GroupShare.iconMapping[fileName];
        }
        return GroupShare.defaultActionIcon;
    },
    setSelectedActions : function(ifUnderGroupShare) {
        if (ifUnderGroupShare == "1" || ifUnderGroupShare == "2") {
            FileMultiActions.extend.beforeShow['multiAction1'] = function() {
                var setDownloadCopy = true;
                $.each($('#fileList tr'), function() {
                    var readable = $(this).attr('data-read');
                    var selected = $(this).hasClass('selected');
                    //只要被選取的檔案中，readable為false的話，則不秀download和copy選項
                    if (selected && (readable == 'false')) {
                        setDownloadCopy = false;
                    }
                });
                //清空所有的actions和actionObjs
                FileMultiActions.removeAllActions();
                //放入想顯示的action
                if (ifUnderGroupShare == "1") {
                    FileMultiActionSet.setDeleteAction();
                }
                if (setDownloadCopy) {
                    FileMultiActionSet.setDownlaodAction();
                    //如果copy有開啟的話
                    if (FilesCopy && typeof FilesCopy != 'undefined') {
                        var img = OC.imagePath('files_copy', 'files_copy.png');
                        FileMultiActions.addActionObj('copy', t('files_copy', 'Copy'), img, FilesCopy.multiAction, 30);
                    }
                }
                FileMultiActions.appendActions(false, false);
            };
        }
    },
    //在FileActions.extend.getAfter中加入新的function
    newActionsUnderGroupShare : function() {
        var ifUnderGroupShare = GroupShare.ifUnderGroupShare();
        if (ifUnderGroupShare == "1" || ifUnderGroupShare == "2") {
            var fileName = FileActions.getCurrentFile();
            var currentFile = $('#fileList tr').filter('[data-file="' + fileName + '"]');
            var readable = currentFile.attr('data-read');
            //清空actions
            FileActions.actions = {};
            //如果取得的權限是「允下下載/複製」
            if (readable == "true") {
                FileActionSet.setDownloadAction();
                if (typeof FilesCopy != 'undefined') {
                    FilesCopy.setDefault();
                }
                if (typeof MediaStreaming != 'undefined') {
                    MediaStreaming.setDefault();
                }
            }
            FileActionSet.setOpenAction();
            // FileActionSet.setDeleteAction();
        }
    },
    addGroupShare : function(folderName, isCompleteName) {
        var prefix = "";
        if (!isCompleteName) {
            prefix = $('#dir').val() + "/";
        }
        folderName = prefix + folderName;
        GroupShare.openWindow(folderName);
        //window.open(OC.filePath("files_groupshare", "", "groupshare_handler.php?source=" + source), "_blank");
        /*var keys = [];？
         var values = [];
         keys[0] = "source";
         values[0] = folderName;
         GroupShare.openWindowWithPost(OC.filePath("files_groupshare", "", "groupshare_handler.php"), "", keys, values);*/
    },
    openWindow : function(folderName) {
        folderName = encodeURIComponent(folderName);
        $.fancybox.open({
            type : 'iframe',
            href : OC.filePath("files_groupshare", "", "groupshare_handler.php?source=" + folderName),
            autoSize : true,
            openEffect : 'none',
            closeEffect : 'none'
        });
    },
    /*openWindowWithPost : function(url, name, keys, values) {
     var newWindow = window.open(url, name);
     if (!newWindow)
     return false;
     var html = "<html><head></head><body><form id='formid' method='post' action='" + url + "'>";
     if (keys && values && (keys.length == values.length))
     for (var i = 0; i < keys.length; i++) {
     //PFunctions is in core/js/public_functions.js
     values[i] = PFunctions.escapeHtml(values[i]);
     html += "<input type='hidden' name='" + keys[i] + "' value='" + encodeURIComponent(values[i]) + "'/>";
     }
     html += "</form><script type='text/javascript'>document.getElementById(\"formid\").submit()</script></body></html>";
     newWindow.document.write(html);
     return newWindow;
     },*/
    showInfoLink : function() {
        $.post(OC.filePath("files_groupshare", "ajax", "groupshare.php"), {
            action : "getGroupShareByUidSharedWith"
        }, function(data) {
            if (data.status == "success") {
                $.each(data.result, function(key, val) {
                    var target = val.target;
                    var groupNames = val.groupNames;
                    var nicknames = val.nicknames;
                    var permissions = val.permissions;
                    var reg = new RegExp("\/" + OC.currentUser + "\/files\/GroupShared\/", "g");
                    target = target.replace(reg, '');
                    switch(permissions) {
                        case "1":
                            permissions = t('files_groupshare', 'Allow Download/Copy');
                            break;
                        case "2":
                            permissions = t('files_groupshare', 'Allow Upload');
                            break;
                        case "3":
                            permissions = t('files_groupshare', 'Allow Download/Copy') + ',' + t('files_groupshare', 'Allow Upload');
                            break;
                        default:
                            permissions = t('files_groupshare', 'Can not change shared content');
                            break;
                    }
                    var targetTr = $('#fileList tr').filter('[data-file="' + target + '"]');
                    var targetObj = targetTr.find('.nametext');
                    var info = permissions + "<br/>" + t('files_groupshare', 'Group') + "：" + groupNames + "<br/>" + t('files_groupshare', 'Member') + "：" + nicknames;
                    targetObj.attr("original-title", info).tipsy({
                        html : true,
                        gravity : 'nw'
                    });

                    //alert("target=" + target + ", groupNames=" + groupNames + ", nicknames=" + nicknames)
                });
            } else {
                //alert("process error");
            }
        });
    },
    hideGroupSharedInFilesList : function() {
        // $('tr[data-file="GroupShared"]').hide();
    },
    checkEmptyFolder : function() {
        if (GroupShare.ifUnderFolder() && $('#emptyfolder')) {
            $('#emptyfolder').html(t('files_groupshare', 'The files that friends share to you will be placed here, and not account for your own quota. Tell them to share now!'));
        }
    },
    ifUnderFolder : function() {
        var dir = $('#dir').val();
        if (dir == GroupShare.sharedFolder) {
            return true;
        }
        return false;
    },
};
