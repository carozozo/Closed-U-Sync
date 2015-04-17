var OC_Appconfig = {
	getValue : function(appId, key, defaultVal) {
		var val = defaultVal;
		$.ajax(OC.filePath('core', 'ajax', 'app_config.php'), {
			async : false,
			data : {
				action : 'getValue',
				appId : appId,
				key : key,
				defaultVal : defaultVal,
			},
			type : 'POST',
			success : function(data) {
				if (data.status == 'success') {
					val = data.result;
				}
			},
		});
		return val;
	},
};
