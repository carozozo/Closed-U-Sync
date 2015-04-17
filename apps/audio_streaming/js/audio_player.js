$(document).ready(function() {
	AudioPlayer.setDefault();
});
var AudioPlayer = {
	setDefault : function() {
		var source = $('#source').val();
		var mode = $('#mode').val();
		var opt = {
			// 'type':'video',
			image : 'img/audio_player_bg.jpg',
			height : 200,
			width : 400,
			controlbar : "bottom",
			'file' : source,
			'skin' : 'img/jwplayer/jwplayer_audio_skin.xml',
		};

		if (mode == 'streaming') {
			AudioPlayer.setupPlayer(opt);
		}
	},
	setupPlayer : function(opt) {
		jwplayer.key = "Zpawz7g5Kb/Vwt9X6Ow4uqg9BCAmYTtNnz9laIVrSbk=";
		jwplayer('audioPlayerDiv').setup(opt).onError(function() {
			AudioPlayer.closePopWindow();
		});
	},
	closePopWindow : function() {
		var parentWindow = window.parent.$(window.parent.document);
		var closeBtn = parentWindow.find('.fancybox-close');
		closeBtn.click();
	},
};
