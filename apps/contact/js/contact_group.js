$(document).ready(function() {
	ContactGroup.setTabHandler();
	if (OC_Appconfig.getValue('contact', 'systemGroupEnabled', false) == 0) {
		ContactGroup.setDefault();
		ContactGroup.setDefaultHandler();
	} else {
		$("#contactGroupMainTable").hide();
	}
});

var ContactGroup = {
	defaultBgColor : '#CBD4B3',
	activeBgColor : '#FFFFFF',
	selectedBgColor : '#99A77E',
	titleColor : '#566635',
	//是否為「編輯中」
	isEditing : false,
	selectedGroupId : null,
	setTabHandler : function() {
		$('#contactGroupTab').css('background-color', ContactGroup.defaultBgColor).css('color', ContactGroup.titleColor);
		$('#contactGroupTab').on('click', function() {
			if ($("#contactGroupMainTable").is(':hidden')) {
				$("#contactGroupMainTable").show();
				ContactGroup.setDefault();
				ContactGroup.setDefaultHandler();
				$("#contactSystemGroupMainTable").hide();
				ContactInGroup.setDefault();
			}
		});
	},
	//初始狀態
	setDefault : function() {
		$('#contactGroupTab').css('background-color', ContactGroup.defaultBgColor).css('color', ContactGroup.titleColor);
		$("#contactGroupMainTable tr").css('background-color', ContactGroup.defaultBgColor);
		// $("#contactGroupMainTable .titleTr").css('color', ContactGroup.titleColor);
		ContactGroup.getGroupList();
		ContactGroup.isEditing = false;
		$("#contactGroupListTable .beSelected").each(function() {
			$(this).removeClass("beSelected");
			ContactGroup.setBgColor($(this));
		});
		$(".groupNameSpan").show();
		$(".renameItem").hide();
		$(".contactGroupButton").hide();
	},
	getGroupList : function() {
		$("#contactGroupListLoaginImg").show();
		$("#contactGroupListTable").hide();
		$.post(OC.filePath("contact", "ajax", "contact_group.php"), {
			action : "getGroupList",
		}, function(data) {
			if (data.status == "success") {
				$("#contactGroupListTable tr:gt(0)").remove();
				//resultArray = ContactGroup.sortGroupName(data.result);
				resultArray = data.result;
				$.each(resultArray, function(key, val) {
					var id = val['groupId'];
					var name = val['groupName'];
					var cloneTr = $("#contactGroupListTable tr:first").clone();
					cloneTr.find(".groupNameSpan").html(name);
					cloneTr.find(".groupId").val(id);
					cloneTr.find(".renameGroupText").val(name);
					cloneTr.find(".renameItem").attr("alt", name);
					cloneTr.find(".renameContactGroupButton").attr("alt", name);
					cloneTr.find(".deleteContactGroupButton").attr("alt", name);
					cloneTr.appendTo("#contactGroupListTable");
					ContactGroup.setHandler(cloneTr);
				});
				$("#contactGroupListTable tr:first").hide().addClass("isForClone");
				//$("#contactGroupListTable tr:first .deleteContactGroupButton").hide();
				$("#contactGroupListTable tr:gt(0)").show();
				//$("#deleteContactGroupButton").show();
				$("#contactGroupListTable").show();
				$("#contactGroupListLoaginImg").hide();
			}
		});
	},
	//預寫不用
	sortGroupName : function(result) {
		var resultArray = new Array();
		$.each(result, function(index, val) {
			var id = val.id;
			var name = (val.name) ? val.name : id;
			resultArray[index] = new Array();
			resultArray[index][0] = name;
			resultArray[index][1] = id;
		});
		return resultArray.sort();
	},
	addGroup : function() {
		$.post(OC.filePath("contact", "ajax", "contact_group.php"), {
			action : "addGroup",
			groupName : $("#groupName").val().trim()
		}, function(data) {
			if (data.status == "success") {
				ContactGroup.getGroupList();
				$("#groupName").val("");
			}
		});
	},
	renameGroup : function(obj) {
		$.post(OC.filePath("contact", "ajax", "contact_group.php"), {
			action : "renameGroup",
			groupName : $(obj).attr("alt"),
			newGroupName : $(obj).val().trim()
		}, function(data) {
			if (data.status == "success") {
				ContactGroup.setDefault();
			}
		});
	},
	delGroup : function(obj) {
		if (!confirm(t('contact', 'Are you sure') + '?')) {
			return false;
		}
		$.post(OC.filePath("contact", "ajax", "contact_group.php"), {
			action : "delGroup",
			groupName : $(obj).attr("alt")
		}, function(data) {
			if (data.status == "success") {
				ContactGroup.getGroupList();
				ContactInGroup.setDefault();
			}
		});
	},
	setDefaultHandler : function() {
		$("#groupName").on("keyup", function(e) {
			if (e.which == 13) {
				if ($(this).val()) {
					ContactGroup.addGroup();
				}
			}
		});
		$("#addContactGroupButton").click(ContactGroup.addGroup);
		$(document).on("click", ".renameContactGroupButton", function() {
			//設定為編輯中
			ContactGroup.isEditing = true;
			//隱藏更新和刪除按鈕
			$(".contactGroupButton").hide();
			var trObj = $(this).parents(".contactGroupTr");
			var renameItemObj = trObj.find(".renameItem");
			var groupNameSpanObj = trObj.find(".groupNameSpan");
			renameItemObj.show().focus();
			groupNameSpanObj.hide();
			ContactInGroup.setDefault();
		}).on("click", ".deleteContactGroupButton", function() {
			ContactGroup.delGroup(this);
		}).on("blur", '.renameGroupText', function() {
			if ($(this).val() != $(this).attr("alt")) {
				ContactGroup.renameGroup(this);
			} else {
				ContactGroup.setDefault();
			}
		}).on("keyup", '.renameGroupText', function(e) {
			if (e.which == 13) {
				if ($(this).val() != $(this).attr("alt")) {
					ContactGroup.renameGroup(this);
				} else {
					ContactGroup.setDefault();
				}
			}
		});
	},
	setHandler : function(obj) {
		obj.mouseover(function() {
			//如果不是在編輯中
			if (!ContactGroup.isEditing) {
				$(this).addClass("beOvered");
				$(this).find(".contactGroupButton").show();
				ContactGroup.setBgColor($(this));
			}
		});
		obj.mouseout(function() {
			if (!ContactGroup.isEditing) {
				$(this).removeClass("beOvered");
				$(this).find(".contactGroupButton").hide();
				ContactGroup.setBgColor($(this));
			}
		});
		obj.click(function(event) {
			if (!ContactGroup.isEditing) {
				//如果按下的目標不是tr裡面的按鈕
				if (event.target.nodeName.toLowerCase() != "input") {
					ContactGroup.selectedGroupId = $(this).find(".groupId").val();
					$("#contactGroupListTable .beSelected").each(function(index) {
						$(this).removeClass("beSelected");
						ContactGroup.setBgColor($(this));
					});
					$(this).addClass("beSelected");
					ContactGroup.setBgColor($(this));
					ContactInGroup.getContactListByGroupId();
				}
			}
		});
	},
	setBgColor : function(obj) {
		if (obj.hasClass("beSelected")) {
			obj.css("background-color", ContactGroup.selectedBgColor);
		} else if (obj.hasClass("beOvered")) {
			obj.css("background-color", ContactGroup.activeBgColor);
		} else {
			obj.css("background-color", ContactGroup.defaultBgColor);
		}
	}
};