/**
 * ownCloud - Files Notification
 *
 * @author Caro Huang
 * @copyright 2013 www.u-sync.com
 *
 * Web 檔案列表頁面中「提示訊息」的設定及通用 function
 */

$(document).ready(function() {
    FilesNotification.autoPosition();
});

var FilesNotification = {
    // 設定提示訊息的位置會隨視窗置中
    autoPosition : function() {
        $(window).resize(function() {
            var width = $(window).width() - $('#notification').width();
            $("#notification").css('left', FilesNotification.getPosition());
        });
    },
    // 取得訊息要擺放的位置
    getPosition : function() {
        var width = $(window).width() - $('#notification').width();
        width = width / 2;
        return width;
    },
    // 顯示訊息
    show : function(mess) {
        // 先放入訊息(這樣才能取得長度)，之後再取得位置
        $('#notification').html(mess);
        $('#notification').css('left', FilesNotification.getPosition());
        $('#notification').fadeIn();
    },
    // 隱藏訊息
    hide:function(){
        $('#notification').fadeOut();
    },
    // 顯示訊息(毫秒)
    showInTime : function(mess, mSeconds) {
        $('#notification').html(mess);
        $('#notification').css('left', FilesNotification.getPosition());
        $('#notification').fadeIn().delay(mSeconds).fadeOut();
    },
};

