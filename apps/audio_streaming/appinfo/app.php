<?php
$appId = 'audio_streaming';

OC::$CLASSPATH['OC_AudioStreaming'] = "apps/audio_streaming/lib/audio_streaming.php";
OC::$CLASSPATH['OC_AudioStreaming_Settings'] = "apps/audio_streaming/lib/audio_streaming_settings.php";

OC_Util::addScript($appId, $appId);

# 設定audio streaing 的所有config初始值
OC_AudioStreaming_Settings::setDefaultAudioStreamingConfigs();