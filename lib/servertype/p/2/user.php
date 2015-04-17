<?php
/**
 * P2 server 為 U-Sync口袋碟
 * 只有口袋碟才需要判斷是否有付費，否則全部的用戶都當作有付費
 */
class DIFF_User extends ServerType_User {

	static function isPaidUser($username) {
		require_once $_SERVER["DOCUMENT_ROOT"] . '/home/configuration.php';
		$jconfig = new JConfig;

		$dbname = $jconfig -> db;
		$dbuser = $jconfig -> user;
		$dbpass = $jconfig -> password;
		$dbhost = $jconfig -> host;
		$prefix = $jconfig -> dbprefix;
		$link = mysql_connect($dbhost, $dbuser, $dbpass);
		if (!$link) {
			die('Could not connect: ' . mysql_error());
		}
		$sel = mysql_select_db($dbname, $link);
		if (!$sel) {
			die('Can\'t use db: ' . mysql_error());
		}

		mysql_query("SET NAMES 'utf8'");
		mysql_query("SET CHARACTER_SET_CLIENT=utf8");
		mysql_query("SET CHARACTER_SET_RESULTS=utf8");
		$query = "select * from ( " . "select t1.username, t3.description paidItem, max(t2.expireDateSp) expireDate " . "  from " . $prefix . "user_product_current t1 " . "  join " . $prefix . "user_product_period t2 " . "    on t1.username=t2.username " . "  join " . $prefix . "product_scheme t3 " . "    on t2.schemeCode=t3.schemeCode " . " where t1.paymentQuota > 0 " . "   and t1.username= '" . $username . "'" . "union " . "select t1.username, t2.productname paidItem, date_add(now(), interval 1 month) expireDate" . "  from " . $prefix . "users t1 " . "  join " . $prefix . "user_product t2 " . "    on t2.username=t1.username " . " where t2.productname='U-Sync-30GB'" . "   and t1.username= '" . $username . "'" . ") t4 " . " where t4.paidItem is not null " . "   and t4.expireDate > '" . OC_Helper::formatDateTimeLocalToUTC() . "'";
		$dataset = mysql_query($query);

		if (list($data_username, $paidItem) = mysql_fetch_array($dataset)) {
			// TODO 亞太用戶部分，亞太Server會認IP，177暫時無法再跟亞太確認用戶是否有效。
			return $paidItem;
		} else {
			$query = "select max(t1.expires_date ) expires_date " . "  from " . $prefix . "iap_receipt t1 " . " where t1.username='" . $username . "' " . "   and t1.expires_date < '" . OC_Helper::formatDateTimeLocalToUTC() . "'";
			$dataset = mysql_query($query);

			// 若是未付費用戶 是否為 iOS 付費過期用戶？
			if (list($expires_date) = mysql_fetch_array($dataset)) {

				include_once $_SERVER["DOCUMENT_ROOT"] . '/iap/inc_isrenew.php';

				// 若是iOS 付費過期用戶，則檢查是否有續費，有的話回傳付費產品名稱
				if (isrenew($username)) {
					$query = "select t3.description paidItem" . "  from " . $prefix . "user_product_current t1 " . "  join " . $prefix . "user_product_period t2 " . "    on t1.username=t2.username " . "  join " . $prefix . "product_scheme t3 " . "    on t2.schemeCode=t3.schemeCode " . " where t1.paymentQuota > 0 " . "   and t1.username='" . $username . "'";
					$dataset = mysql_query($query);
					if (list($paidItem) = mysql_fetch_array($dataset))

						return $paidItem;
				}
			}
			// 未付費用戶若 paymentQuota <> 0 則須將 paymentQuota 設為 0 並重新計算實際總 Quota
			$query = "select paymentQuota " . "  from " . $prefix . "user_product_current " . " where paymentQuota > 0 " . "   and username= '" . $username . "'";
			$dataset = mysql_query($query);
			if (list($paymentQuota) = mysql_fetch_array($dataset)) {
				// 將 paymentQuota 設為 0
				$query = "update " . $prefix . "user_product_current " . "   set paymentQuota=0 " . " where username='" . $username . "'";
				mysql_query($query);
				// 重新計算實際總 Quota
				calculateUserQuota($username);
			}
			return FALSE;
		}
	}

}
