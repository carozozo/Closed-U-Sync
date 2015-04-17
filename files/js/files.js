$(document).ready(function() {
	$('#fileList tr').each(function() {
		//little hack to set unescape filenames in attribute
		var tr = $(this);
		var fileName = decodeURIComponent($(this).attr('data-file'));
		tr.attr('data-file', fileName);
	});

	// if ($('tr[data-file]').length == 0) {
	// $('.file_upload_filename').addClass('highlight');
	// }

	$('#file_action_panel').attr('activeAction', false);

	//drag/drop of files
	$('#fileList tr td.filename').draggable(dragOptions);
	$('#fileList tr[data-type="dir"][data-write="true"] td.filename').droppable(folderDropOptions);
	$('div.crumb').droppable(crumbDropOptions);
	$('ul#apps>li:first-child').data('dir', '');
	$('ul#apps>li:first-child').droppable(crumbDropOptions);

	// Sets the file link behaviour :
	$(document).on('click', 'td.filename a', function(event) {
		event.preventDefault();
		if (event.shiftKey) {
			var checkbox = $(this).closest('tr').find('input:checkbox');
			//如果點下去的時候，檔案沒被選取，則選取
			if (!$(checkbox).prop('checked')) {
				FileMultiProcess.selectFile($(this));
			}
			FileMultiProcess.shiftSelection($('td.filename a'), $(this));
			FileMultiProcess.lastChecked = $(this);
			FileMultiProcess.procesSelection();
		} else if (event.ctrlKey) {
			FileMultiProcess.lastChecked = {};
			var checkbox = $(this).closest('tr').find('input:checkbox');
			//如果點下去的時候，檔案是選取的，則反選
			if ($(checkbox).prop('checked')) {
				FileMultiProcess.unselectFile($(this));
			} else {
				FileMultiProcess.selectFile($(this));
			}
			FileMultiProcess.procesSelection();
		} else {
			var filename = $(this).parent().parent().attr('data-file');
			var tr = $('tr').filterAttr('data-file', filename);
			var renaming = tr.data('renaming');
			if (!renaming && !FileList.isLoading(filename)) {
				var mime = $(this).parent().parent().data('mime');
				var type = $(this).parent().parent().data('type');
				var action = FileActions.getDefault(mime, type);
				if (action) {
					action(filename);
				}
			}
		}
	});

	//add multiply file upload attribute to all browsers except konqueror (which crashes when it's used)
	if (navigator.userAgent.search(/konqueror/i) == -1) {
		$('.file_upload_start').attr('multiple', 'multiple');
	}

	//if the breadcrumb is to long, start by replacing foldernames with '...' except for the current folder
	var crumb = $('div.crumb').first();
	while ($('div.controls').height() > 40 && crumb.next('div.crumb').length > 0) {
		crumb.children('a').text('...');
		crumb = crumb.next('div.crumb');
	}
	//if that isn't enough, start removing items from the breacrumb except for the current folder and it's parent
	var crumb = $('div.crumb').first();
	var next = crumb.next('div.crumb');
	while ($('div.controls').height() > 40 && next.next('div.crumb').length > 0) {
		crumb.remove();
		crumb = next;
		next = crumb.next('div.crumb');
	}
	//still not enough, start shorting down the current folder name
	var crumb = $('div.crumb>a').last();
	while ($('div.controls').height() > 40 && crumb.text().length > 6) {
		var text = crumb.text();
		text = text.substr(0, text.length - 6) + '...';
		crumb.text(text);
	}

	$(window).click(function() {
		$('#new>ul').hide();
		$('#new').removeClass('active');
		$('button.file_upload_filename').removeClass('active');
		$('#new li').each(function(i, element) {
			if ($(element).children('p:visible').length == 0) {
				$(element).children('input').remove();
				$(element).children('p').show();
			}
		});
	});
	$('#new').click(function(event) {
		event.stopPropagation();
	});
	$('#new>a').click(function() {
		$('#new>ul').toggle();
		$('#new').toggleClass('active');
		$('button.file_upload_filename').toggleClass('active');
	});
	$('#new li').click(function() {
		if ($(this).children('p:visible').length == 0) {
			return;
		}

		$('#new li').each(function(i, element) {
			if ($(element).children('p:visible').length == 0) {
				$(element).children('input').remove();
				$(element).children('p').show();
			}
		});

		var type = $(this).data('type');
		var text = $(this).children('p').text();
		$(this).data('text', text);
		$(this).children('p').hide();
		var input = $('<input>');
		$(this).append(input);
		input.focus();
		input.change(function() {
			var name = $(this).val().trim();
			switch(type) {
				case 'text-file':
					name = name + '.txt';
					$.ajax({
						url : OC.filePath('files', 'ajax', 'newfile.php'),
						data : "dir=" + encodeURIComponent($('#dir').val()) + "&filename=" + encodeURIComponent(name) + '&content=%20%0A',
						complete : function(data) {
							boolOperationFinished(data, function() {
								var date = new Date();
								FileList.addTextFile(name, date);
							});
						}
					});
					break;
				case 'file':
					$.ajax({
						url : OC.filePath('files', 'ajax', 'newfile.php'),
						data : "dir=" + encodeURIComponent($('#dir').val()) + "&filename=" + encodeURIComponent(name) + '&content=%20%0A',
						complete : function(data) {
							boolOperationFinished(data, function() {
								var date = new Date();
								FileList.addFile(name, 0, date);
								var tr = $('tr').filterAttr('data-file', name);
								tr.data('mime', 'text/plain');
							});
						}
					});
					break;
				case 'folder':
					$.ajax({
						url : OC.filePath('files', 'ajax', 'newfolder.php'),
						data : "dir=" + encodeURIComponent($('#dir').val()) + "&foldername=" + encodeURIComponent(name),
						complete : function(data) {
							boolOperationFinished(data, function() {
								var date = new Date();
								FileList.addDir(name, 0, date);
							});
						}
					});
					break;
			}
			var li = $(this).parent();
			$(this).remove();
			li.children('p').show();
			$('#new>a').click();
		});
	});
});

