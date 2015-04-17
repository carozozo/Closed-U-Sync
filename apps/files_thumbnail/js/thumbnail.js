$(document).ready(function() {
	Thumbnail.setDefault();
});

Thumbnail = {
	ifGetThumb : true,
	noThumbArray : new Array(),
	noThumbArrayIndex : 0,
	setDefault : function() {
		//如果是在File List頁面
		if ($('#fileList').length > 0) {
			var needThumb = false;
			$('#fileList tr').each(function() {
				var mime = $(this).attr('data-mime');
				if (Thumbnail.ifImage(mime) || Thumbnail.ifVideo(mime)) {
					needThumb = true;
				}
			});
			if (needThumb) {
				Thumbnail.getThumbsInDir();
				// Thumbnail.setControlButton();
			}
			//上傳後，重新抓取縮圖
			FileList.extend.loadingDoneAfter['getThumbAfterUploadDone'] = Thumbnail.getThumbsAfterUpload;
		}
	},
	getThumbsAfterUpload : function(trObj) {
		//延遲1秒後再抓取縮圖(必免縮圖還未產生完畢)
		// setTimeout(Thumbnail.setDefault,1000);
		var file = new Array();
		file[0] = trObj.attr('data-file');
		file[1] = trObj.attr('data-mime');
		file[2] = trObj.attr('data-size');
		file[3] = trObj.attr('data-date');
		setTimeout(function() {
			Thumbnail.createAndGetThumbByFile(file);
		}, 1000);

	},
	getThumbsInDir : function() {
		// Thumbnail.showLoading();
		var dir = $('#dir').val();
		$.post(OC.filePath('files_thumbnail', 'ajax', 'thumbnail.php'), {
			action : "getThumbsInDir",
			dir : dir,
		}, function(data) {
			// setTimeout(Thumbnail.hideLoading, 500);
			if (data.status == 'success') {
				$.each(data.thumbUrlArray, function(key, val) {
					var name = val.name;
					var url = val.url;
					var tr = $('tr').filterAttr('data-file', name);
					var img = tr.find('.fileImg');
					//將原本的小圖路徑和縮圖路徑寫到圖片中
					img.data('originSrc', img.attr('src')).data('thumbSrc', url).attr('src', url);
					// alert('name='+name+', url='+img.attr('src'))
				});
				Thumbnail.createThumbs();
			}
		});
	},
	createThumbs : function() {
		if (Thumbnail.ifGetThumb) {
			Thumbnail.noThumbArray = new Array();
			Thumbnail.noThumbArrayIndex = 0;
			$('#fileList tr').each(function() {
				var type = $(this).attr('data-type');
				var mime = $(this).attr('data-mime');
				if (type == 'file' && (Thumbnail.ifImage(mime) || Thumbnail.ifVideo(mime))) {
					var img = $(this).find('.fileImg');
					//圖片中沒有縮圖路徑資料
					if (!img.data('thumbSrc')) {
						var file = new Array();
						file[0] = $(this).attr('data-file');
						file[1] = mime;
						file[2] = $(this).attr('data-size');
						file[3] = $(this).attr('data-date');
						//存放沒有縮圖的檔案
						Thumbnail.noThumbArray.push(file);
					}
				}
			});
			if (Thumbnail.noThumbArray.length > 0) {
				// Thumbnail.showLoading();
				//取得縮圖
				Thumbnail.createAndGetThumbByFile(Thumbnail.noThumbArray[Thumbnail.noThumbArrayIndex]);
				// Thumbnail.createAndGetThumbByFileArray(Thumbnail.noThumbArray);
			}
		}
	},
	createAndGetThumbByFile : function(file) {
		if (Thumbnail.ifGetThumb) {
			var name = file[0];
			var dir = $('#dir').val();
			var tr = $('tr').filterAttr('data-file', name);
			var img = tr.find('.fileImg');
			$.post(OC.filePath('files_thumbnail', 'ajax', 'thumbnail.php'), {
				action : "createAndGetThumbByFile",
				dir : dir,
				file : file,
			}, function(data) {
				if (data.status == 'success') {
					if (data.thumbUrl) {
						var url = data.thumbUrl.url;
						//將原本的小圖路徑和縮圖路徑寫到圖片中
						img.data('originSrc', img.attr('src')).data('thumbSrc', url).attr('src', url);
						//產生下一張沒有縮圖的檔案
						Thumbnail.noThumbArrayIndex++;
						if (Thumbnail.noThumbArrayIndex < Thumbnail.noThumbArray.length) {
							Thumbnail.createAndGetThumbByFile(Thumbnail.noThumbArray[Thumbnail.noThumbArrayIndex]);
						} else {
							// Thumbnail.hideLoading();
						}
					}
				}
			});
		}
	},
	//預寫不用
	createAndGetThumbByFileArray : function(fileArray) {
		Thumbnail.showLoading();
		var dir = $('#dir').val();
		$.post(OC.filePath('files_thumbnail', 'ajax', 'thumbnail.php'), {
			action : "createAndGetThumbByFileArray",
			dir : dir,
			fileArray : fileArray,
		}, function(data) {
			if (data.status == 'success') {
				if (data.thumbUrlArray) {
					$.each(data.thumbUrlArray, function(key, thumbUrl) {
						var name = thumbUrl.name;
						var url = thumbUrl.url;
						var tr = $('tr').filterAttr('data-file', name);
						var img = tr.find('.fileImg');
						img.data('originSrc', img.attr('src')).attr('src', url);
						Thumbnail.hideLoading();
					});
				}
			}
		});
	},
	//預寫不用
	showLoading : function() {
		$.fancybox.open('<div id="thumbLoadingDiv" align="center"></div>', {
			autoSize : false,
			width : 300,
			minHeight : 30,
			height : 60,
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
		$('#thumbLoadingDiv').css('text-align', 'center').css('font-weight', 'bolder').css('font-size', '1.5em').html(t('files_thumbnail', 'Creating Thumbs') + '...<input id="stopThumbnailBtn" type="button" value="Stop"/>');
		$('#stopThumbnailBtn').click(function() {
			Thumbnail.ifGetThumb = false;
			// Thumbnail.getThumbsInDir();
			Thumbnail.hideLoading();
		});
	},
	//預寫不用
	hideLoading : function() {
		$.fancybox.close();
	},
	ifImage : function(mime) {
		if (mime.indexOf('image') >= 0)
			return true;
		return false;
	},
	ifVideo : function(mime) {
		if (mime.indexOf('video') >= 0 || mime.indexOf('realmedia') >= 0)
			return true;
		return false;
	},
};
