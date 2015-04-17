$(document).ready(function() {
    PublicShare.setDefault();
});

var PublicShare = {
    appId : 'files_publicshare',
    setDefault : function() {
        if ( typeof FileActions !== 'undefined') {
            FileActions.register('all', t(PublicShare.appId, 'Share Link'), PublicShareActionBtn.show, PublicShare.action, 2, true);
            PublicShareActionBtn.getActionBtns();
        };
    },
    // 按下按鈕時的動作
    action : function(fileName) {
        // 設置被按下的檔案名稱
        $('body').data('selectedFileName', fileName);
        PublicShareActionBtn.changeShareAcionBtnByFileName(true);
        PublicShare.createUi();
    },
    // 產生連結分享介面
    createUi : function() {
        var selectedFileName = $('body').data('selectedFileName');
        var dir = $('#dir').val();
        dir = encodeURIComponent(dir);
        fileName = encodeURIComponent(selectedFileName);
        var sourcePath = dir + '/' + fileName;
        $.fancybox.open({
            type : 'iframe',
            href : OC.filePath(PublicShare.appId, '', 'ui.php?sourcePath=' + sourcePath + '&fileName=' + fileName),
            autoSize : true,
            openEffect : 'none',
            closeEffect : 'none',
        });
    },
    // 依 source path 取得分享資料
    getBySource : function(source) {
        var ret;
        $.ajax({
            type : 'POST',
            url : OC.linkTo(PublicShare.appId, 'ajax/publicshare.php'),
            data : {
                action : 'getBySource',
                source : source,
            },
            async : false,
            success : function(data) {
                if (data.status == 'success') {
                    ret = data.property;
                }
            }
        });
        return ret;
    },
};

var PublicShareActionBtn = {
    // 存放檔名對應的按鈕圖案
    btnMapping : {},
    // 預設的分享按鈕圖案
    defaultActionBtn : function() {
        return OC.imagePath(PublicShare.appId, 'share.png');
    },
    // 已經被分享的按鈕圖案
    sharedActionBtn : function() {
        return OC.imagePath(PublicShare.appId, 'shared.png');
    },
    // 取得每一個檔案的分享按鈕圖案
    getActionBtns : function() {
        $('#fileList tr').each(function() {
            var fileName = decodeURIComponent($(this).attr('data-file'));
            PublicShareActionBtn.btnMapping[fileName] = PublicShareActionBtn.getActionBtn(fileName);
        });
    },
    // 取得分享按鈕圖案
    getActionBtn : function(fileName) {
        var btn = PublicShareActionBtn.defaultActionBtn();
        var dir = $('#dir').val();
        var source = dir + '/' + fileName;
        var property = PublicShare.getBySource(source);
        if (property) {
            btn = PublicShareActionBtn.sharedActionBtn();
        }
        return btn;
    },
    // 顯示按鈕
    show : function(fileName) {
        if (PublicShareActionBtn.btnMapping[fileName]) {
            return PublicShareActionBtn.btnMapping[fileName];
        }
        return PublicShareActionBtn.defaultActionBtn();
    },
    // 將預設的分享按鈕改為已經被分享的按鈕圖案
    changeShareAcionBtnByFileName : function(toShared) {
        var fileName = $('body').data('selectedFileName');
        var tr = $('tr').filter('[data-file="' + fileName + '"]');
        var actionBtn = tr.find('a').filter('[title="' + t(PublicShare.appId, 'Share Link') + '"]');
        if (toShared) {
            actionBtn.find('img').attr('src', PublicShareActionBtn.sharedActionBtn());
            return;
        }
        actionBtn.find('img').attr('src', PublicShareActionBtn.defaultActionBtn());
    },
};
