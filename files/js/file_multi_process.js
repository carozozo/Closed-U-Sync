$(document).ready(function() {
	FileMultiProcess.setDefault();
});

var FileMultiProcess = {
	lastChecked : {},
	setDefault : function() {
		FileMultiProcess.setSelectAllAction();
		FileMultiProcess.setCheckboxAction();
	},
	setSelectAllAction : function() {
		$('#select_all').click(function() {
			if ($(this).prop('checked')) {
				FileMultiProcess.selectAllFiles();
			} else {
				FileMultiProcess.unselectAllFiles();
			}
		});
	},
	setCheckboxAction : function() {
		$(document).delegate('td.checkboxClass input:checkbox', 'click', function(event) {
			if (event.shiftKey) {
				FileMultiProcess.shiftSelection($('td.checkboxClass input:checkbox'), $(this));
			}
			if ($(this).prop('checked')) {
				FileMultiProcess.selectFile($(this));
				FileMultiProcess.lastChecked = $(this);
			} else {
				FileMultiProcess.unselectFile($(this));
			}
			FileMultiProcess.procesSelection();
		});
	},
	shiftSelection : function(objs, obj) {
		var first = objs.index(obj);
		var last = objs.index(FileMultiProcess.lastChecked);
		var start = Math.min(first, last);
		var end = Math.max(first, last);
		// alert('first=' + first + ',last=' + last)
		if (start > -1) {
			for (var i = start; i <= end; i++) {
				var tr = $('#fileList').find('tr').eq(i);
				var checkbox = tr.find('.checkboxClass input:checkbox');
				tr.addClass('selected');
				$(checkbox).prop('checked', true);
			}
		}
	},
	procesSelection : function() {
		var selected = FileMultiProcess.getSelectedFiles();
		var selectedFiles = selected.filter(function(el) {
			return el.type == 'file';
		});
		var selectedFolders = selected.filter(function(el) {
			return el.type == 'dir';
		});
		if (selectedFiles.length == 0 && selectedFolders.length == 0) {
			FileMultiProcess.hideSelectedActions();
		} else {
			FileMultiProcess.showSelectedActions(selectedFiles, selectedFolders);
		}
	},
	getSelectedFiles : function(property) {
		var elements = $('td.checkboxClass input:checkbox:checked').closest('tr');
		var files = [];
		elements.each(function(i, element) {
			var file = {
				name : $(element).attr('data-file'),
				mime : $(element).data('mime'),
				type : $(element).data('type'),
				size : $(element).data('size'),
				dataDate : $(element).attr('data-date'),
				readable : $(element).attr('data-read'),
				writeable : $(element).attr('data-write'),
			};
			//如果有指定要抓取的屬性
			if (property) {
				files.push(file[property]);
			} else {
				files.push(file);
			}
		});
		return files;
	},
	getSelectedWritableFiles : function(property) {
		var filesArray = FileMultiProcess.getSelectedFiles(property);
		var filesWriteableArray = FileMultiProcess.getSelectedFiles('writeable');
		filesArray = $.grep(filesArray, function(value, key) {
			return value = (filesWriteableArray[key] == 'true');
		});
		return filesArray;
	},
	getSelectedUnwritableFiles : function(property) {
		var filesArray = FileMultiProcess.getSelectedFiles(property);
		var filesWriteableArray = FileMultiProcess.getSelectedFiles('writeable');
		filesArray = $.grep(filesArray, function(value, key) {
			return value = (filesWriteableArray[key] == 'false');
		});
		return filesArray;
	},
	showSelectedActions : function(selectedFiles, selectedFolders) {
		//定位
		$('.selectedActions').css('margin-top', '3em');
		$('#fileListTable').css('padding-top', '3.5em');
		//將按鈕放到html
		FileMultiActions.appendActions(true, true);
		//執行，顯示之前的function
		$.each(FileMultiActions.extend.beforeShow, function(key, val) {
			if ( typeof (val) == 'function') {
				val();
			}
		});
		$('.selectedActions').show("fast", function() {
			$.each(FileMultiActions.extend.onShow, function(key, val) {
				//執行，顯示之後的function
				if ( typeof (val) == 'function') {
					val();
				}
			});
		});
		FileMultiProcess.showTotalCount(selectedFiles, selectedFolders);
		FileMultiProcess.showTotalSize(selectedFiles, selectedFolders);
		$('#modified').text('');
		// $('thead').addClass('fixed');
		// $('th').addClass('multiselect');
	},
	hideSelectedActions : function() {
		$('.selectedActions').hide();
		$('#headerName>span.name').text(t('files', 'Name'));
		$('#headerSize').text(t('files', 'Size'));
		$('#modified').text(t('files', 'Modified'));
		$('th').removeClass('multiselect');
		// $('thead').removeClass('fixed');
		$('#fileListTable').css('padding-top', '0');
	},
	showTotalSize : function(selectedFiles, selectedFolders) {
		var totalSize = 0;
		for (var i = 0; i < selectedFiles.length; i++) {
			totalSize += selectedFiles[i].size;
		};
		for (var i = 0; i < selectedFolders.length; i++) {
			totalSize += selectedFolders[i].size;
		};
		simpleSize = simpleFileSize(totalSize);
		$('#headerSize').text(simpleSize);
		$('#headerSize').attr('title', humanFileSize(totalSize));
	},
	showTotalCount : function(selectedFiles, selectedFolders) {
		var selection = '';
		if (selectedFolders.length > 0) {
			if (selectedFolders.length == 1) {
				selection += '1 ' + t('files', 'folder');
			} else {
				selection += selectedFolders.length + ' ' + t('files', 'folders');
			}
			if (selectedFiles.length > 0) {
				selection += ' & ';
			}
		}
		if (selectedFiles.length > 0) {
			if (selectedFiles.length == 1) {
				selection += '1 ' + t('files', 'file');
			} else {
				selection += selectedFiles.length + ' ' + t('files', 'files');
			}
		}
		$('#headerName>span.name').text(selection);
	},
	selectFile : function(obj) {
		var tr = obj.closest('tr');
		tr.find('td.checkboxClass input:checkbox').prop('checked', true);
		tr.addClass('selected');
		FileActions.hide();
		//FileMultiProcess.lastChecked = tr.find('td.checkboxClass input:checkbox:checked');
		var selectedCount = $('td.checkboxClass input:checkbox:checked').length;
		var allSelectCount = $('td.checkboxClass input:checkbox').length;
		if (selectedCount == allSelectCount) {
			$('#select_all').prop('checked', true);
		}
	},
	unselectFile : function(obj) {
		var tr = obj.closest('tr');
		tr.find('td.checkboxClass input:checkbox').prop('checked', false);
		tr.removeClass('selected');
		$('#select_all').prop('checked', false);
	},
	selectAllFiles : function() {
		$('td.checkboxClass input:checkbox').prop('checked', true);
		$('td.checkboxClass input:checkbox').parent().parent().addClass('selected');
		FileMultiProcess.procesSelection();
	},
	unselectAllFiles : function() {
		$('td.checkboxClass input:checkbox').prop('checked', false);
		$('td.checkboxClass input:checkbox').parent().parent().removeClass('selected');
		FileMultiProcess.procesSelection();
	},
};
