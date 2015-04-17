$(document).ready(function() {
	FileMultiActionSet.setDownlaodAction();
	FileMultiActionSet.setDeleteAction(true);
});
var FileMultiActions = {
	//記錄順序
	sort : {},
	actionObjs : {},
	actions : {},
	extend : {
		beforeAppend : {},
		afterAppend : {},
		beforeShow : {},
		onShow : {},
	},
	addActionObj : function(className, title, img, action, sort) {
		var actionObj = '<a href="#" title="' + title + '" class="' + className + '" ><img class="svg" src="' + img + '" alt="' + title + '"></a>';
		//如果物件不存在的話，則加入
		if (!FileMultiActions.actionObjs[className]) {
			FileMultiActions.actionObjs[className] = actionObj;
			FileMultiActions.actions[className] = action;
			FileMultiActions.sort[className] = (sort) ? sort : 999;
		}
	},
	removeAllActions : function() {
		FileMultiActions.actionObjs = {};
		// $.each(FileMultiActions.actionObjs, function(index) {
		// delete FileMultiActions.actionObjs[index];
		// });
	},
	appendActions : function(extendBefore, extendAfter) {
		if (extendBefore) {
			$.each(FileMultiActions.extend.beforeAppend, function(key, val) {
				if ( typeof (val) == 'function') {
					val();
				}
			});
		}
		var actionsContainer = $('.selectedActions');
		//移除所有已經放到html的按鈕，然後依排序重新放入
		actionsContainer.html('');
		// FileMultiActions.removeAllActions();
		var actionNameArray = new Array();
		for (className in FileMultiActions.actionObjs) {
			actionNameArray.push(className);
		}
		actionNameArray = actionNameArray.sort(FileMultiActions.sortActionName);
		$.each(actionNameArray, function(key, val) {
			var actionObj = $(FileMultiActions.actionObjs[val]);
			if (actionObj) {
				var action = FileMultiActions.actions[val];
				actionsContainer.append(actionObj);
				actionObj.click(function(event) {
					event.stopPropagation();
					event.preventDefault();
					if ( typeof (action) == "function") {
						action();
					}
				});
			}
		});
		
		if (extendAfter) {
			$.each(FileMultiActions.extend.afterAppend, function(key, val) {
				if ( typeof (val) == 'function') {
					val();
				}
			});
		}
		
	},
	sortActionName : function(a, b) {
		//將登錄的action順序做排序(由小排到大)
		a = FileMultiActions.sort[a];
		b = FileMultiActions.sort[b];
		return ((a < b) ? -1 : ((a > b) ? 1 : 0));
	},
};

var FileMultiActionSet = {
	//用來判斷，是否file在writeable的時候才可以刪除
	deleteInWriteable : true,
	setDownlaodAction : function() {
		var img = OC.imagePath('core', 'actions/download');
		FileMultiActions.addActionObj('download', t('files', 'Download'), img, FileMultiActionSet.multiDownloadAction, 1);
	},
	setDeleteAction : function(inWriteable) {
		FileMultiActionSet.deleteInWriteable = inWriteable;
		var img = OC.imagePath('core', 'actions/delete');
		FileMultiActions.addActionObj('delete', t('files', 'Delete'), img, FileMultiActionSet.multiDeleteAction, 2);
	},
	multiDownloadAction : function() {
		var files = FileMultiProcess.getSelectedFiles('name').join(';');
		var dir = $('#dir').val() || '/';
		$('#notification').text(t('files', 'Compress to .zip file'));
		$('#notification').fadeIn().delay(1000).fadeOut();
		window.location = 'ajax/download.php?files=' + encodeURIComponent(files) + '&dir=' + encodeURIComponent(dir);
		return false;
	},
	multiDeleteAction : function() {
		var filesArray = FileMultiProcess.getSelectedFiles('name');
		//如果是writeable才能刪除的
		if (FileMultiActionSet.deleteInWriteable)
			filesArray = FileMultiProcess.getSelectedWritableFiles('name');
		if (filesArray.length > 0) {
			FileDelete.do_delete(filesArray);
		}
		return false;
	},
};
