/**
 * Copyright (c) 2011, Robin Appelman <icewind1991@gmail.com>
 * This file is licensed under the Affero General Public License version 3 or later.
 * See the COPYING-README file.
 */

$(document).ready(function() {
	$("#passwordbutton").on('click', function() {
		SettingsPersonal.changePwd();
	});
	
	$('#lostpassword #email').blur(function(event) {
		SettingsPersonal.lostPwd(event);
	});
	
	
	$('#nicknameform #nickname').on('blur', function(event) {
		SettingsPersonal.changeNickname(event);
	});
	
	if ($('#nicknameform #nickname').val() != '') {
		$('#nicknameform #nickname').trigger('blur');
	}
	
	$("#languageinput").chosen();

	$("#languageinput").on('change', function() {
		SettingsPersonal.setLanguage();
	});
});

SettingsPersonal = {
	changePwd : function() {
		if ($('#pass1').val() != '' && $('#pass2').val() != '') {
			// Serialize the data
			var post = $("#passwordform").serialize();
			$('#passwordchanged').hide();
			$('#passworderror').hide();
			// Ajax foo
			$.post('ajax/changepassword.php', post, function(data) {
				if (data.status == "success") {
					$('#pass1').val('');
					$('#pass2').val('');
					$('#passwordchanged').show();
				} else {
					$('#passworderror').html(data.data.message);
					$('#passworderror').show();
				}
			});
			return false;
		} else {
			$('#passwordchanged').hide();
			$('#passworderror').show();
			return false;
		}
	},
	//其實是修改email
	lostPwd : function(event) {
		event.preventDefault();
		OC.msg.startSaving('#lostpassword .msg');
		var post = $("#lostpassword").serialize();
		$.post('ajax/lostpassword.php', post, function(data) {
			OC.msg.finishedSaving('#lostpassword .msg', data);
		});
	},
	changeNickname : function(event) {
		event.preventDefault();
		OC.msg.startSaving('#nicknameform .msg');
		var post = $("#nicknameform").serialize();
		$.post('ajax/changenickname.php', post, function(data) {
			OC.msg.finishedSaving('#nicknameform .msg', data);
		});
	},
	setLanguage : function() {
		// Serialize the data
		var post = $("#languageinput").serialize();
		// Ajax foo
		$.post('ajax/setlanguage.php', post, function(data) {
			if (data.status == "success") {
				location.reload();
			} else {
				$('#passworderror').html(data.data.message);
			}
		});
		return false;
	},
};

OC.msg = {
	startSaving : function(selector) {
		$(selector).html(t('settings', 'Saving...')).removeClass('success').removeClass('error').stop(true, true).show();
	},
	finishedSaving : function(selector, data) {
		if (data.status == "success") {
			var message = t('settings', data.data.message);
			$(selector).html(message).addClass('success').stop(true, true).delay(3000).fadeOut(600);
		} else {
			$(selector).html(data.data.message).addClass('error');
		}
	}
}
