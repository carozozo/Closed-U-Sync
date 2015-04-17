$(document).ready(function() {
	U_ChannelSettings.setDefault();
});

var U_ChannelSettings = {
	formObj : function() {
		return $('#uChannelSettingsForm');
	},
	setDefault : function() {
		U_ChannelSettings.setDefaultAction();
	},
	setDefaultAction : function() {
		U_ChannelSettings.formObj().find('#updateBtn').on('click', function() {
			U_ChannelSettings.updateSettings($('#uChannelSettingsForm'), 'u_channel', 'u_channel_settings.php');
		});
		U_ChannelSettings.formObj().find('#revertBtn').on('click', U_ChannelSettings.revertSettings);
	},
	updateSettings : function() {
		U_ChannelSettings.formObj().find('#settingsMess').html('updating...');
		var configArr = U_ChannelSettings.formObj().serializeArray();
		$.post(OC.filePath("u_channel", "ajax", "u_channel_settings.php"), {
			configArr : configArr,
		}, function(data) {
			if (data.configItems) {
				U_ChannelSettings.setConfigItemsToInput(data.configItems);
			}
			U_ChannelSettings.showUpdatedMess(data);
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
		U_ChannelSettings.formObj().find('#settingsMess').html(mess).fadeIn().delay(3000).fadeOut();
	},
	revertSettings : function() {
		U_ChannelSettings.formObj().find('input[type="text"]').val(function() {
			return $(this).attr('alt');
		});
	},
};
