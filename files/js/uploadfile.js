$(document).ready(function() {
	UploadFile.setClickUpload();
	$(document).on('change', '.file_upload_start', UploadFile.startUploadFile);
	$(document).on('click', '.file_cancel', UploadFile.cancelUpload);
	// 點選「顯示用的上傳按鈕」時，則執行內部真正的上傳按鈕(透明度為0)

})
var UploadFile = {
	setClickUpload : function() {
		$(document).on('click', '.file_upload_button_wrapper', function() {
			$(this).siblings('.file_upload_start').trigger('click');
			return false;
		});
	},
	startUploadFile : function() {
		var form = $(this).closest('form');
		var files = this.files;
		var progress_key = form.find('#progress_key');
		var target = form.children('iframe');

		//如果上傳的檔案超過上傳上限
		if (!UploadFile.checkUploadSize(files)) {
			return false;
		}

		//上傳序號帶入亂數
		progress_key.val(Math.random());
		form.submit();
		UploadFile.getUploadRespond(target);
		//取得上傳進度
		UploadFile.startGetProgress();
		UploadFile.addFilesInList(files);
		UploadFile.cloneUploadForm(form);
	},
	checkUploadSize : function(files) {
		var totalSize = 0;
		if (files) {
			for (var i = 0; i < files.length; i++) {
				totalSize += files[i].size;
			}
		}

		if (totalSize > $('#max_upload').val()) {
			$("#uploadsize-message").dialog({
				modal : true,
				buttons : {
					Close : function() {
						$(this).dialog("close");
					}
				}
			});
			return false;
		}
		return true;
	},
	getUploadRespond : function(target) {
		//IE8或更早的版本不支援
		target.load(function() {
			if (target.contents().find('body')) {
				var response = $.parseJSON(target.contents().find('body').text());
				//set mimetype and if needed filesize
				if (response) {
					if (response[0] != undefined && response[0].status == 'success') {
						for (var i = 0; i < response.length; i++) {
							var file = response[i];
							var size = simpleFileSize(file.size);
							var humanSize = humanFileSize(file.size);
							var targetTr = $('tr').filterAttr('data-file', file.name);
							targetTr.data('mime', file.mime);
							targetTr.find('td.filesize').text(size).attr('title', humanSize).tipsy({
								gravity : 's',
								fade : true,
								live : true
							});
							FileList.loadingDone(file.name);
						}
					} else {
						//取消或發生錯誤時回報訊息
						$('#notification').text(t('files', response.data.message));
						$('#notification').fadeIn();
						$('#fileList tr').each(function() {
							if ($(this).data("loading")) {
								$(this).remove();
							}
						});
					}
				}
			}
		});
	},
	addFilesInList : function(files) {
		var date = new Date();
		var size = t('files', 'Pending');
		if (files) {
			for (var i = 0; i < files.length; i++) {
				size = files[i].size;
				FileList.addFile(files[i].name, size, date, true);
			}
		} else {
			//如果是IE的話，只能取得檔案名稱
			//ie prepends C:\fakepath\ in front of the filename
			var filename = this.value.split('\\').pop();
			FileList.addFile(filename, size, date, true);
		}
	},
	cloneUploadForm : function(form) {
		//clone the upload form and hide the new one to allow users to start a new upload while the old one is still uploading
		var uploadId = form.attr('data-upload-id');
		var clone = form.clone();
		uploadId++;
		clone.attr('data-upload-id', uploadId);
		clone.attr('target', 'file_upload_target_' + uploadId);
		clone.children('iframe').attr('name', 'file_upload_target_' + uploadId)
		clone.insertBefore(form);
		form.hide();
	},
	startGetProgress : function() {
		var cancelBtn = '<input type="button" class="file_cancel" value="' + t('files', 'Cancel') + '" />';
		$.fancybox.open('<div id="uploadDiv"><span id="uploadSpan"></span>' + cancelBtn + '</div>', {
			autoSize : false,
			width : 250,
			minHeight : 40,
			height : 40,
			closeBtn : false,
			helpers : {
				overlay : {
					//按下半透明底時不會關閉視窗
					closeClick : false,
				},
			},
			keys : {
				//按下Esc的時候不會關閉視窗
				close : false,
			},
		});
		$('#uploadDiv').css('text-align', 'center');
		$('#uploadSpan').css('font-weight', 'bolder').css('font-size', '1.5em');

		//秀上傳進度的畫面
		UploadFile.getprogress();
		interval = setInterval(function() {
			UploadFile.getprogress();
		}, 1000);
	},
	getprogress : function() {
		$.post(OC.filePath('files', 'ajax', 'getprogress.php'), {
			progress_key : $('#progress_key').val()
		}, function(res) {
			if (res < 100) {
				uploadingVal = t('files', 'Uploading') + ".." + res + "%";
				$("#uploadSpan").html(uploadingVal);
			} else {
				clearInterval(interval);
				$.fancybox.close();
			}
		});
	},
	cancelUpload : function() {
		//清空上傳頁面
		$(".file_upload_target").attr("src", "");
		//$(".file_upload_target").load("");
		$(".file_cancel_form").submit();
		clearInterval(interval);
		$.fancybox.close();
		//window.location.reload();
	},
};
