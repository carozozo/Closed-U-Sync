$(document).ready(function() {
    PublicShare_Manager.setDefault();
    PublicShare_Manager.setDefaultHandler();
});

var PublicShare_Manager = {
    setDefault : function() {
        PublicShareListTable.setList();
    },
    setDefaultHandler : function() {
        $('#pathSortTh').on('click', PathSortTh.click);
        $('#timeSortTh').on('click', TimeSortTh.click);
    },
    // 取得分享連結列表
    getListByUser : function() {
        // var sortBy = PublicShare_Manager.PublicShareList.sortBy;
        var sortBy = $('body').data('sortBy');
        var sort = $('body').data('sort');
        var items;
        $.ajax({
            type : 'POST',
            url : OC.linkTo(PublicShare.appId, 'ajax/publicshare.php'),
            data : {
                action : "getListByUser",
                sortBy : sortBy,
                sort : sort,
            },
            async : false,
            success : function(data) {
                if (data.status == 'success') {
                    items = data.items;
                }
            }
        });
        return items;
    },
    // 產生連結分享介面
    createUi : function(sourcePath) {
        sourcePath = encodeURIComponent(sourcePath);
        $.fancybox.open({
            type : 'iframe',
            href : OC.filePath(PublicShare.appId, '', 'ui.php?sourcePath=' + sourcePath),
            autoSize : true,
            openEffect : 'none',
            closeEffect : 'none',
        });
    },
    // 刪除分享資料
    deleteByToken : function(token) {
        $.ajax({
            type : 'POST',
            url : OC.linkTo(PublicShare.appId, 'ajax/publicshare.php'),
            data : {
                action : 'deleteByToken',
                token : token,
            },
            success : function(data) {
                if (data.status == 'success') {
                    PublicShareTr.remove();
                }
            }
        });
    },
};

var PublicShareListTable = {
    // 記錄目前正在更新的 tr 物件(由 UpdateBtn.click/CancelBtn.click 發動)
    selectedTr : '',
    // 設置分享連結列表
    setList : function() {
        $("#sharedListLoaginImg").show();
        $("#publicShareListTable").hide();
        $("#emptyMessage").hide();
        $("#publicShareListTable .publicShareTr:gt(0)").remove();
        var items = PublicShare_Manager.getListByUser();
        if (!items) {
            $("#emptyMessage").show();
        } else {
            $.each(items, function(index, property) {
                var sourcePath = property.sourcePath;
                var shortUrl = property.shortUrl;
                var deadlineLocal = property.deadlineLocal;
                var pwd = property.pwd;
                var token = property.token;
                var shareLimitDays = property.shareLimitDays;
                var cloneTr = $("#publicShareListTable .publicShareTr:first").clone();
                cloneTr.data('token', token).data('sourcePath', sourcePath);

                cloneTr.find("#sourcePath").html(sourcePath);
                cloneTr.find("#shortUrl").html(shortUrl);
                cloneTr.find("#deadline").html(deadlineLocal);
                cloneTr.find("#pwd").html(pwd);

                cloneTr.find("#updateBtn").on('click', UpdateBtn.click);
                cloneTr.find("#cancelBtn").on('click', CancelBtn.click);
                cloneTr.appendTo("#publicShareListTable");
            });
        }
        $("#publicShareListTable .publicShareTr:gt(0)").fadeIn();
        $("#sharedListLoaginImg").hide();
        $("#publicShareListTable").show();
        PublicShareListTable.setStyle();
    },
    // 設置列表的背景色
    setStyle : function() {
        $(".publicShareTr:first").hide();
        $(".publicShareTr:even").css("background-color", "#F7F3F3");
        $(".publicShareTr:odd").css("background-color", "#FFFFFF");
    },
};

var PathSortTh = {
    click : function() {
        var sortBy = 'path';
        var sort = $(this).attr('alt');
        $('body').data('sortBy', sortBy);
        $('body').data('sort', sort);
        PublicShareListTable.setList();
        if (sort == 'desc') {
            $(this).attr('alt', 'asc');
            $('#pathSort').html('▼');
        } else {
            $(this).attr('alt', 'desc');
            $('#pathSort').html('▲');
        }
        $('#timeSort').html('');
    },
};

var TimeSortTh = {
    click : function() {
        var sortBy = 'time';
        var sort = $(this).attr('alt');
        $('body').data('sortBy', sortBy);
        $('body').data('sort', sort);
        PublicShareListTable.setList();
        if (sort == 'asc') {
            $(this).attr('alt', 'desc');
            $('#timeSort').html('▲');
        } else {
            $(this).attr('alt', 'asc');
            $('#timeSort').html('▼');
        }
        $('#pathSort').html('');
    },
};

var PublicShareTr = {
    // 移除被選定的 item
    remove : function() {
        if (PublicShareListTable.selectedTr) {
            PublicShareListTable.selectedTr.fadeOut(800, function() {
                $(this).remove();
                PublicShareListTable.setStyle();
            });
        }
    },
};

var UpdateBtn = {
    click : function() {
        var tr = $(this).closest('tr');
        // 設定目前在更新的 tr 物件
        PublicShareListTable.selectedTr = tr;
        var sourcePath = tr.data('sourcePath');
        PublicShare_Manager.createUi(sourcePath);
    },
};

var CancelBtn = {
    click : function() {
        var tr = $(this).closest('tr');
        // 設定目前要刪除的 tr 物件
        PublicShareListTable.selectedTr = tr;
        var token = tr.data('token');
        PublicShare_Manager.deleteByToken(token);
    },
};
