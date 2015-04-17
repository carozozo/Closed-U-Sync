$(document).ready(function() {
	PublicShareSettings.setDefault();
});

var PublicShareSettings = {
	formObj : function() {
		return $('#publicShareSettingsForm');
	},
	setDefault : function() {
		PublicShareSettings.setInputLimit();
		PublicShareSettings.setDefaultAction();
	},
	setInputLimit : function() {
		PublicShareSettings.formObj().find('#adEnabled').on('keyup', function() {
			PublicShareSettings.only1or0($(this));
		});
		PublicShareSettings.formObj().find('#shareLimitDays').on('keyup', function() {
			PublicShareSettings.fixNum($(this));
		});
	},
	setDefaultAction : function() {
		PublicShareSettings.formObj().find('#updateBtn').on('click', PublicShareSettings.updateSettings);
		PublicShareSettings.formObj().find('#revertBtn').on('click', PublicShareSettings.revertSettings);
	},
	updateSettings : function() {
		PublicShareSettings.formObj().find('#settingsMess').html('updating...');
		var configArr = PublicShareSettings.formObj().serializeArray();
		$.post(OC.filePath(PublicShare.appId, "ajax", "settings.php"), {
			configArr : configArr,
		}, function(data) {
			if (data.configItems) {
				PublicShareSettings.setConfigItemsToInput(data.configItems);
			}
			PublicShareSettings.showUpdatedMess(data);
		});

	},
	setConfigItemsToInput : function(configItems) {
		$.each(configItems, function(index, configItem) {
			var key = configItem.configkey;
			var val = configItem.configvalue;
			$('#' + key).val(val);
		});
	},
	showUpdatedMess : function(data) {
		var mess = data.status;
		if (data.noMethodArrStr) {
			mess += ', no method: ' + data.noMethodArrStr;
		}
		PublicShareSettings.formObj().find('#settingsMess').html(mess).fadeIn().delay(3000).fadeOut();
	},
	revertSettings : function() {
		PublicShareSettings.formObj().find('input[type="text"]').val(function() {
			return $(this).attr('alt');
		});
	},
	only1or0 : function(obj) {
		var val = obj.val();
		if (val != "0" && val != "1") {
			obj.val(obj.attr('alt'));
		}
	},
	fixNum : function(obj) {
		var val = obj.val();
		// 如果轉成整數失敗，則代入欄位原本的值
		var newVal = parseInt(val, 10) || obj.attr('alt');
		if (newVal < 0) {
			newVal = -newVal;
		}
		// 如果轉換後的值和輸入的值不一樣,則代入新值
		if (newVal != val) {
			obj.val(newVal);
		}
	},
};
