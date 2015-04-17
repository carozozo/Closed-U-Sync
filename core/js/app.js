var OC_App = {
	isEnabled : function(appId) {
		var enabled = false;
		$.ajax(OC.filePath('core', 'ajax', 'app.php'), {
			async : false,
			data : {
				action : 'isEnabled',
				appId : appId,
			},
			type : 'POST',
			success : function(data) {
				if (data.status == 'success') {
					enabled = true;
				}
			},
		});
		return enabled;
	},
};
