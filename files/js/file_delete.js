$(document).ready(function() {
	//預寫不用
	// FileDelete.setHandler();
});

FileDelete = {
	setHandler : function() {
		$('#notification').on('click', FileDelete.cancle_delete);
		//畫面跳轉前，刪除檔案
		$(window).on('beforeunload', FileDelete.delete_before_unload_page);
	},
	cancle_delete : function() {
		if ($('#notification').data('deletefile')) {
			$.each(FileList.deleteFiles, function(index, file) {
				$('tr').filterAttr('data-file', file).show();
			});
			FileList.deleteCanceled = true;
			FileList.deleteFiles = null;
		}
		$('#notification').fadeOut();
	},
	delete_before_unload_page : function() {
		FileDelete.finishDelete(null, true);
	},
	do_delete : function(files) {
		//如果有放在刪除排程的，則正式刪除檔案
		// if (FileList.deleteFiles) {//finish any ongoing deletes first
		// FileDelete.finishDelete(function() {
		// FileDelete.do_delete(files);
		// });
		// return;
		// }

		//如果files是可以被substr的
		if (files.substr) {
			//將fiels轉成陣列物件
			files = [files];
		}
		$.each(files, function(index, file) {
			var files = $('tr').filterAttr('data-file', file);
			files.hide();
			files.find('input[type="checkbox"]').removeAttr('checked');
			files.removeClass('selected');
		});
		FileMultiProcess.procesSelection();
		FileList.deleteCanceled = false;
		FileList.deleteFiles = files;
		//資料回復按鈕
		// $('#notification').text(t('files', 'Restore files'));
		// $('#notification').data('deletefile', true);
		// $('#notification').fadeIn();
		//正式刪除檔案
		FileDelete.finishDelete(null, true);
	},
	finishDelete : function(ready, sync) {
		if (!FileList.deleteCanceled && FileList.deleteFiles) {
			var fileNames = FileList.deleteFiles.join(';');
			/*刪除縮圖,預寫不用,已改用hooks
			Thumbnail.deleteThumbnail($("#dir").val(), fileNames);*/
			//刪除檔案
			$.ajax({
				url : 'ajax/delete.php',
				async : !sync,
				data : "dir=" + encodeURIComponent($('#dir').val()) + "&files=" + encodeURIComponent(fileNames),
				complete : function(data) {
					boolOperationFinished(data, function() {
						$('#notification').fadeOut();
						$.each(FileList.deleteFiles, function(index, file) {
							//將檔案從列表中移除
							FileList.remove(file);
						});
						FileList.deleteCanceled = true;
						FileList.deleteFiles = null;
						if (ready) {
							ready();
						}
					});
				}
			});
		}
	},
}

