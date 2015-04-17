$(document).ready(function() {
    PublicShare_Ui.setDefault();
});

var PublicShare_Ui = {
    setDefault : function() {
        PublicShare_Ui.setDefaultHandler();
    },
    setDefaultHandler : function() {
        DeadlineInp.setDefaultHandler();
        PwdInp.setDefaultHandler();
        $('#emailBtn').on('click', EmailBtn.click);
        $('#clearPwdBtn').on('click', ClearPwdBtn.click);
        $('#advanceLin').on('click', AdvanceLin.click);
        $('#updateBtn').on('click', UpdateBtn.click);
    },
    // 更新 public share
    updateByToken : function(deadline, pwd, token) {
        var property;
        $.ajax({
            type : 'POST',
            url : OC.linkTo(PublicShare.appId, 'ajax/publicshare.php'),
            async : false,
            data : {
                action : 'updateByToken',
                deadline : deadline,
                pwd : pwd,
                token : token,
            },
            success : function(data) {
                property = data.property;
            }
        });
        return property;
    },
};

var DeadlineInp = {
    setDefaultHandler : function() {
        // 預設分享天數
        var shareLimitDays = $('#shareLimitDays').val();
        var datepickerOpt = {
            minDate : 0,
            dateFormat : 'yy-mm-dd',
            maxDate : '+' + shareLimitDays + 'D',
        };
        $('#deadlineInp').datepicker(datepickerOpt).attr('readonly', 'true');
    },
};

var PwdInp = {
    // 密碼欄位預設狀態
    setDefaultHandler : function() {
        // 設置只能輸入英數字
        $('#pwdInp').alphanumeric();
    },
};

var ClearPwdBtn = {
    click : function() {
        $('#pwdInp').val('');
    },
};

var AdvanceLin = {
    click : function() {
        $(this).fadeOut('fast', function() {
            $('#pwdTr').fadeIn();
        });
    },
};

var UpdateBtn = {
    click : function() {
        var deadline = $('#deadlineInp').val();
        var pwd = $('#pwdInp').val();
        var token = $('#token').val();
        property = PublicShare_Ui.updateByToken(deadline, pwd, token);
        status = property.status;
        MessSpan.show(status);
        // PublicShareListTable，代表是在分享管理頁面
        if (parent.PublicShareListTable) {
            parent.PublicShareListTable.setList();
        }
    },
};

var EmailBtn = {
    click : function() {
        EmailBtn.sendEmail($(this));
    },
    // 開啟 browser 預設的 email 軟體
    sendEmail : function(obj) {
        var nickname = $('#nickname').val();
        var link = $('#linkSpan').html();
        var deadline = $('#deadlineInp').val();
        var sourcePath = $('#sourcePathSpan').html();
        if (PublicShare.siteTitle == '') {
            PublicShare.siteTitle = OC_Config.getValue('siteTitle', '', 'CONFIG_CUSTOM');
        }
        var subject = nickname + " has shared '" + sourcePath + "' with you using " + PublicShare.siteTitle;
        var body = "Here's a link to '" + sourcePath + "' in my " + PublicShare.siteTitle + "%0D%0A";
        body += link + "%0D%0A";
        body += "(The valid date until " + deadline + ")%0D%0A";
        var send_it = "mailto:?subject=" + subject + "%0D%0A";
        send_it += "&body= " + body;
        document.location = send_it;
    },
};

var MessSpan = {
    // 顯示訊息
    show : function(mess) {
        mess = t(PublicShare.appId, mess);
        $('#messSpan').hide().html(mess).fadeIn();
    },
};
