$(document).ready(function() {
	MediaStreaming.setDefault();
});
var MediaStreaming = {
	appId : 'media_streaming',
	mediaTypeArrCache : null,
	mediaTypeArr : function() {
		// 如果已經有取得所有 media type的mime type列表, 則直接回傳
		if (MediaStreaming.mediaTypeArrCache) {
			return MediaStreaming.mediaTypeArrCache;
		}
		// 否則呼叫後端取得列表
		MediaStreaming.mediaTypeArrCache = OC_Helper.mediaTypeArr();
		return MediaStreaming.mediaTypeArrCache;
	},
	useHlsCache : null,
	useHls : function() {
		// 如果已經有取得useHls
		if (MediaStreaming.useHlsCache) {
			return MediaStreaming.useHlsCache;
		}
		// 否則呼叫後端資料
		MediaStreaming.useHlsCache = OC_Appconfig.getValue(MediaStreaming.appId, 'useHls', 0);
		return MediaStreaming.useHlsCache;
	},
	setDefault : function() {
		if ( typeof FileActions !== 'undefined') {
			if (MediaStreaming.useHls() == 1) {
				//有開啟hls,執行streaming 2.0版
				$.each(MediaStreaming.mediaTypeArr(), function(key, val) {
					FileActions.register(val, t(MediaStreaming.appId, 'Streaming Play') + '2.0', OC.imagePath(MediaStreaming.appId, 'media_streaming_play.png'), MediaStreaming.streamingAction, 1);
				});
			} else {
				//for 1.5/1.6版，只能播放mp4檔案
				FileActions.register('video/mp4', t(MediaStreaming.appId, 'Streaming Play') + '1.6', OC.imagePath(MediaStreaming.appId, 'media_streaming_play.png'), MediaStreaming.streamingAction, 1);
			}
		}
	},
	streamingAction : function(fileName) {
		MediaStreaming.openPlayerWindow(fileName);
	},
	openPlayerWindow : function(fileName) {
		var message = t(MediaStreaming.appId, 'Media Streaming') + '...';
		$('#notification').html(message).fadeIn();
		var dir = $('#dir').val();
		var source = MediaStreaming.getStreamingSource(dir, fileName);
		if (source) {
			var hostName = window.location.hostname;
			var paramTitle = '&title=';
			var paramMode = '&mode=';
			// var windowUrl = 'https://' + hostName + '/apps/media_streaming/index.php?source=' + source;
			var windowUrl = OC.linkTo(MediaStreaming.appId, 'index.php?source=');
			if (MediaStreaming.checkSourceIfMp4(source)) {
				// 下載播放模式
				paramMode += 'streaming';
				windowUrl += source + paramMode + paramTitle;
			} else {
				// 轉檔預覽播放模式
				source = MediaStreaming.changeProtocol(source);
				paramMode += 'preview';
				paramTitle += t(MediaStreaming.appId, 'Media Preview Mode');
				windowUrl += source + paramMode + paramTitle;
				// source = source.replace(/http/g, "https");
			}

			MediaStreaming.popWindow(windowUrl);
		}
	},
	popWindow : function(windowUrl) {
		$('#notification').delay(3000).fadeOut();
		// console.log('windowUrl', windowUrl);
		$.fancybox.open({
			type : 'iframe',
			href : windowUrl,
			helpers : {
				overlay : {
					// 按下半透明底時不會關閉視窗
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
		$.ajax(OC.filePath(MediaStreaming.appId, 'ajax', 'media_streaming.php'), {
			data : {
				action : 'getStreamingSource',
				dir : dir,
				fileName : fileName,
				deviceType : deviceType,
			},
			type : 'POST',
			async : false,
			success : function(data) {
				// alert('status=' + data.status)
				if (data.status == 'success') {
					source = data.message;
				} else {
					// 失敗時顯示錯誤訊息
					MediaStreaming.showMess(data.message);
				}
			},
		});
		return source;
	},
	checkSourceIfMp4 : function(source) {
		if (source && source.indexOf('.mp4') > -1) {
			return true;
		}
		return false;
	},
	changeProtocol : function(url) {
		if (location.protocol == 'https:' && url.indexOf('https') != 0) {
			url = url.replace('http', 'https');
		}
		return url;
	},
	showMess : function(mess) {
		// TODO 後端回傳的訊息改為英文，所以前端要翻譯，之後再改為由後端直接回翻譯的訊息
		var message = t(MediaStreaming.appId, mess);
		if (mess == 'Source Size Over Limit') {
			var limitSize = OC_Appconfig.getValue(MediaStreaming.appId, 'limitSize', 0);
			limitSize = humanFileSize(limitSize);
			message += ':' + limitSize + '<br/>';
			message += t(MediaStreaming.appId, 'You can play it by MediaWizard') + ', ';
			message += t(MediaStreaming.appId, 'or upgrade to paied user');
		}
		$('#notification').html(message).fadeIn().delay(30000).fadeOut();
	},
};
