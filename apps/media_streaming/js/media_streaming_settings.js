$(document).ready(function() {
	MediaStreamingSettings.setDefault();
});

var MediaStreamingSettings = {
	formObj : function() {
		return $('#mediaStreamingSettingsForm');
	},
	setDefault : function() {
		MediaStreamingSettings.setInputLimit();
		MediaStreamingSettings.setDefaultAction();
	},
	setInputLimit : function() {
		MediaStreamingSettings.formObj().find('#useHls, #convertEnable, #sendEmailAfterConvert, #notificationAfterConvert').on('keyup', function() {
			MediaStreamingSettings.only1or0($(this));
		});
		MediaStreamingSettings.formObj().find('#limitSize, #convertLimitTimes, #compareSeconds').on('keyup', function() {
			MediaStreamingSettings.fixNum($(this));
		});
	},
	setDefaultAction : function() {
		MediaStreamingSettings.formObj().find('#updateBtn').on('click', MediaStreamingSettings.updateSettings);
		MediaStreamingSettings.formObj().find('#revertBtn').on('click', MediaStreamingSettings.revertSettings);
		MediaStreamingSettings.formObj().find('#getListBtn').on('click', MediaStreamingSettings.getStreamingList);
	},
	updateSettings : function() {
		MediaStreamingSettings.formObj().find('#settingsMess').html('updating...');
		var configArr = MediaStreamingSettings.formObj().serializeArray();
		$.post(OC.filePath(MediaStreaming.appId, "ajax", "media_streaming_settings.php"), {
			configArr : configArr,
		}, function(data) {
			if (data.configItems) {
				MediaStreamingSettings.setConfigItemsToInput(data.configItems);
			}
			MediaStreamingSettings.showUpdatedMess(data);
		});
	},
	getStreamingList : function() {
		$.fancybox.open({
			type : 'iframe',
			href : OC.filePath(MediaStreaming.appId, "", "media_streaming_list.php"),
			autoSize : true,
			// openEffect : 'none',
			// closeEffect : 'none'
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
		MediaStreamingSettings.formObj().find('#settingsMess').html(mess).fadeIn().delay(3000).fadeOut();
	},
	revertSettings : function() {
		MediaStreamingSettings.formObj().find('input[type="text"]').val(function() {
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
		//如果轉成整數失敗，則代入欄位原本的值
		var newVal = parseInt(val, 10) || obj.attr('alt');
		if (newVal < 0) {
			newVal = -newVal;
		}
		//如果轉換後的值和輸入的值不一樣,則代入新值
		if (newVal != val) {
			obj.val(newVal);
		}
	},
};
