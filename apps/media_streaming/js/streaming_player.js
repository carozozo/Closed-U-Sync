$(document).ready(function() {
	StreamingPlayer.setDefault();
});
var StreamingPlayer = {
	setDefault : function() {
		var title = $('#title').val();
		var source = $('#source').val();
		var mode = $('#mode').val();
		var opt = {
			// 'type':'video',
			// 'type' : '',
			'title' : title,
			'width' : '800',
			'height' : '600',
			'file' : source,
			// 'type' : 'm3u8',
		};
		if (mode == 'preview') {
			opt['skin'] = 'img/jwplayer/jwplayer_m3u8_skin.xml';
			StreamingPlayer.checkM3u8Exists(source, opt, 10);
		} else {
			StreamingPlayer.setupPlayer(opt);
		}
	},
	checkM3u8Exists : function(hlsUrl, opt) {
		$.ajax(OC.filePath(MediaStreaming.appId, 'ajax', 'media_streaming.php'), {
			data : {
				action : 'checkM3u8Exists',
				hlsUrl : hlsUrl,
			},
			type : 'POST',
			success : function(data) {
				if (data.status == 'success') {
					if (data.message == '1') {
						StreamingPlayer.setupPlayer(opt);
					} else {
						$('#loadingDiv').html(t(MediaStreaming.appId, 'File can not preview'));
						StreamingPlayer.updateM3u8Failed(hlsUrl);
						setTimeout(function() {
							StreamingPlayer.closePopWindow();
						}, 2000);
					}
				} else {
					$('#loadingDiv').html(t(MediaStreaming.appId, 'Can not get server respond'));
					setTimeout(function() {
						StreamingPlayer.closePopWindow();
					}, 2000);
				}
			},
		});
	},
	setupPlayer : function(opt) {
		jwplayer.key = "Zpawz7g5Kb/Vwt9X6Ow4uqg9BCAmYTtNnz9laIVrSbk=";
		jwplayer('mediaPlayerDiv').setup(opt).onError(function() {
			StreamingPlayer.closePopWindow();
		});
	},
	closePopWindow : function() {
		var parentWindow = window.parent.$(window.parent.document);
		var closeBtn = parentWindow.find('.fancybox-close');
		closeBtn.click();
	},
};
