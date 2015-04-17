$(document).ready(function() {
    RecycleManager.setDefault();
});

var RecycleManager = {
    appId : 'files_recycle',
    setDefault : function() {
        RecycleListTable.setDefault();
    },
    // 取得回收列表
    getRecList : function(sn, assignPath, sortBy, sort) {
        var items;
        $.ajax(OC.filePath(RecycleManager.appId, 'ajax', 'manager.php'), {
            data : {
                action : 'getRecList',
                sn : sn,
                assignPath : assignPath,
                sortBy : sortBy,
                sort : sort,
            },
            type : 'POST',
            async : false,
            success : function(data) {
                if (data.status == 'success') {
                    items = data.items;
                }
            },
        });
        return items;
    },
    // 回復資料
    revRec : function(sn, assignPath) {
        var items;
        $.ajax(OC.filePath(RecycleManager.appId, 'ajax', 'manager.php'), {
            data : {
                action : 'revRec',
                sn : sn,
                assignPath : assignPath,
            },
            type : 'POST',
            async : false,
            success : function(data) {
                if (data.status == 'success') {
                    items = data.items;
                }
            },
        });
        return items;
    },
    // 刪除回收資料， sn 和 assignPath 無值時代表清空
    delRec : function(sn, assignPath) {
        var items;
        $.ajax(OC.filePath(RecycleManager.appId, 'ajax', 'manager.php'), {
            data : {
                action : 'delRec',
                sn : sn,
                assignPath : assignPath,
            },
            type : 'POST',
            async : false,
            success : function(data) {
                if (data.status == 'success') {
                    items = data.items;
                }
            },
        });
        return items;
    },
};

var RecycleListTable = {
    // 判斷是要設置根目錄內容，還是指定資料夾底下的內容
    setRoot : true,
    setDefault : function() {
        RecycleListTable.setDefaultHandler();
        RecycleListTable.setContent();
    },
    setStyle : function() {
        $('.recycleTr:even').css('background-color', '#FFFFFF');
        $('.recycleTr:odd').css('background-color', '#F7F3F3');
    },
    setDefaultHandler : function() {
        PathSortTh.on();
        TimeSortTh.on();
        $('#selectAll').on('click', SelectAll.click);
        $('#cleanUpBtn').on('click', CleanUpBtn.click);
        $('#revSeltBtn').on('click', RevSeltBtn.click);
        $('#delSeltBtn').on('click', DelSeltBtn.click);
        $('#topBtn').on('click', TopBtn.click);
        $('#bakBtn').on('click', BakBtn.click);
    },
    // 取得回收內容，並放到 table 中
    setContent : function() {
        $('#loaginImg').show();
        $('#emptyMessage').hide();
        var sn = RecycleListTable.sn;
        var assignPath = RecycleListTable.assignPath;
        var sortBy = RecycleListTable.sortBy;
        var sort = RecycleListTable.sort;
        var items = RecycleManager.getRecList(sn, assignPath, sortBy, sort);
        if (items) {
            if (sn) {
                // 有指定 sn ，代表是要抓取指定的回收資料底下的結構
                RecycleListTable.setRoot = false;
            } else {
                RecycleListTable.setRoot = true;
            }
            var parentItem = items['parent'];
            PathTr.setContent(parentItem);
            RecycleListTable.setList(items);
        } else {
            RecycleListTable.setEmpty();
        }
        $('#loaginImg').hide();
    },
    // 設置回收內容列表
    setList : function(items) {
        var tr = $('.recycleTr:first');
        tr.hide();
        $('.recycleTr:gt(0)').remove();
        if (items) {
            $.each(items, function(index, property) {
                // 取得子目錄的檔案資料
                if (index != 'parent') {
                    var cloneTr = tr.clone();
                    cloneTr = RecycleTr.setDefault(cloneTr, property);
                    cloneTr.appendTo('#recycleListTable');
                }
            });
            $('.recycleTr:gt(0)').fadeIn();
            SelectAll.unSelect();
            RecycleListTable.setStyle();
            return;
        }
        RecycleListTable.setEmpty();
    },
    // 沒內容時顯示的樣式
    setEmpty : function() {
        $('.recycleTr:visible').remove();
        $('#cleanUpBtn').hide();
        SelectAll.unSelect();
        $('#emptyMessage').show();
    },
};

