<?php
$TRANSLATIONS = array(
	"Convert Wizard" => "格式精靈",
	"Convert" => "轉檔",
	"Select the output device" => "選擇輸出裝置",
	"Output the phone size" => "輸出成手機尺寸",
	"Output the pad size" => "輸出成平板尺寸",
	"Output the TV size" => "輸出成電視尺寸",
	"Enter" => "確定",
	"Free user limit " => "免費用戶每日上限",
	" times each day" => "次",
	"Paid user no limit" => "付費用戶無限制",
	"You had convert " => "您已轉檔",
	" times" => "次",
	"Through the format wizard completes video files will be placed here" => "透過格式精靈轉完的影片檔案都會放在此處",

	"Your" => "您的",
	"convert success" => "轉檔完成",
	"convert failed" => "轉檔失敗",

	# 以下為串流播放回傳訊息
	"Streaming Play" => "串流播放",
	"Media Streaming" => "影片處理中",
	"Media Preview Mode" => "影片預覽模式",
	"File can not preview" => "檔案無法預覽播放",
	"Can not get server respond" => "無法取得伺服器回應",
	"Waiting Streaming Finish" => "轉檔處理中，請稍後再試",
	"Media Streaming only support for mp4 currently" => "目前串流播放只支援mp4格式檔案",
	"you can transform by Convert Wizard" => "其它格式可透過格式精靈轉檔成mp4格式",
	"You can play it by MediaWizard" => "您可透過格式精靈轉檔後播放",
	"or upgrade to paied user" => "或升級為付費帳號",

	# 以下為轉檔精靈回傳的訊息，存放的值在oc_media_streaming_status
	"Sending Data" => "處理中",
	"Converting" => "轉檔中,轉檔完成後將通知您",
	"Waiting Convert" => "轉檔排程中,轉檔完成後將通知您",
	"Copy Output To Convert Target Failed" => "複製輸出檔時錯誤",
	"Convert Success" => "轉檔成功，已將檔案放至格式精靈",
	"Convert Times Over Limit" => "轉檔次數超過限制",
	"Same Target File Exists" => "已存在相同的輸出檔名",

	# 以下是串流時回傳的錯誤訊息，存放的值在oc_media_streaming_status
	"Source Size Over Limit" => "檔案大小超過串流限制",

	# 以下是通用回傳的錯誤訊息，存放的值在oc_media_streaming_status
	"System Free Space Not Enough" => "系統剩餘空間不足",
	"User Free Space Not Enough" => "您的剩餘空間不足",
	"Source Not Exists" => "檔案不存在",
	"Source Not Media Type" => "檔案格式不支援",
	"Create Streaming Link Failed" => "影片連結失效",
	"Output File Not Exists" => "串流輸出檔不存在",
	"Convert Media Failed" => "影片轉檔失敗",
	"Ask If Converting Failed" => "確認是否轉檔時失敗",
	"Ask Conver Media Failed" => "要求轉檔失敗",
	"Convert Server Busy" => "串流伺服器忙碌中，建議使用格式精靈轉檔",

	# Jmail轉檔通知內容
	"Your file" => "您的檔案",
	"convert succeed" => "已經轉檔完成",
	"convert failed" => "未能轉檔完成",
	"Dear" => "您好",
	"convert succeed by Convert Wizard" => "已透過雲端格式精靈轉檔成功",
	"convert failed by Convert Wizard" => "未能透過雲端格式精靈轉檔完成",
	"Please check out your media in Convert Wizard" => "快到格式精靈檢視影片吧",
	"Please check your source file and free space" => "請確認您的檔案完整性及空間是否足夠",
	"Thanks for you using" => "感謝您的使用",
	"Work Team" => "工作團隊",

	# settings用到的內容
	"supporterEmail" => "技術支援者Email",
	"tomcatServer" => "Tomcat Server位址",
	"nginxServer" => "Nginx Server位址",
	"useHls" => "是否開啟HLS(邊轉邊播)",
	"useHlsConvert" => "是否開啟HLS轉檔",
	"convertEnable" => "是否開啟格式精靈",
	"limitSize" => "免費用戶觀看HLS時，檔案大小限制",
	"convertDirPath" => "格式精靈輸出的資料夾路徑",
	"convertLimitTimes" => "免費用戶每日轉檔次數限制",
	"streamingDocumentPath1_6" => "無HLS版本，串流伺服器路徑",
	"streamingTempPath1_6" => "無HLS版本,影片的連結資料夾路徑",
	"outputDirFullPath" => "HLS版本,檔案轉檔後放置的資料夾路徑",
	"linkDirFullPath" => "HLS版本,檔案轉檔後產生連結要放置的資料夾路徑",
	"sendEmailAfterConvert" => "轉檔完成後是否發送Email",
	"notificationAfterConvert" => "轉檔完成後是否發佈通知",
	"compareSeconds" => "設定輸出檔和來源檔的時間，大於幾秒的時候判斷為轉檔失敗",
	"convertMaxFailedTimes" => "轉檔失敗時重試次數上限",
	"Conver Server List" => "轉檔伺服器列表",
);
?>