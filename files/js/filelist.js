$(document).ready(function() {
	$('#notification').hide();
});

FileList = {
	extend : {
		loadingDoneBefore : {},
		loadingDoneAfter : {}
	},
	update : function(fileListHtml) {
		$('#fileList').empty().html(fileListHtml);
	},
	addTextFile : function(name, lastModified, loading) {
		FileList.addFile(name, 2, lastModified, loading);
		var tr = $('tr').filterAttr('data-file', name);
		tr.data('mime', 'text/plain').attr('data-mime', 'text/plain');
		var img = OC.imagePath('core', 'filetypes/text-plain.png');
		tr.find('.fileImg').attr('src', img);
	},
	addFile : function(name, size, lastModified, loading) {
		//var img = (loading) ? OC.imagePath('core', 'loading.gif') : OC.imagePath('core', 'filetypes/file.png');
		var img = OC.imagePath('core', 'filetypes/file.png');
		var html = '<tr data-file="' + name + '" data-type="file" data-mime="file" data-size="' + size + '" data-date="' + formatDate(lastModified) + '" data-write="true">';
		if (name.indexOf('.') != -1) {
			var basename = name.substr(0, name.lastIndexOf('.'));
			var extention = name.substr(name.lastIndexOf('.'));
		} else {
			var basename = name;
			var extention = false;
		}
		html += '<td class="checkboxClass" ><input type="checkbox" /></td>';
		html += '<td class="filename">';
		html += '<a class="name" href="download.php?file=' + $('#dir').val() + '/' + name + '">';
		html += '<img class="fileImg" src="' + img + '" height="32" />';
		html += '<span class="nametext">' + basename;

		if (extention)
			html += '<span class="extention">' + extention + '</span>';
		html += '</span></a></td>';
		if (size != t('files', 'Pending'))
			simpleSize = simpleFileSize(size);
		else
			simpleSize = t('files', 'Pending');

		sizeColor = Math.round(200 - size / (1024 * 1024) * 2);
		lastModifiedTime = Math.round(lastModified.getTime() / 1000);
		modifiedColor = Math.round((Math.round((new Date()).getTime() / 1000) - lastModifiedTime) / 60 / 60 / 24 * 14);
		html += '<td class="filesize" title="' + humanFileSize(size) + '" style="color:rgb(' + sizeColor + ',' + sizeColor + ',' + sizeColor + ')">' + simpleSize + '</td>';
		html += '<td class="date"><span class="modified" title="' + formatDate(lastModified) + '" style="color:rgb(' + modifiedColor + ',' + modifiedColor + ',' + modifiedColor + ')">' + PFunctions.relative_time(lastModified.getTime() / 1000) + '</span></td>';
		html += '</tr>';
		FileList.insertElement(name, 'file', $(html).attr('data-file', name));
		if (loading) {
			$('tr').filterAttr('data-file', name).data('loading', true);
		} else {
			$('tr').filterAttr('data-file', name).find('td.filename').draggable(dragOptions);
		}
	},
	addDir : function(name, size, lastModified) {
		html = $('<tr></tr>').attr({
			"data-type" : "dir",
			"data-size" : size,
			"data-file" : name,
			"data-write" : "true"
		});
		td = $('<td></td>').attr({
			"class" : "checkboxClass"
		});
		td.append('<input type="checkbox" />');
		html.append(td);

		td = $('<td></td>').attr("class", "filename");
		var link_elem = $('<a></a>').attr({
			"class" : "name",
			"href" : "index.php?dir=" + encodeURIComponent($('#dir').val() + '/' + name).replace(/%2F/g, '/')
		});
		link_elem.append($('<img/>').addClass('fileImg').attr({
			"src" : OC.imagePath('core', 'filetypes/folder.png'),
			"width" : "32",
			"height" : "32"
		}));
		link_elem.append($('<span></span>').addClass('nametext').text(name));
		td.append(link_elem);
		html.append(td);
		if (size != 'Pending') {
			simpleSize = simpleFileSize(size);
		} else {
			simpleSize = 'Pending';
		}
		sizeColor = Math.round(200 - Math.pow((size / (1024 * 1024)), 2));
		lastModifiedTime = Math.round(lastModified.getTime() / 1000);
		modifiedColor = Math.round((Math.round((new Date()).getTime() / 1000) - lastModifiedTime) / 60 / 60 / 24 * 5);
		td = $('<td></td>').attr({
			"class" : "filesize",
			"title" : humanFileSize(size),
			"style" : 'color:rgb(' + sizeColor + ',' + sizeColor + ',' + sizeColor + ')'
		}).text(simpleSize);
		html.append(td);

		td = $('<td></td>').attr({
			"class" : "date"
		});
		td.append($('<span></span>').attr({
			"class" : "modified",
			"title" : formatDate(lastModified),
			"style" : 'color:rgb(' + modifiedColor + ',' + modifiedColor + ',' + modifiedColor + ')'
		}).text(PFunctions.relative_time(lastModified.getTime() / 1000)));
		html.append(td);

		FileList.insertElement(name, 'dir', html);

		$('tr').filterAttr('data-file', name).find('td.filename').draggable(dragOptions);
		$('tr').filterAttr('data-file', name).find('td.filename').droppable(folderDropOptions);
	},
	refresh : function(data) {
		result = jQuery.parseJSON(data.responseText);
		if ( typeof (result.data.breadcrumb) != 'undefined') {
			updateBreadcrumb(result.data.breadcrumb);
		}
		FileList.update(result.data.files);
		resetFileActionPanel();
	},
	remove : function(name) {
		$('tr').filterAttr('data-file', name).find('td.filename').draggable('destroy');
		$('tr').filterAttr('data-file', name).remove();
		if ($('tr[data-file]').length == 0) {
			$('#emptyfolder').show();
			$('.file_upload_filename').addClass('highlight');
		}
	},
	insertElement : function(name, type, element) {
		//find the correct spot to insert the file or folder
		var fileElements = $('tr[data-file][data-type="' + type + '"]');
		var ifReplace = false;
		var pos;
		if (name.localeCompare($(fileElements[0]).attr('data-file')) < 0) {
			pos = -1;
		} else if (name.localeCompare($(fileElements[fileElements.length - 1]).attr('data-file')) > 0) {
			pos = fileElements.length - 1;
		} else {
			for (var pos = 0; pos < fileElements.length; pos++) {
				if (name.localeCompare($(fileElements[pos]).attr('data-file')) == 0) {
					//如果上傳的檔名和列表中的檔名一樣的話,則取代
					ifReplace = true;
					break;
				}
				//如果目前要比對的不是最後一列
				if (pos < fileElements.length - 1) {
					if (name.localeCompare($(fileElements[pos]).attr('data-file')) > 0 && name.localeCompare($(fileElements[pos + 1]).attr('data-file')) < 0) {
						break;
					}
				}
			}
		}
		if (fileElements.length) {
			if (ifReplace) {
				$(fileElements[pos]).replaceWith(element);
			} else {
				if (pos == -1) {
					$(fileElements[0]).before(element);
				} else {
					$(fileElements[pos]).after(element);
				}
			}
		} else if (type == 'dir' && $('tr[data-file]').length > 0) {
			$('tr[data-file]').first().before(element);
		} else {
			$('#fileList').append(element);
		}
		$('#emptyfolder').hide();
		$('.file_upload_filename').removeClass('highlight');
	},
	loadingDone : function(name) {
		$.each(FileList.extend.loadingDoneBefore, function(index, val) {
			if ( typeof (val) == "function") {
				val();
			}
		});
		var tr = $('tr').filterAttr('data-file', name);
		var dir = $('#dir').val();
		var mime = tr.data('mime');
		var size = tr.data('size');
		tr.data('loading', false);
		tr.attr('data-mime', mime);
		$.each(FileList.extend.loadingDoneAfter, function(index, val) {
			if ( typeof (val) == "function") {
				val(tr);
			}
		});
		FileList.getMimeIcon(tr, mime);
		tr.find('td.filename').draggable(dragOptions);
	},
	isLoading : function(name) {
		return $('tr').filterAttr('data-file', name).data('loading');
	},
	isChecked : function(name) {
		return $('tr').filterAttr('data-file', name).find("td.checkboxClass input:checkbox").attr("checked");
	},
	getMimeIcon : function(tr, mime) {
		$.post(OC.linkTo('files', 'ajax/mimeicon.php'), {
			mime : mime,
		}, function(path) {
			tr.find(".fileImg").attr('src', path);
		});
	}
};