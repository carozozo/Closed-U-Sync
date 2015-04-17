$(document).ready(function() {
	Navigation.setDefault();
});

var Navigation = {
	setDefault : function() {
		if ($('#navigationClass_MySpace').length > 0) {
			// Navigation.setUpgradeSpaceBtn();
		}
	},
	setUpgradeSpaceBtn : function() {
		var linkButton = '<input type="button" id="upgradeSpaceBtn" value="升級"/>';
		$('#navigationClass_MySpace').append($(linkButton));
		$('#upgradeSpaceBtn').css('font-size', '0.8em');
		$('#upgradeSpaceBtn').on('click', function() {
			window.open('https://u-sync.com/home/%E7%AB%8B%E5%8D%B3%E8%B2%B7.html', '_blank');
		});
	}
};