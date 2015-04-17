$(document).ready(function() {
	if ($('#userIdDiv') != undefined) {
		UserInfo.setDefault();
	}
});
var UserInfo = {
	appId : 'user_info',
	setDefault : function() {
		UserInfo.setupBtn();
	},
	setupBtn : function() {
		$('#userIdDiv').append('▼').on('click', UserInfo.show).css('cursor', 'pointer');
	},
	show : function() {
		$.fancybox.open({
			type : 'iframe',
			href : OC.filePath('user_info', '', 'user_info_handler.php'),
			width : 350,
			openEffect : 'none',
			closeEffect : 'none',
		});
	},
};
