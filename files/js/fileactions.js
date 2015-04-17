$(document).ready(function() {
	FileActions.setDefaultDisplay();
	FileActionSet.setOpenAction();
	FileActionSet.setDownloadAction();
	FileActionSet.setDeleteAction(true);
	FileActionSet.setRenameAction();
});

FileActions = {
	//記錄順序
	sort : {},
	//判斷檔案是否可寫入時，是否顯示該action
	inWriteable : {},
	actions : {},
	currentActions : {},
	defaults : {},
	icons : {},
	extend : {
		displayBefore : {},
		displayAfter : {},
		getBefore : {},
		getAfter : {}
	},
	currentFile : null,
	setDefaultDisplay : function() {
		// Sets the file-action buttons behaviour :
		$(document).on('mouseenter', '#fileList tr', function(event) {
			var fileName = $(this).attr("data-file");
			var isLoading = FileList.isLoading(fileName);
			var isChecked = FileList.isChecked(fileName);
			if (!isChecked && !isLoading) {
				if (FileActions.display != false) {
					FileActions.display($(this));
				}
			}
		}).on('mouseleave', '#fileList tr', function(event) {
			FileActions.hide();
		});
	},
	register : function(mime, name, icon, action, sort, inWriteable) {
		if (!FileActions.actions[mime]) {
			FileActions.actions[mime] = {};
		}
		FileActions.actions[mime][name] = action;
		FileActions.icons[name] = icon;
		//記錄登錄的排序
		if (sort) {
			FileActions.sort[name] = sort;
		} else {
			FileActions.sort[name] = 999;
		}
		//true代表，檔案可寫入時，則秀該action；false代表，檔案不可寫入時，才秀該action；空值為不判斷
		if (inWriteable == true || inWriteable == false) {
			FileActions.inWriteable[name] = inWriteable;
		} else {
			FileActions.inWriteable[name] = '';
		}
	},
	setDefault : function(mime, name) {
		FileActions.defaults[mime] = name;
	},
	get : function(mime, type) {
		$.each(FileActions.extend.getBefore, function(key, val) {
			if ( typeof (val) == "function") {
				val();
			}
		});
		var actions = {};
		if (FileActions.actions.all) {
			actions = $.extend(actions, FileActions.actions.all);
		}
		if (mime) {
			if (FileActions.actions[mime]) {
				actions = $.extend(actions, FileActions.actions[mime]);
			}
			var mimePart = mime.substr(0, mime.indexOf('/'));
			if (FileActions.actions[mimePart]) {
				actions = $.extend(actions, FileActions.actions[mimePart]);
			}
		}
		if (type) {//type is 'dir' or 'file'
			if (FileActions.actions[type]) {
				actions = $.extend(actions, FileActions.actions[type]);
			}
		}
		//存放目前檔案要顯示的動作
		FileActions.currentActions = actions;
		$.each(FileActions.extend.getAfter, function(key, val) {
			if ( typeof (val) == "function") {
				val();
			}
		});
		//actions = FileActions.sortActions(actions);
		return actions;
	},
	getDefault : function(mime, type) {
		if (mime) {
			var mimePart = mime.substr(0, mime.indexOf('/'));
		}
		var name = false;
		if (mime && FileActions.defaults[mime]) {
			name = FileActions.defaults[mime];
		} else if (mime && FileActions.defaults[mimePart]) {
			name = FileActions.defaults[mimePart];
		} else if (type && FileActions.defaults[type]) {
			name = FileActions.defaults[type];
		} else {
			name = FileActions.defaults.all;
		}
		var actions = this.get(mime, type);
		return actions[name];
	},
	display : function(trObj) {
		if (trObj.data('renaming') || trObj.attr('class') == 'selected') {
			return;
		}
		$.each(FileActions.extend.displayBefore, function(key, val) {
			if ( typeof (val) == "function") {
				val();
			}
		});

		FileActions.currentFile = trObj;
		if (trObj.find('.action').length < 1) {
			FileActions.addActionBtn(trObj);
		}
		trObj.find('.action').show();
		$.each(FileActions.extend.displayAfter, function(key, val) {
			if ( typeof (val) == "function") {
				val();
			}
		});
		return false;
	},
	hide : function() {
		$('#fileList .action').hide();
	},
	addActionBtn : function(trObj) {
		var fileName = FileActions.getCurrentFile();
		var defaultAction = FileActions.getDefault(FileActions.getCurrentMimeType(), FileActions.getCurrentType());
		FileActions.get(FileActions.getCurrentMimeType(), FileActions.getCurrentType());
		var actions = FileActions.currentActions;
		var actionNameArray = new Array();
		for (name in actions) {
			actionNameArray.push(name);
		}

		actionNameArray = actionNameArray.sort(FileActions.sortActionName);
		$.each(actionNameArray, function(index, name) {
			var fileWriteable = FileActions.getCurrentWriteable();
			var inWriteable = FileActions.inWriteable[name];
			//如果檔案可寫入，但action是不可寫入，則跳出；反之亦然
			if (inWriteable != '' && fileWriteable != inWriteable) {
				return 1;
			}
			var action = actions[name];
			if (action && action != defaultAction) {
				var img = FileActions.icons[name];
				if (img.call) {
					img = img(fileName);
				}
				var html = '<a href="#" order="' + FileActions.sort[name] + '" title="' + name + '" class="action"/>';
				var element = $(html);
				if (img) {
					element.append($('<img src="' + img + '"/>'));
				}
				element.data('action', name);
				// alert(trObj.find('a.name').attr('class'))
				// trObj.children('a.name').append(element);
				trObj.find('a.name').append(element);
				element.on('click', function(event) {
					event.stopPropagation();
					event.preventDefault();
					var action = actions[$(this).data('action')];
					var currentFile = FileActions.getCurrentFile();
					// FileActions.hide();
					action(currentFile);
				});
				// element.hide();
			}
		});
	},
	getCurrentFile : function() {
		return FileActions.currentFile.attr('data-file');
	},
	getCurrentMimeType : function() {
		return FileActions.currentFile.attr('data-mime');
	},
	getCurrentType : function() {
		return FileActions.currentFile.attr('data-type');
	},
	getCurrentWriteable : function() {
		if (FileActions.currentFile.attr('data-write').toLowerCase() == 'false')
			return false;
		else
			return true;
	},
	sortActionName : function(a, b) {
		//將登錄的action順序做排序(由小排到大)
		a = FileActions.sort[a];
		b = FileActions.sort[b];
		return ((a < b) ? -1 : ((a > b) ? 1 : 0));
	},
};

