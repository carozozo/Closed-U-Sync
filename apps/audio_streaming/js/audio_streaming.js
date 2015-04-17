$(document).ready(function() {
	AudioStreaming.setDefault();
});
var AudioStreaming = {
	appId : 'audio_streaming',
	audioTypeArrCache : null,
	audioTypeArr : function() {
		//如果已經有取得所有 audio type的mime type列表, 則直接回傳
		if (AudioStreaming.audioTypeArrCache) {
			return AudioStreaming.audioTypeArrCache;
		}
		//否則呼叫後端取得列表
		AudioStreaming.audioTypeArrCache = OC_Helper.audioTypeArr();
		return AudioStreaming.audioTypeArrCache;
	},
	setDefault : function() {
		if ( typeof FileActions !== 'undefined') {
			$.each(AudioStreaming.audioTypeArr(), function(key, val) {
				FileActions.register(val, t(AudioStreaming.appId, 'Streaming Play'), OC.imagePath(AudioStreaming.appId, 'audio_streaming_play.png'), AudioStreaming.streamingAction, 1);
			});
		}
	},
	streamingAction : function(fileName) {
		AudioStreaming.openPlayerWindow(fileName);
	},
	openPlayerWindow : function(fileName) {
		var message = t(AudioStreaming.appId, 'Audio Streaming') + '...';
		$('#notification').html(message).fadeIn();
		var dir = $('#dir').val();
		var source = AudioStreaming.getStreamingSource(dir, fileName);

		if (source) {
			var windowUrl = OC.linkTo(AudioStreaming.appId, 'index.php');
			// var paramDir = '?dir=' + dir;
			// var paramFileName = '&fileName=' + fileName;
			//下載播放模式(預留播放模式,方便以後擴展功能)
			var paramSource = '?source=' + source;
			var paramMode = '&mode=streaming';
			// windowUrl += paramDir + paramFileName + paramMode;
			windowUrl += paramSource + paramMode;
			AudioStreaming.popWindow(windowUrl);
		}
	},
	popWindow : function(windowUrl) {
		$('#notification').delay(3000).fadeOut();
		$.fancybox.open({
			type : 'iframe',
			href : windowUrl,
			autoDimensions : false,
			width : 400,
			minHeight : 200,
			helpers : {
				overlay : {
					//按下半透明底時不會關閉視窗
					closeClick : false,
				},
			},
		});
	},
	getStreamingSource : function(dir, fileName) {
		// dir = encodeURIComponent(dir);
		// fileName = encodeURIComponent(fileName);
		// 預設要播放的格式為空字串(後端自動搜尋可播放的輸出檔，如沒有，則進行hls播放)
		var deviceType = '';
		var source;
		$.ajax(OC.filePath(AudioStreaming.appId, 'ajax', 'audio_streaming.php'), {
			data : {
				action : 'getStreamingSource',
				dir : dir,
				fileName : fileName,
			},
			type : 'POST',
			async : false,
			success : function(data) {
				if (data.status == 'success') {
					source = data.message;
				} else {
					// 失敗時顯示錯誤訊息
					AudioStreaming.showMess('Streaming Error');
				}
			},
		});
		return source;
	},
	showMess : function(mess) {
		var message = t(MediaStreaming.appId, mess);
		$('#notification').html(message).fadeIn().delay(3000).fadeOut();
	},
};
