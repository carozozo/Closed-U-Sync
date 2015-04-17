$(document).ready(function() {
	MediaConvert.setDefault();
	MediaConvert.checkEmptyFolder();
});

var MediaConvert = {
	setDefault : function() {
		if ( typeof FileActions !== 'undefined') {
			$.each(MediaStreaming.mediaTypeArr(), function(key, val) {
				FileActions.register(val, t(MediaStreaming.appId, 'Convert'), OC.imagePath(MediaStreaming.appId, 'media_convert.png'), MediaConvert.addConvertJob, 9);
			});
		}
		MediaConvert.setDefaultAction();
	},
	setDefaultAction:function(){
		$(document).on('click', function(event) {
			var tar = $(event.target);
			if (!(tar.hasClass('drop')) && tar.parents().index($('#convertJobDropdown')) == -1) {
				if ($('#convertJobDropdown').is(':visible')) {
					MediaConvert.removeDropdown();
				}
			}
		});
	},
	addConvertJob : function(filename) {
		if ($('#convertJobDropdown').length > 0) {
			MediaConvert.removeDropdown();
		} else {
			MediaConvert.createConvertJobDropdown(filename);
		}
	},
	removeDropdown : function() {
		$('#convertJobDropdown').hide('blind', function() {
			//var file = $('#dir').val() + '/' + filename;
			$('#convertJobDropdown').remove();
			$('tr').removeClass('mouseOver');
		});
	},
	createConvertJobDropdown : function(filename) {
		// var loadingPath = OC.imagePath('core', 'loading.gif');
		var html = '<div id="convertJobDropdown" class="drop" data-file="' + filename + '">';
		html += '<select style="width:220px;" id="deviceType">';
		html += '<option value="phone">' + t(MediaStreaming.appId, 'Output the phone size') + '</option>';
		html += '<option value="pad">' + t(MediaStreaming.appId, 'Output the pad size') + '</option>';
		html += '<option value="tv">' + t(MediaStreaming.appId, 'Output the TV size') + '</option>';
		html += '</select>';
		html += '<input type="button" id="covertJobButton" value="' + t(MediaStreaming.appId, 'Enter') + '"/>';
		// html += '<img id="loadingImg" src="' + loadingPath + '">';
		html += '<br/><span id="mediaConverterMessage"></span>';
		html += '</div>';
		$('tr').filterAttr('data-file', filename).addClass('mouseOver');
		$(html).appendTo($('tr').filterAttr('data-file', filename).find('td.filename'));
		MediaConvert.setConvertAction();
		$('#loadingImg').hide();
		$('#convertJobDropdown').show('blind');
		MediaConvert.showConvertTimesMess();
	},
	setConvertAction:function(){
		$("#covertJobButton").on('click', function() {
			MediaConvert.convertMedia();
		});
		$('#deviceType').on('change',function(){
			$("#covertJobButton").show();
		});
	},
	showConvertTimesMess : function() {
		// var ifCanCover = false;
		$.ajax({
			type : 'POST',
			url : OC.linkTo(MediaStreaming.appId, 'ajax/media_convert.php'),
			data : {
				action : 'showConvertTimesMess',
			},
			async : false,
			success : function(data) {
				if (data.status == "success") {
					var messArr = data.messArr;
					var mess ='';
					$.each(messArr, function(index,val) {
						mess += val+'<br/>';
					});
					$("#mediaConverterMessage").html(mess);
				}
			}
		});
	},
	convertMedia : function() {
		MediaConvert.showLoadingMessage();
		var dir = $('#dir').val();
		var fileName = $('#convertJobDropdown').data('file');
		// alert('dir='+dir+', fileName='+fileName)
		var deviceType = $("#deviceType").val();
		$.ajax({
			type : 'POST',
			url : OC.linkTo(MediaStreaming.appId, 'ajax/media_convert.php'),
			data : {
				action : 'convertMedia',
				dir : dir,
				fileName : fileName,
				deviceType : deviceType,
			},
			success : function(data) {
				var message = data.message;
				$("#mediaConverterMessage").html(t(MediaStreaming.appId, message));
			}
		});

	},
	showLoadingMessage : function() {
		$("#covertJobButton").hide();
		var loadingPath = OC.imagePath('core', 'loading.gif');
		var loadginImg = '<img id="loadingImg" src="' + loadingPath + '">';
		var message = t(MediaStreaming.appId, 'Sending Data') + '...' + loadginImg;
		$('#mediaConverterMessage').html(message);
	},
	// 如果是在轉檔資料夾底下，而且裡面沒有檔案，則秀訊息
	checkEmptyFolder : function() {
		if (MediaConvert.ifUnderOutputFolder() && $('#emptyfolder')) {
			$('#emptyfolder').html(t(MediaStreaming.appId, 'Through the format wizard completes video files will be placed here'));
		}
	},
	ifUnderOutputFolder : function() {
		var dir = $('#dir').val();
		var ougputFolder = OC_Appconfig.getValue('media_streaming','convertDir', '/MediaWizard');
		if (dir == ougputFolder) {
			return true;
		}
		return false;
	},
};
