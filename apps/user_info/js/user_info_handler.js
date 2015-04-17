$(document).ready(function() {
	UserInfo_Handler.setDefault();
});
var UserInfo_Handler = {
	appId : 'user_info',
	setDefault : function() {
		// UserInfo_Handler.hideMess();
		UserInfo_Handler.setVaildateMessColor();
		UserInfo_Handler.setInpRule();
		UserInfo_Handler.setAction();
	},
	// 設置email是否已驗證的文字顏色
	setVaildateMessColor : function() {
		var vaildateMessColor = $('#vaildateMessColor').val();
		if (vaildateMessColor == 'green') {
			$('#vaildateMessSpan').css('color', '#9bc11f');
		}else{
		    // 如果未驗證，則更改按鈕文字
		    var btnMess=t(UserInfo_Handler.appId,'Validate email');
		    $('#updateEmailBtn').val(btnMess);
		}
	},
	setInpRule : function() {
		// 只予許輸入英數及特定符號
		$('.userInfoPwdInp').alphanumeric({
			allow : "_@-.",
			allowSpace : false,
		});
		$('#userInfoEmailInp').alphanumeric({
			allow : "_@-.",
			allowSpace : false,
		});
	},
	setAction : function() {
		$('#updateNicknameBtn').on('click', UserInfo_Handler.changeNickname);
		$('#updatePasswordBtn').on("click", UserInfo_Handler.changePwd);
		$('#updateEmailBtn').on("click", UserInfo_Handler.changeEmail);
	},
	changeNickname : function() {
		UserInfo_Handler.showUpdating();
		var nickname = $('#userInfoNicknameInp').val();
		nickname = $.trim(nickname);
		$.post(OC.filePath(UserInfo_Handler.appId, "ajax", "user_info.php"), {
			action : 'changeNickname',
			nickname : nickname,
		}, function(data) {
			var mess = data.message;
			UserInfo_Handler.showMess(mess);
		});
	},
	changePwd : function() {
		var oldPwd = $('#userInfoOldPasswordInp').val();
		var newPwd = $('#userInfoNewPasswordInp').val();
		var checkNewPwd = $('#userInfoCheckNewPasswordInp').val();
		oldPwd = $.trim(oldPwd);
		newPwd = $.trim(newPwd);
		checkNewPwd = $.trim(checkNewPwd);
		var ifPwdPass = UserInfo_Handler.checkPwd(oldPwd, newPwd, checkNewPwd);
		if (ifPwdPass == true) {
			UserInfo_Handler.showUpdating();
			$.post(OC.filePath(UserInfo_Handler.appId, "ajax", "user_info.php"), {
				action : 'changePwd',
				oldPwd : oldPwd,
				newPwd : newPwd,
				checkNewPwd : checkNewPwd,
			}, function(data) {
				var mess = data.message;
				$('.userInfoPwdInp').val('');
				UserInfo_Handler.showMess(mess);
			});
		} else {
			$('.userInfoPwdInp').val('');
			UserInfo_Handler.showMess(ifPwdPass);
		}
	},
	checkPwd : function(oldPwd, newPwd, checkNewPwd) {
		// 如果有空值
		if (oldPwd == '' || newPwd == '' || checkNewPwd == '') {
			return t(UserInfo_Handler.appId, 'Please insert password');
		}
		if (newPwd != checkNewPwd) {
			return t(UserInfo_Handler.appId, 'Wrong check new password');
		}
		return true;
	},
	changeEmail : function() {
		var email = $('#userInfoEmailInp').val();
		var vaildateMessColor = $('#vaildateMessColor').val();
		email = $.trim(email);
		if (email != '') {
			$.post(OC.filePath(UserInfo_Handler.appId, "ajax", "user_info.php"), {
				action : 'changeEmail',
				email : email,
				vaildateMessColor : vaildateMessColor,
			}, function(data) {
				var mess = data.message;
				UserInfo_Handler.showMess(mess);
			});
		}
	},
	showMess : function(mess) {
		$('#userInfoMess').html(mess).show().delay(5000).fadeOut();
	},
	hideMess : function() {
		$('#userInfoMess').html('').hide();
	},
	showUpdating : function() {
		$('#userInfoMess').html(t(UserInfo_Handler.appId, 'Updating')).show();
	},
};
