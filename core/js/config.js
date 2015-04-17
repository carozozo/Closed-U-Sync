var OC_Config = {
	getValue : function(key, defaultVal, configName) {
		var val = defaultVal;
		$.ajax(OC.filePath('core', 'ajax', 'config.php'), {
			async:false,
			data : {
				action : 'getValue',
				key : key,
				defaultVal : defaultVal,
				configName : configName,
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
