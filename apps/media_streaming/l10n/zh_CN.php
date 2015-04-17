<?php
$TRANSLATIONS = array(
	"Convert Wizard" => "格式精灵",
	"Convert" => "转档",
	"Select the output device" => "选择输出装置",
	"Output the phone size" => "输出成手机尺寸",
	"Output the pad size" => "输出成平板尺寸",
	"Output the TV size" => "输出成电视尺寸",
	"Enter" => "确定",
	"Free user limit " => "免费用户每日上限",
	" times each day" => "次",
	"Paid user no limit" => "付费用户无限制",
	"You had convert " => "您已转档",
	" times" => "次",
	"Through the format wizard completes video files will be placed here" => "透过格式精灵转完的影片档案都会放在此处",

	"Your" => "您的",
	"convert success" => "转档完成",
	"convert failed" => "转档失败",

	# 以下為串流播放回傳訊息
	"Streaming Play" => "	串流播放",
	"Media Streaming" => "影片处理中",
	"Media Preview Mode" => "影片预览模式",
	"File can not preview" => "档案无法预览播放",
	"Can not get server respond" => "无法取得伺服器回应",
	"Waiting Streaming Finish" => "转档处理中，请稍后再试",
	"Media Streaming only support for mp4 currently" => "目前串流播放只支援mp4格式档案",
	"you can transform by Convert Wizard" => "其它格式可透过格式精灵转档成mp4格式",
	"You can play it by MediaWizard" => "您可透过格式精灵转档后播放",
	"or upgrade to paied user" => "或升级为付费帐号",

	# 以下為轉檔精靈回傳的訊息，存放的值在oc_media_streaming_status
	"Sending Data" => "处理中",
	"Converting" => "转档中,转档完成后将通知您",
	"Waiting Convert" => "转档排程中,转档完成后将通知您",
	"Copy Output To Convert Target Failed" => "复制输出档时错误",
	"Convert Success" => "转档成功,已将档案放至格式的精灵",
	"Convert Times Over Limit" => "转档次数超过限制",
	"Same Target File Exists" => "已存在相同的输出档名",

	# 以下是串流時回傳的錯誤訊息，存放的值在oc_media_streaming_status
	"Source Size Over Limit" => "档案大小超过串流限制",

	# 以下是通用回傳的錯誤訊息，存放的值在oc_media_streaming_status
	"System Free Space Not Enough" => "系统剩余空间不足",
	"User Free Space Not Enough" => "您的剩余空间不足",
	"Source Not Exists" => "档案不存在",
	"Source Not Media Type" => "档案格式不支援",
	"Create Streaming Link Failed" => "影片连结失效",
	"Output File Not Exists" => "串流输出档不存在",
	"Convert Media Failed" => "影片转档失败",
	"Ask If Converting Failed" => "确认是否转档时失败",
	"Ask Conver Media Failed" => "要求转档失败",
	"Convert Server Busy" => "串流伺服器忙碌中，建议使用格式精灵转档",

	# Jmail轉檔通知內容
	"Your file" => "您的档案",
	"convert succeed" => "已经转档完成",
	"convert failed" => "未能转档完成",
	"Dear" => "您好",
	"convert succeed by Convert Wizard" => "已透过云端格式精灵转档成功",
	"convert failed by Convert Wizard" => "未能透过云端格式精灵转档完成",
	"Please check out your media in Convert Wizard" => "快到格式精灵检视影片吧",
	"Please check your source file and free space" => "请确认您的档案完整性及空间是否足够",
	"Thanks for you using" => "感谢您的使用",
	"Work Team" => "工作团队",

	# settings用到的內容
	"supporterEmail" => "技术支援者Email",
	"tomcatServer" => "Tomcat Server位址",
	"nginxServer" => "Nginx Server位址",
	"useHls" => "是否开启HLS(边转边播)",
	"useHlsConvert" => "是否开启HLS转档",
	"convertEnable" => "是否开启格式精灵",
	"limitSize" => "免费用户观看HLS时，档案大小限制",
	"convertDirPath" => "格式精灵输出的资料夹路径",
	"convertLimitTimes" => "免费用户每日转档次数限制",
	"streamingDocumentPath1_6" => "无HLS版本，串流伺服器路径",
	"streamingTempPath1_6" => "无HLS版本，影片的连结资料夹路径",
	"outputDirFullPath" => "HLS版本，档案转档后放置的资料夹路径",
	"linkDirFullPath" => "HLS版本,档案转档后产生连结要放置的资料夹路径",
	"sendEmailAfterConvert" => "转档完成后是否发送Email",
	"notificationAfterConvert" => "转档完成后是否发布通知",
	"compareSeconds" => "设定输出档和来源档的时间，大于几秒的时候判断为转档失败",
	"convertMaxFailedTimes" => "转档失败时重试次数上限",
	"Conver Server List" => "转档伺服器列表",
);
?>