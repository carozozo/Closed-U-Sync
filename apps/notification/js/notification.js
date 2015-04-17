$(document).ready(function() {
	if ($('body').attr('id') == 'body-user') {
		Notification.setDefault();
	}
});

var Notification = {
	setDefault : function() {
		Notification.setDocumentAction();
		NotificationBtn.setObj();
		NotificationCount.getCount();
		NotificationMess.setObj();
		NotificationMess.hide();
		NotificationMess.setBtnAction();
		setInterval(NotificationCount.getCount, 30000);
		// setInterval(NotificationMess.getMess, 2000);
	},
	setDocumentAction : function() {
		$(document).on('click', function(event) {
			// alert($('#notificationDiv:visible').length)
			if (!$(event.target).hasClass('notificationClass') && $('#notificationDiv:visible').length > 0)
				$('#notificationDiv').slideUp();
		});
	},
};

var NotificationBtn = {
	setObj : function() {
		// var btnObj = '<div id="notificationBtn">' + t('notification', 'Notification Center') + '</div>';
		var btnObj = '<div id="notificationBtn"><img id="notificationImg" src="' + OC.imagePath('notification', 'notification_center.png') + '"><br/><span id="notificationTitle">' + t('notification', 'Notification') + '</span></div>';
		$('#logoutDiv').before($(btnObj));
		$('#notificationBtn, #notificationImg, #notificationTitle').addClass('notificationClass');
		NotificationBtn.setAction();
	},
	setAction : function() {
		$('#notificationBtn').on('click', function() {
			if ($('#notificationDiv').is(':hidden')) {
				var messList = NotificationMess.getMess(5);
				NotificationMess.setMess(messList);
			}
			NotificationCount.resetCount();
			$('#notificationDiv').slideToggle();
		});
	},
};

var NotificationCount = {
	getCount : function() {
		$.post(OC.filePath('notification', 'ajax', 'notification.php'), {
			action : 'getNotificationCount',
		}, function(data) {
			if (data.status == 'success') {
				var count = data.count;
				if (count > 0) {
					NotificationCount.setObj(data.count);
					NotificationCount.show();
				}
			} else {
				//
			}
		});
	},
	resetCount : function() {
		$.post(OC.filePath('notification', 'ajax', 'notification.php'), {
			action : 'resetNotificationCount',
		}, function(data) {
			if (data.status == 'success') {
				$('#notificationCount').hide();
			} else {
				//
			}
		});
	},
	setObj : function(count) {
		if ($('#notificationCount').length == 0) {
			var countObj = '<div id="notificationCount">' + count + '</div>';
			$('#notificationBtn').parent().append($(countObj));
		}
		$('#notificationCount').html(count).addClass('notificationClass');
	},
	show : function() {
		$('#notificationCount').show();
	},
	hide : function() {
		$('#notificationCount').hide();
	},
};

