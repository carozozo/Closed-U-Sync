$(document).ready(function() {
	Permission.setDefaultHandler();
});
/**
 * GroupShare中，權限相關操作
 */
var Permission = {
	/**
	 * 指定操作
	 */
	setDefaultHandler : function() {
		$("#permissionChBox1").on('click', PermissionChBox1.click);
		$("#permissionChBox2").on('click', PermissionChBox2.click);
	},
	/**
	 * 取得權限
	 */
	sumPermission : function() {
		var permission1 = PermissionChBox1.getPermission();
		var permission2 = PermissionChBox2.getPermission();
		var permission = permission1 + permission2;
		return permission;
	},
};
/**
 * 「允許下載/複製」選項相關操作
 */
var PermissionChBox1 = {
	/**
	 * 取得checkbox的權限狀態
	 */
	getPermission : function() {
		if ($('#permissionChBox1').prop('checked')) {
			return 1;
		}
		return 0;
	},
	click : function() {
		var source = $('#source').val();
		var permission = Permission.sumPermission();
		GroupShare_Manager.updatePermission(source, permission, SharedMainTable.setGroupShareList);
	},
};
/**
 * 「允許上傳」選項相關操作
 */
var PermissionChBox2 = {
	/**
	 * 取得checkbox的權限狀態
	 */
	getPermission : function() {
		if ($('#permissionChBox2').prop('checked')) {
			return 2;
		}
		return 0;
	},
	click : function() {
		var source = $('#source').val();
		var permission = Permission.sumPermission();
		GroupShare_Manager.updatePermission(source, permission, SharedMainTable.setGroupShareList);
	},
};
