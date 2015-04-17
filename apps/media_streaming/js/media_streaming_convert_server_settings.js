$(document).ready(function() {
	MediaStreamingConvertServerSettings.setDefault();
});

var MediaStreamingConvertServerSettings = {
	formObj : function() {
		return $('#mediaConvertServerSettingsForm');
	},
	setDefault : function() {
		MediaStreamingConvertServerSettings.hideTmpTr();
		MediaStreamingConvertServerSettings.getConvertServerList();
		MediaStreamingConvertServerSettings.setNewInputRule();
		MediaStreamingConvertServerSettings.setDefaultAction();
	},
	hideTmpTr : function() {
		// 隱藏範本tr
		$('#mediaConvertServerTable tr').first().hide();
	},
	setNewInputRule : function() {
		// 新增convert server的輸入欄位，只允許數字及[.]
		$('#newInp').numeric({
			allow : ".",
		});
	},
	setDefaultAction : function() {
		MediaStreamingConvertServerSettings.formObj().find('#getListBtn').on('click', function() {
			MediaStreamingConvertServerSettings.getConvertList();
		});
		MediaStreamingConvertServerSettings.formObj().find('#refreshBtn').on('click', function() {
			MediaStreamingConvertServerSettings.getConvertServerList();
		});
		MediaStreamingConvertServerSettings.formObj().find('.defaultBtn').on('click', function() {
			MediaStreamingConvertServerSettings.setConvertServerDefault($(this));
		});
		MediaStreamingConvertServerSettings.formObj().find('.delBtn').on('click', function() {
			MediaStreamingConvertServerSettings.delConvertServer($(this));
		});
		MediaStreamingConvertServerSettings.formObj().find('#newBtn').on('click', function() {
			MediaStreamingConvertServerSettings.newConvertServer();
		});
	},
	getConvertList : function() {
		$.fancybox.open({
			type : 'iframe',
			href : OC.filePath(MediaStreaming.appId, "", "media_streaming_convert_list.php"),
			autoSize : true,
			// openEffect : 'none',
			// closeEffect : 'none'
		});
	},
	getConvertServerList : function() {
		MediaStreamingConvertServerSettings.showLoading();
		$('.convertServerTr:gt(0)').remove();
		$.post(OC.filePath(MediaStreaming.appId, "ajax", "media_streaming_convert_server_settings.php"), {
			action : 'getConvertServerList',
		}, function(data) {
			MediaStreamingConvertServerSettings.hideLoading();
			if (data.status == 'success') {
				var tr = $('.convertServerTr').first();
				var convertServerList = data.convertServerList;
				$.each(convertServerList, function(index, convertServer) {
					var cloneTr = tr.clone();
					var serverIp = convertServer['server_ip'];
					cloneTr.fadeIn();
					cloneTr.find('.serverIpSpan').html(serverIp);
					cloneTr.find('.pidSpan').html(convertServer['pid']);
					cloneTr.find('.startTimeSpan').html(convertServer['start_time']);
					cloneTr.find('.statusSpan').html(convertServer['status']);
					MediaStreamingConvertServerSettings.setBtnAction(cloneTr, serverIp);
					$('.convertServerTr').last().after(cloneTr);
				});

			}
		});
	},
	setConvertServerDefault : function(obj) {
		if (confirm('Are you sure?')) {
			var serverIp = obj.attr('alt');
			$.post(OC.filePath(MediaStreaming.appId, "ajax", "media_streaming_convert_server_settings.php"), {
				action : 'setConvertServerDefault',
				serverIp : serverIp,
			}, function(data) {
				if (data.status == 'success') {
					var tr = obj.parent().parent();
					tr.find('.pidSpan').html('');
					tr.find('.startTimeSpan').html('');
					tr.find('.statusSpan').html(data.message);
				}
			});
		}
	},
	delConvertServer : function(obj) {
		if (confirm('Are you sure?')) {
			var serverIp = obj.attr('alt');
			$.post(OC.filePath(MediaStreaming.appId, "ajax", "media_streaming_convert_server_settings.php"), {
				action : 'delConvertServer',
				serverIp : serverIp,
			}, function(data) {
				if (data.status == 'success') {
					var tr = obj.parent().parent();
					tr.remove();
				}
			});
		}
	},
	newConvertServer : function() {
		var serverIp = $('#newInp').val();
		if (serverIp) {
			$.post(OC.filePath(MediaStreaming.appId, "ajax", "media_streaming_convert_server_settings.php"), {
				action : 'newConvertServer',
				serverIp : serverIp,
			}, function(data) {
				if (data.status == 'success') {
					var tr = $('.convertServerTr').last();
					var cloneTr = tr.clone();
					var status = data.message;
					cloneTr.find('.serverIpSpan').html(serverIp);
					cloneTr.find('.statusSpan').html(status);
					MediaStreamingConvertServerSettings.setBtnAction(cloneTr, serverIp);
					tr.after(cloneTr);
					$('#newInp').val('');
				}
			});
		}
	},
	setBtnAction : function(tr, serverIp) {
		tr.find('.defaultBtn').attr('alt', serverIp).on('click', function() {
			MediaStreamingConvertServerSettings.setConvertServerDefault($(this));
		});
		tr.find('.delBtn').attr('alt', serverIp).on('click', function() {
			MediaStreamingConvertServerSettings.delConvertServer($(this));
		});
	},
	showLoading:function(){
		MediaStreamingConvertServerSettings.formObj().find('#loadingImg').show();
	},
	hideLoading:function(){
		MediaStreamingConvertServerSettings.formObj().find('#loadingImg').hide();
	},
};
