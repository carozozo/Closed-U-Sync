<?php
try {
	OC_Helper::deleteDirByFullPath(OC::$SERVERROOT . "/apps/u-sync_user_info");
	OC_Appconfig::deleteApp('u-sync_user_info');

	// $quStr = "SHOW TABLES LIKE '*PREFIX*bookmarks'";
	// $query = OC_DB::prepare($quStr);
	// $result = $query -> execute() -> fetchAll();
	// if (count($result)) {
		// $quStr = "DROP TABLE `*PREFIX*bookmarks`;";
		// $query = OC_DB::prepare($quStr);
		// $query -> execute();
	// }
// 
	// $quStr = "SHOW TABLES LIKE '*PREFIX*bookmarks_tags'";
	// $query = OC_DB::prepare($quStr);
	// $result = $query -> execute() -> fetchAll();
	// if (count($result)) {
		// $quStr = "DROP TABLE `*PREFIX*bookmarks_tags`;";
		// $query = OC_DB::prepare($quStr);
		// $query -> execute();
	// }
} catch(exception $e) {
	// exit ;
}
?>