function boolOperationFinished(data, callback) {
	result = jQuery.parseJSON(data.responseText);
	if (result.status == 'success') {
		callback.call();
	} else {
		alert(result.data.message);
	}
}

function updateBreadcrumb(breadcrumbHtml) {
	$('p.nav').empty().html(breadcrumbHtml);
}

function humanFileSize(bytes) {
	if (bytes < 1024) {
		return bytes + ' B';
	}
	bytes = Math.round(bytes / 1024, 1);
	if (bytes < 1024) {
		return bytes + ' kB';
	}
	bytes = Math.round(bytes / 1024, 1);
	if (bytes < 1024) {
		return bytes + ' MB';
	}

	// Wow, heavy duty for owncloud
	bytes = Math.round(bytes / 1024, 1);
	return bytes + ' GB';
}

function simpleFileSize(bytes) {
	mbytes = Math.round(bytes / (1024 * 1024 / 10)) / 10;
	if (bytes == 0) {
		return '0';
	} else if (mbytes < 0.1) {
		return '< 0.1';
	} else if (mbytes > 1000) {
		return '> 1000';
	} else {
		return mbytes.toFixed(1);
	}
}

function formatDate(date) {
	var monthNames = [t('files', 'January'), t('files', 'February'), t('files', 'March'), t('files', 'April'), t('files', 'May'), t('files', 'June'), t('files', 'July'), t('files', 'August'), t('files', 'September'), t('files', 'October'), t('files', 'November'), t('files', 'December')];
	return monthNames[date.getMonth()] + ' ' + date.getDate() + ', ' + date.getFullYear() + ', ' + ((date.getHours() < 10) ? '0' : '') + date.getHours() + ':' + date.getMinutes();
}

//options for file drag/dropp
var dragOptions = {
	distance : 20,
	revert : 'invalid',
	opacity : 0.7,
	stop : function(event, ui) {
		$('#fileList tr td.filename').addClass('ui-draggable');
	}
};
var folderDropOptions = {
	drop : function(event, ui) {
		var file = ui.draggable.text().trim();
		var target = $(this).text().trim();
		var dir = $('#dir').val();
		$.ajax({
			url : 'ajax/move.php',
			data : "dir=" + encodeURIComponent(dir) + "&file=" + encodeURIComponent(file) + '&target=' + encodeURIComponent(dir) + '/' + encodeURIComponent(target),
			complete : function(data) {
				boolOperationFinished(data, function() {
					var el = $('#fileList tr').filterAttr('data-file', file).find('td.filename');
					el.draggable('destroy');
					FileList.remove(file);
				});
			}
		});
	}
};
var crumbDropOptions = {
	drop : function(event, ui) {
		var file = ui.draggable.text().trim();
		var target = $(this).data('dir');
		var dir = $('#dir').val();
		while (dir.substr(0, 1) == '/') {//remove extra leading /'s
			dir = dir.substr(1);
		}
		dir = '/' + dir;
		if (dir.substr(-1, 1) != '/') {
			dir = dir + '/';
		}
		if (target == dir) {
			return;
		}
		$.ajax({
			url : 'ajax/move.php',
			data : "dir=" + encodeURIComponent(dir) + "&file=" + encodeURIComponent(file) + '&target=' + encodeURIComponent(target),
			complete : function(data) {
				boolOperationFinished(data, function() {
					FileList.remove(file);
				});
			}
		});
	},
	tolerance : 'pointer'
};

function getMimeIcon(mime, ready) {
	if (getMimeIcon.cache[mime]) {
		ready(getMimeIcon.cache[mime]);
	} else {
		$.get(OC.filePath('files', 'ajax', 'mimeicon.php') + '?mime=' + mime, function(path) {
			getMimeIcon.cache[mime] = path;
			ready(path);
		});
	}
};

getMimeIcon.cache = {};
