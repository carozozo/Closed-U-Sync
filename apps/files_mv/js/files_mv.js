$(document).ready(function() {
    FilesMv.setDefault();
});

var FilesMv = {
    appId : 'files_mv',
    /**
     * 設置初始狀態
     */
    setDefault : function() {
        if ( typeof FileActions !== 'undefined') {
            FilesMv.setAction();
            FilesMv.setMultiAction();
        }
    },
    /**
     * 設置檔案的動作按鈕
     */
    setAction : function() {
        var img = OC.imagePath('files_mv', 'files_mv.png');
        FileActions.register('all', t('files_mv', 'Move'), img, FilesMv.moveAction, 6, true);
    },
    /**
     * 設置複選檔案的動作按鈕
     */
    setMultiAction : function() {
        var img = OC.imagePath('files_mv', 'files_mv.png');
        FileMultiActions.addActionObj('move', t('files_mv', 'Move'), img, FilesMv.multiAction, 30);
    },
    /**
     * 按下動作按鈕要執行的function
     */
    moveAction : function() {
        var file = FileActions.getCurrentFile();
        FilesMv.createUI(file);
    },
    /**
     * 按下複選動作按鈕要執行的function
     */
    multiAction : function() {
        var dir = $('#dir').val();
        var fileNameArr = FileMultiProcess.getSelectedFiles('name');
        var files = fileNameArr.join(';');
        FilesMv.createUI(files);
    },
    /**
     * 開啟目錄選單
     */
    createUI : function(files) {
        var dir = $('#dir').val();
        dir = encodeURIComponent(dir);
        files = encodeURIComponent(files);
        $.fancybox.open({
            type : 'iframe',
            href : OC.filePath('files_mv', '', 'menu.php?dir=' + dir + '&files=' + files),
            autoSize : false,
            openEffect : 'none',
            closeEffect : 'none',
        });
    },
};
