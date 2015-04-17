$(document).ready(function() {
	ArtdiskRenderSettings.setDefault();
});

var ArtdiskRenderSettings = {
	formObj : function() {
		return $('#artdiskRenderSettingsForm');
	},
	setDefault : function() {
		ArtdiskRenderSettings.setDefaultAction();
	},
	setDefaultAction : function() {
		ArtdiskRenderSettings.formObj().find('#updateBtn').on('click', ArtdiskRenderSettings.updateSettings);
		ArtdiskRenderSettings.formObj().find('#revertBtn').on('click', ArtdiskRenderSettings.revertSettings);
	},
	updateSettings : function() {
		ArtdiskRenderSettings.formObj().find('#settingsMess').html('updating...');
		var configArr = ArtdiskRenderSettings.formObj().serializeArray();
		$.post(OC.filePath(ArtdiskRender.appId, "ajax", "settings.php"), {
			configArr : configArr,
		}, function(data) {
			if (data.configItems) {
				ArtdiskRenderSettings.setConfigItemsToInput(data.configItems);
			}
			ArtdiskRenderSettings.showUpdatedMess(data);
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
		ArtdiskRenderSettings.formObj().find('#settingsMess').html(mess).fadeIn().delay(3000).fadeOut();
	},
	revertSettings : function() {
		ArtdiskRenderSettings.formObj().find('input[type="text"]').val(function() {
			return $(this).attr('alt');
		});
	},
};
