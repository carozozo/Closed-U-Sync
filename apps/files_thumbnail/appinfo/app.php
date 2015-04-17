<?php
OC::$CLASSPATH['OC_Thumbnail'] = 'apps/files_thumbnail/lib/thumbnail.php';
OC::$CLASSPATH['OC_Thumbnail_DB'] = 'apps/files_thumbnail/lib/thumbnail_db.php';
OC::$CLASSPATH['OC_Thumbnail_Handle'] = 'apps/files_thumbnail/lib/thumbnail_handler.php';
OC::$CLASSPATH['OC_Thumbnail_Sync'] = 'apps/files_thumbnail/lib/thumbnail_sync.php';
OC::$CLASSPATH['OC_Thumbnail_Hooks'] = 'apps/files_thumbnail/lib/thumbnail_hooks.php';
OC::$CLASSPATH['OC_Video_Info'] = 'apps/files_thumbnail/lib/video_info.php';

# 檔案處理時，執行相關縮圖處理
OC_Hook::connect("OC_Filesystem", OC_Filesystem::signal_post_create, "OC_Thumbnail_Hooks", "createThumb");
OC_Hook::connect("OC_Filesystem", OC_Filesystem::signal_delete, "OC_Thumbnail_Hooks", "deleteThumb");
OC_Hook::connect("OC_Filesystem", OC_Filesystem::signal_rename, "OC_Thumbnail_Hooks", "renameThumb");
OC_Hook::connect("OC_Filesystem", OC_Filesystem::signal_copy, "OC_Thumbnail_Hooks", "copyThumb");

OC_App::register(array(
	"order" => 200,
	"id" => "files_thumbnail",
	"name" => "Files Thumbnail"
));
OC_Util::addScript('files_thumbnail', 'thumbnail');

OC_Thumbnail::hideThumbsDir();
?>