$(document).ready(function() {
	ImageViewer.setHandler();
});
var ImageViewer = {
	setHandler : function() {
		$(document).on('click', '#fileListTable tr a.name', function() {
			var tr = $(this).closest('tr');
			var mime = tr.attr('data-mime');
			if (mime.indexOf('image') > -1) {
				var beginIndex = $('#fileListTable tr a.name').index($(this));
				// alert(beginIndex);
				var hrefArr = ImageViewer.getAllImageHref(beginIndex);
				ImageViewer.openImage(hrefArr);
			}
		});
	},
	getAllImageHref : function(beginIndex) {
		var hrefArrBefore = Array();
		var hrefArrAfter = Array();
		$('#fileList tr').each(function() {
			if ($(this).attr('data-mime').indexOf('image') >= 0) {
				var eachHref = $(this).find('a.name').attr('href');
				var eachTitle = decodeURIComponent($(this).attr('data-file'));
				var hrefObj = {
					href : eachHref,
					title : eachTitle
				}
				var eachIndex = $('#fileList tr').index($(this));
				if (eachIndex >= beginIndex) {
					hrefArrBefore.push(hrefObj);
				} else {
					hrefArrAfter.push(hrefObj);
				}
			}
		});
		//ex 圖檔的index為{1,2,4,6,7}，點選的圖為4,則排出來的結果為{4,6,7,1,2}
		//hrefArr格式為[{href:xxx1,title:yyy1},{href:xxx2,title:yyy2}]
		return $.merge(hrefArrBefore,hrefArrAfter );
	},
	openImage : function(hrefArr) {
		$.fancybox.open(hrefArr);
	},
};
