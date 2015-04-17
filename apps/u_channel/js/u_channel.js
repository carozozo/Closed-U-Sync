$(document).ready(function() {
	U_Channel.setDefault();
});
var U_Channel = {
	appId : 'u_channel',
	setDefault : function() {
		U_Channel.setupListAction();
		U_Channel.setupPlayer();
	},
	setupPlayer : function(source) {
		var img = OC.imagePath(U_Channel.appId, U_Channel.appId);
		img = 'img/u_channel.png';
		var opt = {
			// 'type' : 'video',
			'width' : '800',
			'height' : '600',
			'autostart' : 'true',
			'file' : 'http://vod.u-sync.com/hls/live.m3u8',
			// 'img' : img,
			'type' : 'm3u8',
		};
		if (source == undefined) {
			source = $('.channelSpan:first').attr('alt');
		}
		opt['file'] = source;
		jwplayer.key = "Zpawz7g5Kb/Vwt9X6Ow4uqg9BCAmYTtNnz9laIVrSbk=";
		jwplayer('uChannelPlayerDiv').setup(opt).onError(function() {
			// StreamingPlayer.closePopWindow();
		});
	},
	setupListAction : function() {
		$('#uChannelListTable td').on('click', function() {
			var source = $(this).find('.channelSpan').attr('alt');
			U_Channel.setupPlayer(source);
		});
	}
};
