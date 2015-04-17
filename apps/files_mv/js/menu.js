$(document).ready(function() {
    FilesMvMenu.setDefault();
    FilesMvMenu.setDefaultHandler();
});

var FilesMvMenu = {
    // 被點選的選單項目層級
    clickedLevel : 1,
    // 被點選的選單項目路徑
    clickedPath : '',
    setDefault : function() {
        FilesMvMenu.setMenu();
    },
    // 設置選單相關操作
    setDefaultHandler : function() {
        $('body').on('click', '#mvBtn', MvBtn.click);
    },
    // 設置目錄選單
    setMenu : function() {
        var files = $('#files').val();
        if (files.length > 100) {
            files = files.substr(0, 100) + '...';
        }
        $('#filesSpan').html(files);
        MvBtn.hide();

        var dirTree = FilesMvMenu.getDirTree('/');
        DirListTable.insertTreeToTable('', dirTree, 0);
    },
    // 取得目錄列表
    getDirTree : function(dir) {
        var returnDirTree;
        $.ajax(OC.filePath(FilesMv.appId, 'ajax', 'files_mv.php'), {
            type : 'POST',
            data : {
                action : 'getDirTree',
                dir : dir,
            },
            async : false,
            success : function(dirTree) {
                returnDirTree = dirTree;
            }
        });
        return returnDirTree;
    },
    // 移動到目的地
    mvToTarget : function() {
        var dir = $('#dir').val();
        var files = $('#files').val();
        var destDir = FilesMvMenu.clickedPath;
        $.ajax(OC.filePath(FilesMv.appId, 'ajax', 'files_mv.php'), {
            type : 'POST',
            async : false,
            data : {
                action : 'mvToTarget',
                dir : dir,
                files : files,
                destDir : destDir,
            },
            success : function(data) {
                if (data.status == "success") {
                    var successedFiles = data.successedFiles;
                    var failedFiles = data.failedFiles;
                    setTimeout(function() {
                        if (failedFiles.length > 0) {
                            MsgSpan.showFailedMess(failedFiles);
                        } else {
                            var successMess = t(FilesMv.appId, 'Move Successed');
                            MsgSpan.showMess(successMess);
                            // 將移動成功的檔案從file list中移除
                            $.each(successedFiles, function(index, fileName) {
                                parent.FileList.remove(fileName);
                            });
                        }
                    }, 2000);
                }
            }
        });
    },
    // 取得被選取的路徑
    getSelectedPathArr : function() {
        var dir = $('#dir').val();
        var files = $('#files').val();
        files = files.split(";");
        files = $.map(files, function(fileName) {
            return selectedPath = dir + '/' + fileName;
        });
        return files;
    },
    // 捲軸移動到特定 obj 的位置
    scrollToObj : function(obj) {
        var scrTop = $(window).scrollTop();
        var scrLeft = $(window).scrollLeft();
        $('html, body').animate({
            // scrollTop : obj.offset().top,
            scrollTop : 0,
        }, 10000 / scrTop);
        $('html, body').animate({
            scrollLeft : obj.offset().left,
        }, 10000 / scrLeft);
    },
};
var MenuDiv = {
    // 重新設定menudiv的寬度(選單table的位置才不會往下移)
    rewidth : function() {
        var totalWidth = 0;
        $('.dirListTable:visible').each(function() {
            var tableWidth = $(this).width();
            totalWidth += tableWidth;
        });
        $('#menuDiv').css('width', (totalWidth + 300) + 'px');
    },
};
var DirListTable = {
    // 記錄已經設置的table
    setedPath : Array(),
    // 將取得的列表設置到table中
    insertTreeToTable : function(parentPath, dirTree, level) {
        // 記錄該路徑已設置為選單
        DirListTable.setedPath.push(parentPath);
        $('.dirListTable:first').hide();
        var dir = $('#dir').val();
        var selectedPathArr = FilesMvMenu.getSelectedPathArr();
        var cloneTable = $('.dirListTable:first').clone();
        var tr = cloneTable.find('.dirPathTr:first');
        level++;
        // 該選單的路徑
        cloneTable.attr('parentPath', parentPath);
        // 該選單的層級
        cloneTable.attr('level', level);
        $('#menuDiv').append(cloneTable);
        $.each(dirTree, function(dirPath, property) {
            var isDir = property.isDir;
            // 路徑不是資料夾的話，則跳出
            if (!isDir) {
                return;
            }
            // 路徑
            var filePath = property.path;
            // 檔名
            var fileName = property.basename;
            // 遮罩路徑
            var markPath = property.markPath;
            // 遮罩檔名
            var markName = property.markName;
            // 子目錄(可能為null)
            var tree = property.tree;
            // 是否為空目錄
            var isEmptyFolder = property.isEmptyFolder;

            // 如果目錄路徑等於被選取的檔案來源路徑，則跳出
            if ($.inArray(filePath, selectedPathArr) >= 0) {
                return;
            }
            var cloneTr = tr.clone();
            cloneTr.data('filePath', filePath).data('fileName', fileName).data('markPath', markPath);
            cloneTr.find('.markNameSpan').html(markName);
            cloneTr.appendTo(cloneTable);

            // 設定項目動作(為了支援pad,無法使用document.on)
            DirPathTr.setHandler(cloneTr);
            if (!isEmptyFolder && tree != null) {
                // 取得子目錄的數量
                var treeLength = Object.keys(tree).length;
                // 如果 menu 路徑等於被選取的來源目錄，而且子選單的數量等於來源的數量，代表該 menu 底下已經沒有目的地可以選擇，所以不用設置箭頭
                if (dir == filePath && treeLength == selectedPathArr.length) {
                } else {
                    // 設置為可以繼續點選下層目錄
                    cloneTr.find('.arrowSpan').html('>');
                    cloneTr.data('canGetTree', true);
                }
            }
        });
        if (level == 1) {
            // 設置根目錄選單
            tr.data('filePath', '/').data('fileName', '/').data('markPath', '/');
            tr.find('.markNameSpan').html('/');
            // 設置根目錄的動作
            DirPathTr.setHandler(tr);
            cloneTable.show();
        } else {
            tr.hide();
        }
    },
    // 顯示點選的路徑的選單
    showMenu : function(parentPath) {
        DirListTable.hideOtherMenu();
        var subMenu = $('.dirListTable').filterAttr('parentPath', FilesMvMenu.clickedPath);
        // 如果有子選單
        if (subMenu.attr('level') != undefined && subMenu.attr('level') > 1) {
            DirListTable.setDefault(subMenu);
            subMenu.fadeIn();
            MenuDiv.rewidth();
            FilesMvMenu.scrollToObj(subMenu);
        }
    },
    // 隱藏非點選路徑的選單
    hideOtherMenu : function() {
        var clickedLevel = parseInt(FilesMvMenu.clickedLevel, 10);
        $('.dirListTable').each(function() {
            var level = $(this).attr('level');
            var parentPath = $(this).attr('parentPath');
            // 如果層級大於user點選的層級，而且不是所點選的路徑底下的menu
            if (level > clickedLevel && parentPath != FilesMvMenu.clickedPath) {
                DirListTable.setDefault($(this));
                $(this).hide();
            }
        });
    },
    setDefault : function(table) {
        table.find('.dirPathTr').each(function() {
            $(this).removeClass('beSelected');
            FilesMvMenuStyle.setBgColor($(this));
        });
    }
};
var MvBtn = {
    show : function() {
        $('#mvBtn').fadeIn();
    },
    hide : function() {
        $('#mvBtn').hide();
    },
    click : function(event) {
        var mess = t(FilesMv.appId, 'Moving') + '…' + t(FilesMv.appId, 'Finish time depend on file size');
        MvBtn.hide();
        MsgSpan.showMess(mess);
        FilesMvMenu.mvToTarget();
    },
};

