$(document).ready(function() {
	OC_Login.setDefault();
});
var OC_Login = {
	setDefault : function() {
		OC_Login.setDefaultAction();
	},
	setDefaultAction : function() {
		// 只予許輸入英數及特定符號
		$('#loginForm #user').alphanumeric({
			allow : "_@-.",
		});
	},
};