var FileActionSet = {
	setOpenAction : function() {
		FileActions.register('dir', t('files', 'Open'), '', function(filename) {
			window.location = 'index.php?dir=' + encodeURIComponent($('#dir').val()).replace(/%2F/g, '/') + '/' + encodeURIComponent(filename);
		});
		FileActions.setDefault('dir', t('files', 'Open'));
	},
	setDownloadAction : function() {
		if ($('#allowZipDownload').val() == 1) {
			var downloadScope = 'all';
		} else {
			var downloadScope = 'file';
		}
		var img = OC.imagePath('core', 'actions/download');
		var title = t('files', 'Download');
		FileActions.register(downloadScope, title, img, function(filename) {
			window.location = 'ajax/download.php?files=' + encodeURIComponent(filename) + '&dir=' + encodeURIComponent($('#dir').val());
			var tr = $('#fileList tr[data-file="' + filename + '"]');
			var actionObj = tr.find('a[title="' + title + '"]');
			//清空下載按鈕原本的動作(再度按下時會無反應)，並設為半透明
			actionObj.off('click').on('click', function() {
				return false;
			}).css('opacity', 0.3);
		}, 3);
	},
	setDeleteAction : function(inWriteable) {
		var img = OC.imagePath('core', 'actions/delete');
		FileActions.register('all', t('files', 'Delete'), img, function(filename) {
			FileDelete.do_delete(filename);
		}, 5, inWriteable);
	},
	setRenameAction : function() {
		var img = OC.imagePath('core', 'actions/rename');
		FileActions.register('all', t('files', 'Rename'), img, function(filename) {
			FileRename.addRenameObj(filename);
		}, 4, true);
	},
};