var DirPathTr = {
    setHandler : function(obj) {
        obj.on('click', DirPathTr.click);
        obj.on('mouseover', DirPathTr.mouseover);
        obj.on('mouseout', DirPathTr.mouseout);
    },
    mouseover : function() {
        $(this).addClass("beOvered");
        FilesMvMenuStyle.setBgColor($(this));
    },
    mouseout : function() {
        $(this).removeClass("beOvered");
        FilesMvMenuStyle.setBgColor($(this));
        // $(this).find('.groupContentBtn').hide();
    },
    click : function(event) {
        var table = $(this).parents('table');
        var filePath = $(this).data('filePath');
        var markPath = $(this).data('markPath');
        var canGetTree = $(this).data('canGetTree');
        var level = table.attr('level');
        // 如果可以開啟下層目錄
        if (canGetTree) {
            // 如果還沒設置，則取得目錄結構
            if ($.inArray(filePath, DirListTable.setedPath) < 0) {
                $('#loadingImg').show();
                var dirTree = FilesMvMenu.getDirTree(filePath);
                DirListTable.insertTreeToTable(filePath, dirTree, level);
                $('#loadingImg').hide();
            }
        }
        DirListTable.setDefault(table);
        $(this).addClass('beSelected');
        FilesMvMenuStyle.setBgColor($(this));
        FilesMvMenu.clickedPath = filePath;
        FilesMvMenu.clickedLevel = level;
        $('#destSpan').html(markPath);
        MvBtn.show();
        MsgSpan.clean();
        DirListTable.showMenu();
    },
};

var MsgSpan = {
    // 清除訊息
    clean : function() {
        $('#msgSpan').html('');
    },
    // 顯示訊息
    showMess : function(mess) {
        $('#msgSpan').hide().html(mess).fadeIn();
    },
    // 顯示移動失敗的檔名
    showFailedMess : function(failedFiles) {
        var mess = t(FilesMv.appId, 'Move Failed');
        failedFiles = failedFiles.join(';');
        mess += ':' + failedFiles;
        MsgSpan.showMess(mess);
    },
};

var FilesMvMenuStyle = {
    // 樣式顏色
    defaultBgColor : '#FFFFFF',
    activeBgColor : '#B3CC63',
    selectedBgColor : '#B3CC63',
    titleColor : '#566635',
    // 設置背景色
    setBgColor : function(obj) {
        if (obj.hasClass("beSelected")) {
            obj.css("background-color", FilesMvMenuStyle.selectedBgColor);
        } else if (obj.hasClass("beOvered")) {
            obj.css("background-color", FilesMvMenuStyle.activeBgColor);
        } else {
            obj.css("background-color", FilesMvMenuStyle.defaultBgColor);
        }
    },
};
