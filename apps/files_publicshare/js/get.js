$(document).ready(function() {
    PublicShare_Get.setDefault();
});

var PublicShare_Get = {
    setDefault : function() {
        PublicShare_Get.setStyle();
        PublicShare_Get.setDefaultHandler();
        var token = $('#token').val();
        var arr = PublicShare_Get.checkByToken(token);
        var result = arr.result;
        if (result == 'Need pwd') {
            MessDiv.showMess('Please insert password to get file', '：' + arr.path);
            $('#pwdDiv').show();
            return;
        }
        if (result == 'Share expired') {
            MessDiv.showMess(result, '：' + arr.path);
            return;
        }
        if (result == 'No data') {
            MessDiv.showMess(result);
            return;
        }
        var property = PublicShare_Get.getByPwdToken('', token);
        $('body').data('property', property);
        FileListTable.setDefault();
    },
    setDefaultHandler : function() {
        $('#topBtn').on('click', TopBtn.click);
        $('#pwdBtn').on('click', PwdBtn.click);
    },
    setStyle : function() {
        // iframe 依內容調整高度
        $('iframe').load(function() {
            this.style.height = this.contentWindow.document.body.offsetHeight + 'px';
        });
    },
    // 依 token 確認資料狀況，及是否需要密碼
    checkByToken : function(token) {
        var arr;
        $.ajax({
            type : 'POST',
            url : OC.linkTo(PublicShare.appId, 'ajax/get.php'),
            data : {
                action : "checkByToken",
                token : token,
            },
            async : false,
            success : function(data) {
                if (data.status == 'success') {
                    arr = data.resultArr;
                }
            }
        });
        return arr;
    },
    // 依 pwd 和 token 取得分享連結屬性
    getByPwdToken : function(pwd, token) {
        var ret;
        $.ajax({
            type : 'POST',
            url : OC.linkTo(PublicShare.appId, 'ajax/get.php'),
            data : {
                action : "getByPwdToken",
                pwd : pwd,
                token : token,
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
    // 取得指定 dir path 底下的所有檔案
    getFileList : function(pwd, token, dirPath) {
        var ret;
        $.ajax({
            type : 'POST',
            url : OC.linkTo(PublicShare.appId, 'ajax/get.php'),
            data : {
                action : "getFileList",
                pwd : pwd,
                token : token,
                dirPath : dirPath,
            },
            async : false,
            success : function(data) {
                if (data.status == 'success') {
                    contents = data.contents;
                    ret = contents;
                }
            }
        });
        return ret;
    },
};

var MessDiv = {
    showEmpty : function() {
        MessDiv.showMess('Empty Folder');
    },
    showDoanload : function() {
        MessDiv.showMess('Please Select To Download');
    },
    // 顯示訊息
    showMess : function(mess, extendMess) {
        var originMess = $('#messDiv').html();
        extendMess = extendMess || '';
        mess = t(PublicShare.appId, mess) + extendMess;
        if (originMess != '') {
            $('#messDiv').hide().html(mess).fadeIn();
            return;
        }
        $('#messDiv').html(mess);
    }
};

var FileListTable = {
    // 將取得的分享資料設置到 table 中
    setDefault : function() {
        MessDiv.showDoanload();
        $('#fileListTable').fadeIn();
        var property = $('body').data('property');
        var tr = $('.fileListTr:first');
        var dirPath = property.dirname;

        // 將取得的路徑中 dir 的路徑設為要隱藏的部份
        $('body').data('rootPath', dirPath);
        PathTr.clean();
        $('#topBtn').hide();
        $('.fileListTr:gt(0)').remove();
        FileListTr.setDefault(tr, property);
    },
    // 將取得的檔案列表放到 table 中
    setFileList : function() {
        PathTr.setDefault();
        $('#topBtn').fadeIn();
        $('.fileListTr:gt(0)').remove();
        var tr = $('.fileListTr:first');
        var pwd = $('#pwdInp').val();
        var token = $('#token').val();
        var dirPath = $('body').data('selectedDirPath');
        var contetns = PublicShare_Get.getFileList(pwd, token, dirPath);
        if (!contetns) {
            MessDiv.showEmpty();
            tr.hide();
            return;
        }
        $.each(contents, function(index, property) {
            var cloneTr = tr.clone();
            FileListTr.setDefault(cloneTr, property);
            $('#fileListTable').append(cloneTr);
        });
        tr.remove();
        MessDiv.showDoanload();
        FileListTr.setStyle();
        $('.fileListTr').hide().fadeIn();
    },
};

var PathTr = {
    // 設置所在位置的顯示
    setDefault : function() {
        var tr = $('#pathTr');
        var fullPathLink = PathTr.setPathLink();
        tr.find('#locationSpan').html(fullPathLink);
        PathSpan.setHandler();
    },
    // 清除路徑顯示
    clean : function() {
        var tr = $('#pathTr');
        tr.find('#locationSpan').html('');
    },
    // 將選取的路徑轉為連結
    setPathLink : function() {
        var selectedDirPath = $('body').data('selectedDirPath');
        // 將要顯示在前端的連結轉成 array
        var displayPath = selectedDirPath.replace($('body').data('rootPath'), '');
        var sourceArr = displayPath.split('/');
        var fullPathLink = '';
        var sourcePath = $('body').data('rootPath');
        // 依序將每個path組合起來
        $.each(sourceArr, function(index, pathName) {
            if (pathName != '') {
                // 這裡的 sourcePath 是檔案真正的路徑
                sourcePath += '/' + pathName;
                fullPathLink += '<span class="pathSpan" alt="' + sourcePath + '">/' + pathName + '</span>';
            }
        });
        fullPathLink += '/';
        return fullPathLink;
    }
};

var PathSpan = {
    setHandler : function() {
        $('.pathSpan').on('click', PathSpan.click);
        $('.pathSpan').on('mouseover', PathSpan.mouseOver);
        $('.pathSpan').on('mouseout', PathSpan.mouseOut);
    },
    click : function() {
        sourcePath = $(this).attr('alt');
        if (sourcePath != $('body').data('selectedDirPath')) {
            // 將目前按下的檔案路徑設為被選取的資料夾路徑
            $('body').data('selectedDirPath', sourcePath);
            FileListTable.setFileList();
        }
    },
    mouseOver : function() {
        var tarIndex = $("span").index($(this));
        $('.pathSpan').each(function() {
            var index = $("span").index($(this));
            if (index <= tarIndex) {
                $(this).css('color', '#35537A');
            }
        });
    },
    mouseOut : function() {
        var tarIndex = $("span").index($(this));
        $('.pathSpan').each(function() {
            var index = $("span").index($(this));
            if (index <= tarIndex) {
                $(this).css('color', '#FFFFFF');
            }
        });

    },
};

var FileListTr = {
    // 設置 file 列表的樣式
    setDefault : function(tr, property) {
        var fileName = property.basename;
        var fileType = property.type;
        var fileSize = property.sizeHuman;
        var sourcePath = property.path;
        var openBtn = tr.find('.openBtn');
        var fileSizeSpan = tr.find('.fileSizeSpan');
        var downloadBtn = tr.find('.downloadBtn');
        // 如果檔案類型為資料夾
        if (fileType == 'dir') {
            // 如果檔案名稱頭尾沒有[/]，則補上
            if (fileName.indexOf('/') != 0) {
                fileName = '/' + fileName;
            }
            if (fileName.lastIndexOf('/') != fileName.length - 1) {
                fileName += '/';
            }
            fileSizeSpan.html('');
            // 先移除按鈕原先的 click 動作，再重新綁定，以免出現 exception
            openBtn.data('filePath', sourcePath).off('click').on('click', OpenBtn.click).show();
        } else {
            fileSizeSpan.html(fileSize);
            openBtn.hide();
        }
        tr.fadeIn();
        tr.find('.fileNameSpan').html(fileName);
        downloadBtn.data('filePath', sourcePath).on('click', DownloadBtn.click).show();
        // 清除 iframe 的下載url
        var downloadFrame = tr.find('.downloadFrame');
        downloadFrame.attr('src', '');
    },
    setStyle : function() {
        $('.fileListTr:even').css('background-color', '#FFFFFF');
        $('.fileListTr:odd').css('background-color', '#F7F3F3');
    },
};

var PwdBtn = {
    click : function() {
        var pwd = $('#pwdInp').val();
        var token = $('#token').val();
        var property = PublicShare_Get.getByPwdToken(pwd, token);
        // 如果輸入的密碼符合
        if (property) {
            // 設置 user 輸入的密碼
            // $('body').data('pwd', pwd);
            $('#pwdDiv').fadeOut('fast', function() {
                $('body').data('property', property);
                FileListTable.setDefault();
            });
            return;
        }
        MessDiv.showMess('Wrong Password');
    },
};

var TopBtn = {
    click : function() {
        FileListTable.setDefault();
    },
};

var OpenBtn = {
    click : function() {
        var filePath = $(this).data('filePath');
        // 將目前按下的檔案路徑設為被選取的資料夾路徑
        $('body').data('selectedDirPath', filePath);
        FileListTable.setFileList();
    },
};

var DownloadBtn = {
    click : function() {
        var token = $('#token').val();
        var filePath = $(this).data('filePath');
        var encodeFilePath = encodeURIComponent(filePath);
        var property = $('body').data('property');
        var pwd = property.pwd;
        var host = window.location.protocol + '//' + window.location.host;
        var url = host + '/apps/' + PublicShare.appId + '/download.php?token=' + token + '&filePath=' + encodeFilePath + '&pwd=' + pwd;
        var td = $(this).closest('td');
        var downloadFrame = td.find('.downloadFrame');
        downloadFrame.attr('src', url);
        DownloadBtn.unable($(this));
    },
    // 將下載按鈕設為無效
    unable : function(btnObj) {
        btnObj.fadeOut();
        // var mess = t(PublicShare.appId, 'Downloading');
        // btnObj.val(mess).on('click', false);
    },
};
