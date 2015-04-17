var OC_Helper = {
	publicFunction : function(data) {
		var result;
		$.ajax(OC.filePath('core', 'ajax', 'helper.php'), {
			async : false,
			data : data,
			type : 'POST',
			success : function(data) {
				if (data.status == 'success') {
					result = data.result;
				}
			},
		});
		return result;
	},
	randomPassword : function(length, ifEng, ifNum, ifUpper) {
		var data = {
			action : 'randomPassword',
			length : length,
			ifEng : ifEng,
			ifNum : ifNum,
			ifUpper : ifUpper
		};
		return OC_Helper.publicFunction(data);
	},
	audioTypeArr : function() {
		var data = {
			action : 'audioTypeArr',
		};
		return OC_Helper.publicFunction(data);
	},
	mediaTypeArr : function() {
		var data = {
			action : 'mediaTypeArr',
		};
		return OC_Helper.publicFunction(data);
	},
};
