$(document).ready(function() {
	SettingsLog.getLog();
	$('#clearLogBtn').on('click', SettingsLog.clearLog);
	$('#refreshLogBtn').on('click', SettingsLog.getLog);
});

var SettingsLog = {
	getLog : function() {
		$('.logTable tr:gt(0)').remove();
		$.post(OC.filePath('settings', 'ajax', 'log.php'), {
			action : 'getLog',
		}, function(data) {
			// alert(data.status)
			if (data.status == 'success') {
				$.each(data.entries, function(key, val) {
					var tr = $('.logTable tr:first');
					var cloneTr = tr.clone();
					cloneTr.find('.level').html(val.level);
					cloneTr.find('.app').html(val.app);
					cloneTr.find('.message').html(val.message);
					var time = new Date(val.time * 1000);
					var year = time.getFullYear();
					var month = time.getMonth() + 1;
					var day = time.getDay();
					var hour = time.getHours();
					var minute = time.getMinutes();
					var second = time.getSeconds();
					timeStr = year + '-' + month + '-' + day + ' ' + hour + ':' + minute + ':' + second;
					cloneTr.find('.time').html(timeStr);
					$('.logTable').append($(cloneTr));
				});
			}

		});
	},
	clearLog : function() {
		$.post(OC.filePath('settings', 'ajax', 'log.php'), {
			action : 'clearLog',
		}, function(data) {
			$('.logTable tr:gt(0)').remove();
		});
	},
};
