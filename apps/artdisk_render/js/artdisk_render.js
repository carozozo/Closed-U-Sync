$(document).ready(function() {
    ArtdiskRender.setDefault();
    ArtdiskRender.setAction();
});

var ArtdiskRender = {
    appId : 'artdisk_render',
    renderPath : null,
    setDefault : function() {
        ArtdiskRender.getRenderPath();
        ArtdiskRender.showMessInRenderPath();
        ArtdiskRender.createBtn();
    },
    createBtn : function() {
        if (location.href.match(/\/files\/index\.php/)) {
            var artdiskRenderBtn = '<div id="artdiskRenderBtn" class="button"></div>';
            var artdiskRenderBtnLink = '<a href="#" onclick="return false;">&nbsp;' + t(ArtdiskRender.appId, 'Artdisk Render') + '</a>';
            $(artdiskRenderBtn).appendTo('div #controls .actions');
            $(artdiskRenderBtnLink).appendTo('div #artdiskRenderBtn');
        }
    },
    setAction : function() {
        $('#artdiskRenderBtn > a').on('click', ArtdiskRender.createRender);
    },
    getRenderPath : function() {
        $.ajax({
            type : 'POST',
            url : OC.linkTo(ArtdiskRender.appId, 'ajax/artdisk_render.php'),
            data : {
                action : 'getRenderPath',
            },
            async : false,
            success : function(data) {
                if (data.status == 'success') {
                    ArtdiskRender.renderPath = data.renderPath;
                }
            },
        });
    },
    createRender : function() {
        // 如果不是在算圖資料夾底下，才執行
        if (!ArtdiskRender.ifUserInRenderPath()) {
            $.ajax({
                type : 'POST',
                url : OC.linkTo(ArtdiskRender.appId, 'ajax/artdisk_render.php'),
                data : {
                    action : 'createRender',
                },
                success : function(data) {
                    if (data.status == 'success') {
                        var mess = data.message;
                        var currentStatus = data.currentStatus;
                        // 產生成功
                        if (currentStatus > 0) {
                            // 跳轉到算圖資料夾
                            window.location.href = 'index.php?dir=' + ArtdiskRender.renderPath;
                        }
                        // 產生失敗，顯示訊息
                        FilesNotification.showInTime(mess, 3000);
                    }
                },
            });
        }
    },
    // 當 user 在算圖資料夾底下的時候，顯示提示訊息
    showMessInRenderPath : function() {
        if (ArtdiskRender.ifUserInRenderPath()) {
            var mess = t(ArtdiskRender.appId, 'Please upload files that you want to render 3D model');
            // 因為getRenderPath影響，如果直接呼叫會產生warning而無法顯示訊息，故延遲執行
            setTimeout(function() {
                // 在頁面顯示訊息
                FilesNotification.showInTime(mess, 3000);
            }, 500);
            $('#emptyfolder').html(mess);
        }
    },
    ifUserInRenderPath : function() {
        // 取得 uri 中後面的變數名稱
        var search = window.location.search;
        var renderPath = '?dir=' + ArtdiskRender.renderPath;
        if (search.indexOf(renderPath) == 0) {
            return true;
        }
    },
};
