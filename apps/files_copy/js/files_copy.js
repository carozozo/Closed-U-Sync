$(document).ready(function() {
    FilesCopy.setDefault();
});

var FilesCopy = {
    appId : 'files_copy',
    /**
     * 設置初始狀態
     */
    setDefault : function() {
        if ( typeof FileActions !== 'undefined') {
            FilesCopy.setAction();
            FilesCopy.setMultiAction();
        }
    },
    /**
     * 設置檔案的動作按鈕
     */
    setAction : function() {
        var img = OC.imagePath('files_copy', 'files_copy.png');
        FileActions.register('all', t('files_copy', 'Copy'), img, FilesCopy.copyAction, 7);
    },
    /**
     * 設置複選檔案的動作按鈕
     */
    setMultiAction : function() {
        var img = OC.imagePath('files_copy', 'files_copy.png');
        FileMultiActions.addActionObj('copy', t('files_copy', 'Copy'), img, FilesCopy.multiAction, 30);
    },
    /**
     * 按下動作按鈕要執行的function
     */
    copyAction : function() {
        var file = FileActions.getCurrentFile();
        FilesCopy.createUI(file);
    },
    /**
     * 按下複選動作按鈕要執行的function
     */
    multiAction : function() {
        var dir = $('#dir').val();
        var fileNameArr = FileMultiProcess.getSelectedFiles('name');
        var files = fileNameArr.join(';');
        FilesCopy.createUI(files);
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
            href : OC.filePath('files_copy', '', 'menu.php?dir=' + dir + '&files=' + files),
            autoSize : false,
            openEffect : 'none',
            closeEffect : 'none',
        });
    },
};
