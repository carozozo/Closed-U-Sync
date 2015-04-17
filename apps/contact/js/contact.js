$(document).ready(function() {
	Contact.setDefault();
	Contact.setDefaultHandler();
});

var Contact = {
	defaultBgColor : '#EDC9A7',
	activeBgColor : '#FFFFFF',
	selectedBgColor : '#CEA683',
	titleColor : '#774713',
	//是否為「編輯中」
	isEditing : false,
	setDefault : function() {
		$('#contactTab').css('background-color', Contact.defaultBgColor).css('color', Contact.titleColor);
		$("#contactMainTable tr").css('background-color', Contact.defaultBgColor);
		$("#contactMainTable .titleTr").css('color', Contact.titleColor);
		Contact.getContactList();
		$("#selectAllContact").attr("checked", false);
		$("#contactListTable .beSelected").each(function() {
			$(this).removeClass("beSelected");
			Contact.setBgColor($(this));
		});
		$(".nicknameSpan,.emailSpan").show();
		$(".renameItem,.contactListButton").hide();
	},
	setAddContactInputDefalt : function() {
		$("#addContactId").val($("#addContactId").attr('alt'));
		$("#addContactNickname").val($("#addContactNickname").attr('alt'));
		$("#addContactId,#addContactNickname").css('color', "#ccc");
	},
	getContactList : function() {
		$("#contactListLoaginImg").show();
		$("#contactListTable").hide();
		$.post(OC.filePath("contact", "ajax", "contact.php"), {
			action : "getContactList",
			groupName : $("#selectedGroup").val()
		}, function(data) {
			if (data.status == "success") {
				//除了樣版之外其它的tr都刪除
				$("#contactListTable tr:gt(0)").remove();
				//var resultArray = Contact.sortNickname(data.result);
				var resultArray = data.result;
				$.each(resultArray, function(index, val) {
					var contact = val['contact'];
					var nickname = val['nickname'];
					var email = val['email'];
					var cloneTr = $("#contactListTable tr:first").clone();
					cloneTr.find("#contactId").val(contact);
					cloneTr.find(".nicknameSpan").html(nickname);
					if (email) {
						cloneTr.find(".emailSpan").html('(' + email + ')');
					}
					cloneTr.find(".renameNicknameText").val(nickname);
					cloneTr.find(".renameItem").attr("alt", nickname).val(nickname);
					cloneTr.find(".renameContactNicknameButton").attr("alt", nickname);
					cloneTr.find(".deleteContactIdButton").attr("alt", contact);
					cloneTr.appendTo("#contactListTable");
					Contact.setHandler(cloneTr);
				});

				//隱藏樣版
				$("#contactListTable tr:first").hide();
				$("#contactListTable tr:gt(0)").show();
				$("#contactListTable").show();
				$("#contactListLoaginImg").hide();
				//Contact.setHandler();
			}
		});
	},
	//預寫不用
	sortNickname : function(result) {
		var resultArray = new Array();
		$.each(result, function(index, val) {
			var contact = val.contact;
			var nickname = (val.nickname) ? val.nickname : contact;
			resultArray[index] = new Array();
			resultArray[index][0] = nickname;
			resultArray[index][1] = contact;
		});
		return resultArray.sort();
	},
	addContact : function() {
		var contactId = $("#addContactId").val().trim();
		//如果沒輸入暱稱，則預設為Id
		// var contactNickname = ($("#addContactNickname").val() == $("#addContactNickname").attr("alt")) ? contactId : $("#addContactNickname").val();
		if (!contactId || contactId == $("#addContactId").attr("alt")) {
			alert(t('contact', 'Please input userId/email'));
			return false;
		}
		$.post(OC.filePath("contact", "ajax", "contact.php"), {
			action : "addContact",
			contactId : contactId,
			// contactNickname : contactNickname,
		}, function(data) {
			if (data.status == "success") {
				if (data.result == true) {
					Contact.getContactList();
					$("#addContactId,#addContactNickname").val("");
					Contact.setAddContactInputDefalt();
				} else {
					alert(t('contact', data.result));
				}
			}
		});
	},
	// 新增複數聯絡人
	addContacts:function(contacts){
	    $.post(OC.filePath("contact", "ajax", "contact.php"), {
            action : "addContacts",
            contacts : contacts,
            // contactNickname : contactNickname,
        }, function(data) {
            if (data.status == "success") {
                //XXX
                console.log('result='+data.result)
                if (data.result == true) {
                    Contact.getContactList();
                    $("#addContactId,#addContactNickname").val("");
                    Contact.setAddContactInputDefalt();
                } else {
                    alert(t('contact', data.result));
                }
            }
        });
	},
	renameContactNickname : function(obj) {
		var contactId = $(obj).siblings('#contactId').val();
		$.post(OC.filePath("contact", "ajax", "contact.php"), {
			action : "renameContactNickname",
			contactId : contactId,
			contactNickname : $(obj).val().trim()
		}, function(data) {
			if (data.status == "success") {
				// var newNickname = data.result;
				// var target = $(obj).siblings(".nicknameSpan");
				// target.html(newNickname);
				// $(obj).attr("alt", newNickname);
				Contact.setDefault();
			}
		});
	},
	delContact : function(obj) {
		if (!confirm(t('contact', 'Are you sure') + '?'))
			return false;
		$.post(OC.filePath("contact", "ajax", "contact.php"), {
			action : "delContact",
			contactId : $(obj).attr("alt")
		}, function(data) {
			if (data.status == "success") {
				Contact.getContactList();
				ContactGroup.setDefault();
				ContactInGroup.setDefault();
			}
		});
	},
	selecteAll : function() {
		$(".contactTr:visible").each(function(index) {
			$(this).addClass("beSelected");
			Contact.setBgColor($(this));
		});
	},
	unSelectAll : function() {
		$(".contactTr:visible").each(function(index) {
			$(this).removeClass("beSelected");
			Contact.setBgColor($(this));
		});
	},
	setDefaultHandler : function() {
		$("#addContactId,#addContactNickname").on('focus', function() {
			if (!$(this).val() || $(this).val() == $(this).attr('alt')) {
				$(this).val("");
			} else {
				$(this).select();
			}
			$(this).css('color', "#000");
		}).on('blur', function() {
			if (!$(this).val() || $(this).val() == $(this).attr('alt')) {
				$(this).val($(this).attr('alt'));
				$(this).css('color', "#ccc");
			}
		}).on("keyup", function(e) {
			var code = e.keyCode || e.which;
			if (code == 13) {
				if ($(this).val() && $(this).val() != $(this).attr("alt")) {
					Contact.addContact();
				}
			}
		}).css('color', "#ccc");
		$("#addContactButton").click(Contact.addContact);
		$("#selectAllContact").click(function() {
			if ($(this).prop('checked')) {
				Contact.selecteAll();
			} else {
				Contact.unSelectAll();
			}
		});
		$(document).on('click', '.renameContactNicknameButton', function() {
			//設定為編輯中
			Contact.isEditing = true;
			//隱藏更新和刪除按鈕
			$(".contactListButton").hide();
			var trObj = $(this).parents(".contactTr");
			var renameItemObj = trObj.find(".renameItem");
			var nicknameSpanObj = trObj.find(".nicknameSpan");
			var emailSpanObj = trObj.find(".emailSpan");
			renameItemObj.show().focus();
			emailSpanObj.hide();
			nicknameSpanObj.hide();
		}).on('click', '.deleteContactIdButton', function() {
			Contact.delContact(this);
		}).on('blur', '.renameNicknameText', function() {
			if ($(this).val() != $(this).attr("alt")) {
				Contact.renameContactNickname(this);
			} else {
				Contact.setDefault();
			}
		}).on("keyup", '.renameNicknameText', function(e) {
			var code = e.keyCode || e.which;
			if (code == 13) {
				if ($(this).val() != $(this).attr("alt")) {
					Contact.renameContactNickname(this);
				} else {
					Contact.setDefault();
				}
			}
		});
	},
	setHandler : function(obj) {
		//因為safari不支援live中的mouseenter及mouseleave，所以只能各別綁定
		obj.mouseover(function() {
			$(this).addClass("beOvered");
			$(this).find(".contactListButton").show();
			Contact.setBgColor($(this));
		});
		obj.mouseout(function() {
			$(this).removeClass("beOvered");
			$(this).find(".contactListButton").hide();
			Contact.setBgColor($(this));
		});
		obj.click(function() {
			if ($(this).hasClass("beSelected"))
				$(this).removeClass("beSelected");
			else
				$(this).addClass("beSelected");
			Contact.setBgColor($(this));
		});
	},
	setBgColor : function(obj) {
		if (obj.hasClass("beSelected")) {
			obj.css("background-color", Contact.selectedBgColor);
		} else if (obj.hasClass("beOvered")) {
			obj.css("background-color", Contact.activeBgColor);
		} else {
			obj.css("background-color", Contact.defaultBgColor);
		}
	}
};