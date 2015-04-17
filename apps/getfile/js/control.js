$(document).ready(function() {
	GetFile.setDefault();
	GetFile.setSubmitGetFile();
});

var GetFile = {
	setDefault : function() {
		if (location.href.match(/\/files\/index\.php/)) {
			$('<div id="getfileI" class="button"><a href="#" onclick="return false;">&nbsp;' + t('getfile', 'Cloud Download') + '</a></div>').appendTo('div #controls .actions')
			$('#getfileI>a').click(function(event) {
				if ($('#getfileUI').length > 0) {
					$('#getfileUI').detach();
				} else
					GetFile.getfileCreateUi();
			});
		}
		$('#getfileI').click(function(event) {
			event.stopPropagation();
		});
		/*$('#getfileUI').click(function(event){
		 console.log("#getfileUI");
		 event.stopPropagation();
		 });*/
		$(window).click(function() {
			if ($('#getfileUI').length > 0)
				$('#getfileUI').detach();
		});
	},
	setSubmitGetFile : function() {
		$(document).on('submit', '#getfileSubmitForm', function() {
			//in [files/js/files.js]
			// popNotification(t('getfile', 'Downloading'), 5000);
			var message = t('files_mv', 'Moving to');
			$('#notification').html(t('getfile', 'Downloading')).fadeIn();
			var getfileSrc = $('#getfileUrl').val();
			var getfileName = $('#getfileName').val();
			if ($('#getfileUI').length > 0)
				$('#getfileUI').detach();
			setTimeout(function() {
				GetFile.getFile(getfileSrc, getfileName);
			}, 1000);
		});
	},
	getFile : function(getfileSrc, getfileName) {
		$.ajax({
			type : 'POST',
			url : OC.linkTo('getfile', 'ajax/load.php'),
			cache : false,
			data : {
				getfileSrc : getfileSrc,
				getfileName : getfileName,
				dir : $('#dir').val()
			},
			success : function(data) {
				$('#notification').delay(2000).fadeOut();
				if (data.status == "success") {
					FileList.addFile(data.name, data.size, new Date(), false, data.mime);
					$('tr').filterAttr('data-file', data.name).data('mime', data.mime);
					FileList.loadingDone(data.name);
				} else {
					$('<div id="getfileUI">' + data.message + '</div>').appendTo('#getfileI');
				}
			},
		});
	},
	getfileCreateUi : function() {
		var html = '<div id="getfileUI" class="popup popupTop">';
		html += '<form id="getfileSubmitForm" onsubmit="return false;">';
		html += '<input style="width:200px" type="text" name="getfileUrl" id="getfileUrl" value="http://" /><label for="getfileUrl">' + t('getfile', 'Source') + ' URL</label><br />';
		html += '<input type="text" name="getfileName" id="getfileName" value="" /><label for="getfileName">' + t('getfile', 'Insert full file name\(Required\)') + '</label><br />';
		html += '<button name="getfileSubmit" type="submit" id="getfileSubmit">' + t('getfile', 'Enter') + '</button></form>';
		html += '</div>';
		$(html).appendTo('div#getfileI');
	},
};