var NotificationMess = {
	getMess : function(limit) {
		var notificationList;
		$.ajax({
			url : OC.linkTo('notification', 'ajax/notification'),
			type : "POST",
			async : false,
			data : {
				action : 'getNotification',
				limit : limit,
			},
			success : function(data) {
				if (data.status == 'success') {
					notificationList = data.notificationList;
				} else {
					//
				}
			}
		});
		return notificationList;
	},
	setMess : function(notificationList) {
		if (notificationList) {
			$('#notificationMessMainDiv').html('');
			$.each(notificationList, function(key, val) {
				var sn = val['sn'];
				var publisher = val['publisher'];
				var mess = val['message'];
				var link = val['link'];
				var createDate = val['createDate'];
				// alert(createDate)
				createDate = PFunctions.toTimestamp(createDate);
				var notiMessObj = '<div class="notificationMessDiv"><input type="hidden" id="notificationSn" value="' + sn + '">' + publisher + '：<br/><a href="' + link + '">' + mess + '</a><br/><span class="notificationDateSpan">' + PFunctions.relative_time(createDate) + '</span><span class="notificationDelSpan">' + t('notification', 'Delete') + '</span></div>';
				$('#notificationMessMainDiv').append(notiMessObj);
				//將物件加入notificationClass，用來判別setDocumentAction
				$('#notificationMessMainDiv,.notificationMessDiv,.notificationDateSpan,.notificationDelSpan').addClass('notificationClass');
				NotificationMess.setActStyle($('.notificationMessDiv'));
				$('#notificationOptDiv').show();
			});

		} else {
			NotificationMess.showNoMess();
		}
	},
	showNoMess : function() {
		var noMess = '<div id="noMess">' + t('notification', 'No message') + '</div>';
		$('#notificationMessMainDiv').html(noMess);
		$('#notificationOptDiv').hide();
	},
	showAllMess : function(notificationList) {
		$('#notificationDiv').hide();
		var allMess = '';
		$.each(notificationList, function(key, val) {
			var sn = val['sn'];
			var mess = val['message'];
			var createDate = val['createDate'];
			createDate = PFunctions.toTimestamp(createDate);
			var notiMessObj = '<div class="allNotificationMessDiv"><input type="hidden" id="notificationSn" value="' + sn + '">' + mess + '<span class="allNotificationDateSpan">' + PFunctions.relative_time(createDate) + '</span><span class="allNotificationDelSpan">' + t('notification', 'Delete') + '</span></div>';
			allMess += notiMessObj;
		});
		$.fancybox.open(allMess);
		// NotificationMess.setActStyle($('.allNotificationMessDiv'));
		$('.allNotificationDelSpan').on('click', function() {
			var sn = $(this).parent().find('#notificationSn').val();
			NotificationMess.delMess(sn);
			$(this).parent().remove();
		});
	},
	delMess : function(sn) {
		$.post(OC.linkTo('notification', 'ajax/notification'), {
			action : 'delNotification',
			sn : sn,
		}, function(data) {
			if (data.status == 'success') {

			} else {
				//
			}
		})
	},
	setObj : function(notificationList) {
		if ($('#notificationDiv').length == 0) {
			var notificationObj = '<div id="notificationDiv"></div>';
			var notificationMessMainObj = '<div id="notificationMessMainDiv"></div>';
			var notificationOptObj = '<div id="notificationOptDiv"><span id="notificationAllSpan">' + t('notification', 'More Message') + '</span><span id="notificationClearSpan">' + t('notification', 'Clear All Message') + '</span></div>';
			$('#header').prepend($(notificationObj));
			$('#notificationDiv').append($(notificationMessMainObj)).append($(notificationOptObj));
			//將物件加入notificationClass，用來判別setDocumentAction
			$('#notificationDiv,#notificationMessMainDiv,#notificationOptDiv,#notificationAllSpan,#notificationClearSpan').addClass('notificationClass');
		}
	},
	setActStyle : function(messObj) {
		messObj.on('mouseenter', function() {
			$(this).css('background-color', '#9bc11f');
		}).on('mouseleave', function() {
			$(this).css('background-color', '#ffffff');
		});
	},
	setBtnAction : function() {
		$(document).on('click', '#notificationAllSpan', function() {
			var messList = NotificationMess.getMess();
			NotificationMess.showAllMess(messList);
		});
		$(document).on('click', '#notificationClearSpan', function() {
			if (confirm(t('notification', 'Are you sure ?'))) {
				NotificationMess.delMess();
				NotificationMess.hide();
			}
		});
		$(document).on('click', '.notificationDelSpan', function() {
			var sn = $(this).parent().find('#notificationSn').val();
			NotificationMess.delMess(sn);
			$(this).parent().remove();
			if ($('.notificationMessDiv').length == 0) {
				NotificationMess.showNoMess();
			}
		});
	},
	show : function() {
		$('#notificationDiv').slideDown();
	},
	hide : function() {
		$('#notificationDiv').hide();
	},
};
