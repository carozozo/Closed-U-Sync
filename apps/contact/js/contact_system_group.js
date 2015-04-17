$(document).ready(function() {
	// $("#contactSystemGroupMainTable").hide();
	ContactSystemGroup.setTabHandler();
	ContactSystemGroup.setDefault();
	ContactSystemGroup.setDefaultHandler();
});

var ContactSystemGroup = {
	defaultBgColor : '#9BC11F',
	activeBgColor : '#FFFFFF',
	selectedBgColor : '#92A847',
	titleColor : '#4B5427',
	//是否為「編輯中」
	isEditing : false,
	selectedSystemGroupId : null,
	setTabHandler : function() {
		$('#contactSystemGroupTab').css('background-color', ContactSystemGroup.defaultBgColor).css('color', ContactSystemGroup.titleColor);
		$('#contactSystemGroupTab').on('click', function() {
			if ($("#contactSystemGroupMainTable").is(':hidden')) {
				$("#contactSystemGroupMainTable").show();
				ContactSystemGroup.setDefault();
				ContactSystemGroup.setDefaultHandler();
				$("#contactGroupMainTable").hide();
				ContactInGroup.setDefault();
			}
		});
	},
	//初始狀態
	setDefault : function() {
		$("#contactSystemGroupMainTable tr").css('background-color', ContactSystemGroup.defaultBgColor);
		$("#contactSystemGroupMainTable .titleTr").css('color', ContactSystemGroup.titleColor);
		ContactSystemGroup.getSystemGroupList();
		ContactSystemGroup.isEditing = false;
		$("#contactSystemGroupListTable .beSelected").each(function() {
			$(this).removeClass("beSelected");
			ContactSystemGroup.setBgColor($(this));
		});
		$(".systemGroupNameSpan").show();
		$(".renameItem").hide();
		$(".contactSystemGroupButton").hide();
	},
	getSystemGroupList : function() {
		$("#contactSystemGroupListLoaginImg").show();
		$("#contactSystemGroupListTable").hide();
		$.post(OC.filePath("contact", "ajax", "contact_system_group.php"), {
			action : "getSystemGroupList",
		}, function(data) {
			if (data.status == "success") {
				$("#contactSystemGroupListTable tr:gt(0)").remove();
				//resultArray = ContactSystemGroup.sortGroupName(data.result);
				resultArray = data.result;
				$.each(resultArray, function(key, val) {
					var id = val['systemGroupId'];
					var name = val['systemGroupName'];
					var cloneTr = $("#contactSystemGroupListTable tr:first").clone();
					cloneTr.find(".systemGroupNameSpan").html(name);
					cloneTr.find(".systemGroupId").val(id);
					cloneTr.find(".renameItem ").val(name);
					cloneTr.find(".renameItem").attr("alt", id);
					cloneTr.find(".renameContactSystemGroupButton").attr("alt", name);
					cloneTr.find(".deleteContactSystemGroupButton").attr("alt", name);
					cloneTr.appendTo("#contactSystemGroupListTable");
					ContactSystemGroup.setHandler(cloneTr);
				});
				$("#contactSystemGroupListTable tr:first").hide().addClass("isForClone");
				//$("#contactSystemGroupListTable tr:first .deleteContactSystemGroupButton").hide();
				$("#contactSystemGroupListTable tr:gt(0)").show();
				//$("#deleteContactSystemGroupButton").show();
				$("#contactSystemGroupListTable").show();
				$("#contactSystemGroupListLoaginImg").hide();
			}
		});
	},
	//TODO 暫時不用
	addGroup : function() {
		$.post(OC.filePath("contact", "ajax", "contact_group.php"), {
			action : "addGroup",
			groupName : $("#groupName").val().trim()
		}, function(data) {
			if (data.status == "success") {
				ContactSystemGroup.getSystemGroupList();
				$("#groupName").val("");
			}
		});
	},
	//TODO 暫時不用
	renameSystemGroup : function(obj) {
		systemGroupId = $(obj).attr('alt');
		systemGroupName = $(obj).val().trim();
		$.post(OC.filePath("contact", "ajax", "contact_system_group.php"), {
			action : "renameSystemGroup",
			systemGroupId : systemGroupId,
			systemGroupName : systemGroupName,
		}, function(data) {
			if (data.status == "success") {
				// var newGroupName = data.result;
				// var target = $(obj).siblings(".systemGroupNameSpan");
				// target.html(newGroupName);
				// $(obj).attr("alt", newGroupName).val(newGroupName);
				ContactSystemGroup.setDefault();
			}
		});
	},
	//TODO 暫時不用
	delGroup : function(obj) {
		if (!confirm(t('contact', 'Are you sure') + '?')) {
			return false;
		}
		$.post(OC.filePath("contact", "ajax", "contact_group.php"), {
			action : "delGroup",
			groupName : $(obj).attr("alt")
		}, function(data) {
			if (data.status == "success") {
				ContactSystemGroup.getSystemGroupList();
				ContactInGroup.setDefault();
			}
		});
	},
	setDefaultHandler : function() {
		$("#addContactSystemGroupButton").click(ContactSystemGroup.addGroup);
		$(document).on("click", ".renameContactSystemGroupButton", function() {
			//設定為編輯中
			ContactSystemGroup.isEditing = true;
			//隱藏更新和刪除按鈕
			$(".contactSystemGroupButton").hide();
			var trObj = $(this).parents(".contactSystemGroupTr");
			var renameItemObj = trObj.find(".renameItem");
			var systemGroupNameSpanObj = trObj.find(".systemGroupNameSpan");
			renameItemObj.show().focus();
			systemGroupNameSpanObj.hide();
			ContactInGroup.setDefault();
		}).on("click", ".deleteContactSystemGroupButton", function() {
			ContactSystemGroup.delGroup(this);
		}).on("blur", '.renameSystemGroupText', function() {
			if ($(this).val() != $(this).attr("alt")) {
				ContactSystemGroup.renameSystemGroup(this);
			} else {
				ContactSystemGroup.setDefault();
			}
		}).on("keyup", '.renameSystemGroupText', function(e) {
			if (e.which == 13) {
				if ($(this).val() != $(this).attr("alt")) {
					ContactSystemGroup.renameSystemGroup(this);
				} else {
					ContactSystemGroup.setDefault();
				}
			}
		});
	},
	setHandler : function(obj) {
		obj.mouseover(function() {
			//如果不是在編輯中
			if (!ContactSystemGroup.isEditing) {
				$(this).addClass("beOvered");
				//暫時不用
				// $(this).find(".contactSystemGroupButton").show();
				ContactSystemGroup.setBgColor($(this));
			}
		});
		obj.mouseout(function() {
			if (!ContactSystemGroup.isEditing) {
				$(this).removeClass("beOvered");
				$(this).find(".contactSystemGroupButton").hide();
				ContactSystemGroup.setBgColor($(this));
			}
		});
		obj.click(function(event) {
			if (!ContactSystemGroup.isEditing) {
				//如果按下的目標不是tr裡面的按鈕
				if (event.target.nodeName.toLowerCase() != "input") {
					//將選取的system group id代入
					ContactSystemGroup.selectedSystemGroupId = $(this).find(".systemGroupId").val();
					$("#contactSystemGroupListTable .beSelected").each(function(index) {
						$(this).removeClass("beSelected");
						ContactSystemGroup.setBgColor($(this));
					});
					$(this).addClass("beSelected");
					ContactSystemGroup.setBgColor($(this));
					ContactInGroup.getContactListBySystemGroupId();
				}
			}
		});
	},
	setBgColor : function(obj) {
		if (obj.hasClass("beSelected")) {
			obj.css("background-color", ContactSystemGroup.selectedBgColor);
		} else if (obj.hasClass("beOvered")) {
			obj.css("background-color", ContactSystemGroup.activeBgColor);
		} else {
			obj.css("background-color", ContactSystemGroup.defaultBgColor);
		}
	}
};