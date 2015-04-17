$(document).ready(function() {
	ShareBtn.setDefaultHandler();
});
/**
 * GroupShare中，被分享按鈕相關操作
 */
var ShareBtn = {
	/**
	 * 指定操作
	 */
	setDefaultHandler : function() {
		$("#addShareBtn").click(AddShareBtn.click);
		$("#removeShareBtn").click(removeShareBtn.click);
	},
	/**
	 * 新增至分享名單
	 */
	addShare : function() {
		var gids = Shared.getSharedGidsArray();
		var addGids = ContactGroup.getSelectedGidsArray();
		var mergedGids = PFunctions.mergeUniqueArray(gids, addGids);
		if ( typeof (ContactSystemGroup) != 'undefined') {
			// addSystemGids裡面的gid前面會帶's'字串
			var addSystemGids = ContactSystemGroup.getSelectedGidsArray();
			mergedGids = PFunctions.mergeUniqueArray(mergedGids, addSystemGids);
		}

		mergedGids.sort(function(a, b) {
			return a > b;
		});

		var uids = Shared.getSharedUidsArray();
		var addUids = Contact.getSelectedUidsArray();
		var addUids2 = ContactInGroup.getSelectedUidsArray();
		var mergedUids = PFunctions.mergeUniqueArray(uids, addUids);
		mergedUids = PFunctions.mergeUniqueArray(mergedUids, addUids2);
		mergedUids.sort(function(a, b) {
			return a > b;
		});
		ShareBtn.updateGroupShare(mergedGids, mergedUids);
	},
	/**
	 * 從分享名單移除
	 */
	removeShare : function() {
		var gids = Array();
		var uids = Array();
		$(".sharedGroupTr").each(function(index) {
			var val = $(this).find("#sharedGroupId").val();
			// 如果有內容，而且是未選取(要保留)的
			if (val && !$(this).hasClass("beSelected")) {
				gids.push(val);
			}
		});

		$(".sharedContactTr").each(function(index) {
			var val = $(this).find("#sharedContactId").val();
			if (val && !$(this).hasClass("beSelected")) {
				uids.push($.base64.decode(val));
			}
		});
		ShareBtn.updateGroupShare(gids, uids);
	},
	/**
	 * 更新分享名單
	 */
	updateGroupShare : function(gids, uids) {
		var source = $('#source').val();
		gids = Shared.filterEmptyGroup(gids);
		gidsStr = gids.join(";");
		uidsStr = uids.join(";");
		$.post(OC.filePath("files_groupshare", "ajax", "groupshare_handler.php"), {
			action : "updateGroupShare",
			source : source,
			gids : gidsStr,
			uids : uidsStr
		}, function(data) {
			if (data.status == "success") {
				// Permission.sumPermission();
				SharedMainTable.setGroupShareList();
			}
		});
	},
	/**
	 * 設置Group Share操作預設畫面
	 */
	resetAllDefault : function() {
		// 系統群組有開始的時候，恢復為預設畫面
		var systemGroupEnabled = $('#systemGroupEnabled').val();
		if (systemGroupEnabled == '1') {
			ContactSystemGroup.setDefault();
		}
		Contact.setDefault();
		ContactGroup.setDefault();
		ContactInGroup.setDefault();
	},
};

/**
 * 「加入分享名單」按鈕
 */
var AddShareBtn = {
	/**
	 * 設置滑鼠移按下時的動作
	 */
	click : function() {
		if (($(".beSelected").length) > 0) {
			ShareBtn.addShare();
			ShareBtn.resetAllDefault();
		}
	},
};

/**
 * 「從名單中移除」按鈕
 */
var removeShareBtn = {
	/**
	 * 設置滑鼠移按下時的動作
	 */
	click : function() {
		if (($("#sharedListTable").find(".beSelected").length) > 0) {
			ShareBtn.removeShare();
			ShareBtn.resetAllDefault();
		}
	},
};