var PathTr = {
    setContent : function(item) {
        if (RecycleListTable.setRoot) {
            // 設置為根目錄狀態
            $('#locationSpan').html('');
            $('#topBtn').hide();
            $('#bakBtn').hide();
            $('#cleanUpBtn').fadeIn();
        } else {
            // 設置為檔案列表狀態
            $('#locationSpan').html(item.path);
            $('#topBtn').fadeIn();
            $('#bakBtn').fadeIn();
            $('#cleanUpBtn').hide();
            // 設置「回上層」按鈕的資料
            BakBtn.setData(item);
        }
    },
};
var SelectAll = {
    click : function() {
        if ($(this).prop('checked')) {
            var count = $('.recycleTr:visible').length;
            if (count > 0) {
                SelectAll.select();
            }
        } else {
            SelectAll.unSelect();
        }
    },
    select : function() {
        $('.selectedBtn').fadeIn();
        $('.selectRecycle').prop('checked', true);
    },
    unSelect : function() {
        $('.selectedBtn').fadeOut();
        $('.selectRecycle').prop('checked', false);
    },
};

var PathSortTh = {
    click : function() {
        var sortBy = 'path';
        var sort = $(this).attr('alt');
        var sn = RecycleListTable.sn;
        var assignPath = RecycleListTable.assignPath;
        RecycleListTable.sortBy = sortBy;
        RecycleListTable.sort = sort;
        RecycleListTable.setContent();
        if (sort == 'desc') {
            $(this).attr('alt', 'asc');
            $('#pathSort').html('▼');
        } else {
            $(this).attr('alt', 'desc');
            $('#pathSort').html('▲');
        }
        $('#timeSort').html('');
    },
    // 開啟可點選狀態
    on : function() {
        $('#pathSortTh').css('cursor', 'pointer');
        $('#pathSortTh').off('click').on('click', PathSortTh.click);
    },
    // 關閉可點選狀態
    off : function() {
        $('#pathSortTh').css('cursor', 'default');
        $('#pathSortTh').off('click');
        $('#pathSort').html('');
    },
};

var TimeSortTh = {
    click : function() {
        var sortBy = 'time';
        var sort = $(this).attr('alt');
        RecycleListTable.sortBy = sortBy;
        RecycleListTable.sort = sort;
        RecycleListTable.setContent();
        if (sort == 'desc') {
            $(this).attr('alt', 'asc');
            $('#timeSort').html('▼');
        } else {
            $(this).attr('alt', 'desc');
            $('#timeSort').html('▲');
        }
        $('#pathSort').html('');
    },
    // 開啟可點選狀態
    on : function() {
        $('#timeSortTh').css('cursor', 'pointer');
        $('#timeSortTh').off('click').on('click', TimeSortTh.click);
    },
    // 關閉可點選狀態
    off : function() {
        $('#timeSortTh').css('cursor', 'default');
        $('#timeSortTh').off('click');
        $('#timeSort').html('');
    },
};

var CleanUpBtn = {
    click : function() {
        var msg = t(RecycleManager.appId, 'Are you sure to clean up') + '?';
        if (confirm(msg)) {
            var result = RecycleManager.delRec();
            if (result) {
                RecycleListTable.setEmpty();
            }
        }
    },
};

var RevSeltBtn = {
    click : function() {
        var sn = SelectRecycle.getSelSn();
        var assignPath = SelectRecycle.getSelAssignPath();
        MsgDiv.show();
        var items = RecycleManager.revRec(sn, assignPath);
        if (items) {
            MsgDiv.done();
        }
    },
};

var DelSeltBtn = {
    click : function() {
        var msg = t(RecycleManager.appId, 'Are you sure to delete selected files') + '?';
        if (confirm(msg)) {
            var sn = SelectRecycle.getSelSn();
            var assignPath = SelectRecycle.getSelAssignPath();
            var items = RecycleManager.delRec(sn, assignPath);
            // 刪除成功，有回傳被刪除的回收資料
            if (items) {
                RecycleListTable.setContent();
            }
        }
    },
};

var BakBtn = {
    click : function() {
        var sn = $(this).data('sn');
        var assignPath = $(this).data('assignPath');
        if (assignPath) {
            RecycleListTable.sn = sn;
            RecycleListTable.assignPath = assignPath;
            RecycleListTable.setContent();
            return;
        }
        TopBtn.click();
    },
    setData : function(property) {
        var sn = property.sn;
        var assignPath = property.assignPath;
        // 取得路徑的上一層
        var assignPath = PFunctions.dirname(assignPath);
        $('#bakBtn').data('sn', sn).data('assignPath', assignPath);
    },
};

var TopBtn = {
    click : function() {
        // 清空 sn 和 assignPath 後，再取得列表
        RecycleListTable.sn = null;
        RecycleListTable.assignPath = null;
        RecycleListTable.setContent();
    }
};

var RecycleTr = {
    setDefault : function(tr, property) {
        // 取得檔案屬性
        var fileType = property.type;
        var isEmptyFolder = property.isEmptyFolder;
        var dirPath = property.dirname;
        var sizeHuman = property.sizeHuman;
        // 取得回收桶相關資訊
        var sn = property.sn;
        var assignPath = property.assignPath;
        var recycleTimeLocal = property.recycleTimeLocal;

        if (RecycleListTable.setRoot) {
            // 是要設置根目錄內容，所以要把路徑放入
            var filePath = property.path;
            tr.find('.filePathTd').html(filePath);
        } else {
            // 是要設置指定路徑底下的內容，所以只放檔名
            var fileName = property.basename;
            tr.find('.filePathTd').html(fileName);
        }
        tr.find('.recycleTimeTd').html(recycleTimeLocal);
        tr.find('.fileSizeTd').html(sizeHuman);

        tr.find('.selectRecycle').data('sn', sn).data('assignPath', assignPath);
        tr.find('.revertBtn').data('sn', sn).data('assignPath', assignPath);
        tr.find('.deleteBtn').data('sn', sn).data('assignPath', assignPath);
        if (fileType == 'dir' && !isEmptyFolder) {
            var openBtn = tr.find('.openBtn');
            OpenBtn.btn = openBtn;
            OpenBtn.fileType = fileType;
            openBtn.data('sn', sn).data('assignPath', assignPath).show();
        }
        RecycleTr.setHandler(tr);
        return tr;
    },
    setHandler : function(tr) {
        tr.find('.selectRecycle').on('click', SelectRecycle.click);
        tr.find('.openBtn').on('click', OpenBtn.click);
        tr.find('.revertBtn').on('click', RevertBtn.click);
        tr.find('.deleteBtn').on('click', DeleteBtn.click);
    },
};

var SelectRecycle = {
    click : function() {
        var count = $('.recycleTr:visible .selectRecycle').length;
        var selectCount = $('.recycleTr:visible .selectRecycle:checked').length;
        if (selectCount > 0)
            $('.selectedBtn').fadeIn();
        else
            $('.selectedBtn').fadeOut();
        if (selectCount == count)
            $('#selectAll').prop('checked', true);
        else
            $('#selectAll').prop('checked', false);
    },
    // 取得被選取的回收 sn(以|分隔的字串)
    getSelSn : function() {
        var seltBox = $('.recycleTr:visible .selectRecycle:checked');
        var snStrArr = '';
        seltBox.each(function() {
            var sn = $(this).data('sn');
            snStrArr += sn + '|';
        });
        return snStrArr;
    },
    // 取得被選取的回收指定路徑(以|分隔的字串)
    getSelAssignPath : function() {
        var seltBox = $('.recycleTr:visible .selectRecycle:checked');
        var assignPathStrArr = '';
        seltBox.each(function() {
            var assignPath = $(this).data('assignPath');
            assignPathStrArr += assignPath + '|';
        });
        return assignPathStrArr;
    },
};

var OpenBtn = {
    click : function() {
        var sn = $(this).data('sn');
        var assignPath = $(this).data('assignPath');
        // 指定要找的資料
        RecycleListTable.sn = sn;
        RecycleListTable.assignPath = assignPath;
        RecycleListTable.setContent(sn, assignPath);
    },
};

var RevertBtn = {
    click : function() {
        var sn = $(this).data('sn');
        var assignPath = $(this).data('assignPath');
        MsgDiv.show();
        var result = RecycleManager.revRec(sn, assignPath);
        if (result) {
            MsgDiv.done();
        }
    },
};

var DeleteBtn = {
    click : function() {
        var sn = $(this).data('sn');
        var assignPath = $(this).data('assignPath');
        // MsgDiv.show('Deleting');
        var items = RecycleManager.delRec(sn, assignPath);
        // 刪除成功，有回傳被刪除的回收資料
        if (items) {
            RecycleListTable.setContent();
        }
    },
};

var MsgDiv = {
    // 顯示訊息遮罩
    show : function(message) {
        $.fancybox.open('<div id="msgDiv"></div>', {
            autoSize : false,
            width : 250,
            minHeight : 30,
            height : 30,
            closeBtn : false,
            helpers : {
                overlay : {
                    // 按下半透明底時不會關閉視窗
                    closeClick : false,
                },
            },
            keys : {
                // 按下Esc的時候不會關閉視窗
                close : false,
            },
        });
        message = message || 'Reverting';
        $('#msgDiv').css('text-align', 'center').css('font-weight', 'bolder').css('font-size', '1.5em').text(t(RecycleManager.appId, message));
    },
    // 顯示訊息後消失
    done : function(message) {
        if (message) {
            setTimeout(function() {
                $('#msgDiv').text(message);
            }, 1000);
        }
        setTimeout(MsgDiv.close, 2000);
    },
    // 關閉訊息遮罩
    close : function() {
        $.fancybox.close();
    },
};
