<?php
/**
 * ownCloud - Media Convert plugin
 *
 * @author Caro Huang
 * @copyright 2013 www.u-sync.com
 *
 * 將影片檔轉檔並做串流播放
 * 現行的Meida Convert有1.6版2.0兩個版本
 *
 * V2.0 : 需要Tomcat Server做轉檔, 及Nginx server做轉檔串流播放(邊轉邊播)
 * 預設的轉檔輸出路徑為 /var/www/html/data/video-on-demand/private/md5(使用者ID)/輸出檔名(.mp4)，只允許server讀取
 * 預設用來播放的連結路徑為 /var/www/html/data/video-on-demand/public/md5(使用者ID)/輸出檔名(.mp4)
 * 轉完成後,將輸出檔複製到使用者的存放路徑(預設為/MediaWizard)
 * 並將相關的Streaming資料存到DB
 *
 * V1.6 : 需要Tomcat Server做轉檔
 * 直接將轉檔資料放到DB中, Tomcat Server隔一段時間會從DB讀取資料做轉檔
 *
 */

class OC_MediaConvert extends OC_MediaStreaming {
    static $requiredJmail = false;
    # 存放通用變數
    static $localUserId = '';
    static $localSourcePath = '';
    static $localOutputName = '';
    static $targetUserId = '';
    static $targetSourcePath = '';
    static $targetPath = '';
    static $targetOutputName = '';
    static $deviceType = '';
    static $insertTime = '';
    static $requestTime = '';
    static $serverIp = '';
    static $pid = '';
    static $startTime = '';
    static $sourceFootage = '';
    static $outputFootage = '';
    static $email = '';
    static $requestFailedTimes = '';
    static $checkFailedTimes = '';
    static $status = '';
    # 存放從DB抓取得device type列表
    static $deviceTypeArr = array();

    /**
     * 產生轉檔後要放的路徑資料夾
     * @return array(status,message)
     */
    static function createMediaConvertFolder() {
        # Create the folder if not exists
        $convertDirPath = self::convertDirPath();
        $convertDirFullPath = OC_LocalSystem::getLocalFullPath($convertDirPath);
        OC_Helper::createDirByFullPath($convertDirFullPath);

        # 指定格式精靈的存放資料夾只在 copy/move 的選單內才顯示
        OC_Files::addShowPath($convertDirPath, array(
            'FilesCopy',
            'FilesMv',
        ));

        # 自訂要顯示的目錄名稱
        $l = new OC_L10N(self::appId);
        $convertDirName = $l -> t('Convert Wizard');
        OC_Files::addMarkFileName($convertDirPath, $convertDirName, array(
            'FilesCopy',
            'FilesMv',
            'Breadcrumb',
        ));

        # 在webDav中，設定格式精靈資料夾無法更名/移動
        $webDav_FS_Plugin = new OC_Connector_Sabre_FileSystemPlugin();
        $webDav_FS_Plugin -> addRejectMovePath($convertDirPath);
    }

    /**
     * 取得所有轉檔資料
     * @return array
     */
    static function getConvertList() {
        $convertList = OC_MediaConvert_DB::getConvertItems();
        foreach ($convertList as $index => $convertItem) {
            # 轉換DB中的UTC時間轉為local時間
            $convertList[$index]['insert_time'] = OC_Helper::formatDateTimeUTCToLocal($convertItem['insert_time']);
            $convertList[$index]['start_time'] = OC_Helper::formatDateTimeUTCToLocal($convertItem['start_time']);
            # 將狀態(int)轉為文字敘述
            $status = $convertItem['status'];
            $convertList[$index]['status'] = self::streamingStatusArr($status);
        }
        return $convertList;
    }

    /**
     * 取得用戶一天內轉檔的次數, 2.0 和 1.6 版的convert DB table不一樣
     * @return int
     */
    static function getDailyConverTimes($userId) {
        $userId = OC_User::getUserByUserInput($userId);
        if (self::useHlsConvert()) {
            $conVertListInDay = OC_MediaConvert_DB::getConvertItemsInDayByUser($userId);
            return $dailyConverTimes = count($conVertListInDay);
        }
        return OC_MediaConvert_DB::getConvertItemsInDayByUser1_6($userId);
    }

    /**
     * 取得轉檔次數的訊息
     * @return array
     */
    static function showConvertTimesMess() {
        $l = new OC_L10N('media_streaming');
        $messArr = array();
        $paidSystemEnable = OC_Helper::paidSystemEnable();
        if ($paidSystemEnable) {
            $limitTimes = self::convertLimitTimes();
            $messArr[] = $l -> t('Free user limit ') . $limitTimes . $l -> t(' times each day') . ',' . $l -> t('Paid user no limit');
        }
        $convertTimes = self::getDailyConverTimes();
        $messArr[] = $l -> t('You had convert ') . $convertTimes . $l -> t(' times');
        return $messArr;
    }

    /**
     * 取得轉檔格式的相關資料
     * @param device type
     * @return array
     */
    static function getDeviceType($deviceType) {
        # 先從暫存變數中取值
        if (self::$deviceTypeArr[self::$deviceType]) {
            return self::$deviceTypeArr[self::$deviceType];
        }
        # 從DB中取得device type相關資料
        $deviceTypeItem = OC_MediaStreaming_DB::getDeviceType(self::$deviceType);
        # 將相關資料存放到暫存變數中
        self::$deviceTypeArr[$deviceType] = $deviceTypeItem;
        return $deviceTypeItem;
    }

    /**
     * 確認格式精靈是否轉檔完成，及後續動作 1.6版
     * DB table中的 notification: 0未通知, 1已通知
     */
    static function checkConvertJob1_6() {
        $convertItems = OC_MediaConvert_DB::getConvertItemsWithoutNotification1_6();
        if ($convertItems) {
            foreach ($convertItems as $key => $convertItem) {
                $userId = $convertItem['userName'];
                $registerDate = $convertItem['registerDate'];
                # DB中的fileName指的是檔案完整路徑
                $sourceFullPath = $convertItem['fileName'];
                $failDate = $convertItem['failDate'];
                $uploadFailDate = $convertItem['uploadFailDate'];
                $deviceType = $convertItem['deviceType'];
                $email = $convertItem['Email'];
                $notification = $convertItem['notification'];

                # 設置到通用變數，讓通知程式可以使用
                self::$targetUserId = $userId;
                self::$email = $email;

                $userDataDir = OC_LocalSystem::getDataDirFullPathByUserId($userId);
                $sourcePath = preg_replace('#' . $userDataDir . '#', '', $sourceFullPath);

                if ($failDate || $uploadFailDate) {
                    # 轉檔失敗
                    self::sendMessToUser(false);
                } else {
                    # 轉檔成功
                    self::sendMessToUser();
                }
                # 更新DB資料為已通知
                OC_MediaConvert_DB::updateConvertItemNotification1_6($userId, $registerDate, $deviceType);
            }
        }
    }

    /**
     * 由crontab發動，確認格式精靈是否轉檔完成及後續動作
     */
    static function checkConvertJob() {
        # 將今天之前已經轉好的資料移到log
        self::moveConvertListBeforeToday();

        # 找出所有轉檔中的資料
        $converting = self::converting;
        $convertItems = OC_MediaConvert_DB::getConvertItemsByStatus($converting);
        if ($convertItems) {
            foreach ($convertItems as $key => $convertItem) {
                self::$localUserId = $convertItem['user_id'];
                self::$localSourcePath = $convertItem['source_path'];
                self::$localOutputName = $convertItem['output_name'];
                self::$targetUserId = $convertItem['target_user_id'];
                self::$targetSourcePath = $convertItem['target_source_path'];
                self::$targetPath = $convertItem['target_path'];
                self::$targetOutputName = $convertItem['target_output_name'];
                self::$deviceType = $convertItem['device_type'];
                self::$insertTime = $convertItem['insert_time'];
                self::$serverIp = $convertItem['server_ip'];
                self::$pid = $convertItem['pid'];
                self::$startTime = $convertItem['start_time'];
                self::$checkFailedTimes = $convertItem['check_failed_times'];
                self::$status = $convertItem['status'];
                self::$email = $convertItem['email'];

                # 防呆，將取得的轉檔狀態寫到轉檔server DB
                OC_MediaConvertServer::updateConvertServer(self::$pid, self::$startTime, self::$status, self::$serverIp);
                // OC_MediaConvertServer::updateConvertServerStatus($converting, self::$serverIp);
                # 開始確認轉檔是否完成
                self::checkConvert();
            }
        }
    }

    /**
     * 確認格式精靈是否轉檔完成及後續動作
     */
    private static function checkConvert() {
        # 如果有現成的streaming可用，則會做後續相關處理；沒有的話則向Tomcat確認轉檔
        $result = self::ifStreamingCanUseAndReturnStatus();
        if ($result === false) {
            # 沒有現成的streaming可用，向Tomcat確認轉檔狀態
            $result = self::curlCheckConvert();
        }
        if ((int)$result === self::converting) {
            return;
        }
        # 如果回傳的是轉檔完成
        if ((int)$result === self::convert_success) {
            # 更新轉檔server為待轉檔
            OC_MediaConvertServer::setConvertServerToDefault(self::$serverIp);
            # copy output file 到格式精靈底下
            if (!self::copyOutputToConvertTargetPath()) {
                # 刪除轉檔相關檔案(記得先刪除link才能刪file)
                self::deleteStreamingLink(self::$targetOutputName);
                self::deleteOutputMedia(self::$targetOutputName);
                # 更新轉檔資料狀態並移到log
                $error = self::copy_output_to_convert_target_failed;
                self::moveConvertToLog($error);
                # 通知user轉檔失敗
                self::sendMessToUser(false, $error);
                return;
            }
            # 產生target output file的連結
            if (!is_string(self::createLink(self::$targetOutputName))) {
                # 刪除轉檔相關檔案
                self::deleteOutputMedia(self::$targetOutputName);
                # 更新轉檔資料狀態並移到log
                $error = self::create_streaming_link_failed;
                self::moveConvertToLog($error);
                # 通知user轉檔失敗
                self::sendMessToUser(false, $error);
                return;
            }
            # 更新轉檔資料為轉檔完成
            OC_MediaConvert_DB::updateConvertStatus($result, self::$localOutputName, self::$targetOutputName, self::$insertTime);
            # 將轉檔資料寫到串流資料(讓user點選格式精靈底下的檔案時可以播放)
            OC_MediaStreaming_DB::insertStreaming(self::$targetUserId, self::$targetPath, self::$deviceType, self::$targetOutputName);
            # 將轉檔server資料寫到串流資料
            OC_MediaStreaming_DB::updateStreamingByOutputName(self::$serverIp, self::$pid, self::$startTime, null, self::$targetOutputName);
            OC_MediaStreaming_DB::updateStreamingFootageByOutputName(self::$sourceFootage, self::$outputFootage, self::$targetOutputName);
            # 更新串流資料為轉檔成功
            OC_MediaStreaming_DB::updateStreamingStatusByOutputName(self::convert_success, self::$targetOutputName);

            # 通知user轉檔成功
            self::sendMessToUser();
            return;
        }
        # 剩下的為失敗狀態
        if ((int)$result === self::ask_if_converting_failed) {
            # 計數確認失敗次數
            self::$checkFailedTimes++;
            OC_MediaConvert_DB::updateConvertCheckFailedTimes(self::$checkFailedTimes, self::$localOutputName, self::$targetOutputName, self::$insertTime);
            if (self::$checkFailedTimes > self::convertMaxFailedTimes()) {
                # 刪除轉檔相關檔案
                self::deleteOutputMedia(self::$targetOutputName);
                # 更新轉檔server為待轉檔
                OC_MediaConvertServer::setConvertServerToDefault(self::$serverIp);
                # 更新轉檔資料狀態並移到log
                self::moveConvertToLog($result);
            } else {
                # 還未超過錯誤次數上限，所以不執行之後的動作
                return;
            }
        }
        # 轉檔server為錯誤狀態
        if ((int)$result === self::convert_server_error) {
            # 更新轉檔server狀態(但不將轉檔資料移到log，這樣下次才能繼續轉檔)
            OC_MediaConvertServer::updateConvertServerStatus($result, self::$serverIp);
            return;
        }

        # 其它失敗狀態
        # 刪除轉檔相關檔案
        self::deleteOutputMedia(self::$targetUserId, self::$targetOutputName);
        # 更新轉檔server為待轉檔
        OC_MediaConvertServer::setConvertServerToDefault(self::$serverIp);
        # 更新轉檔資料狀態並移到log
        self::moveConvertToLog($result);

        # 通知user轉檔失敗
        self::sendMessToUser(false);
    }

    /**
     * 由crontab發動，要求轉檔
     */
    static function askConvertJob() {
        # 找出空閒的轉檔server
        $waitingConvert = self::waiting_convert;
        $convertServerList = OC_MediaConvertServer::getConvertServerListByStatus($waitingConvert);
        $convertServerCount = count($convertServerList);
        # 找出等待轉檔的資料
        $convertList = OC_MediaConvert_DB::getConvertItemsByStatus($waitingConvert);
        if ($convertList) {
            for ($i = 0; $i < $convertServerCount; $i++) {
                if (isset($convertList[$i])) {
                    # 取得轉檔server ip
                    $convertServerItem = $convertServerList[$i];
                    self::$serverIp = $convertServerItem['server_ip'];
                    # 取得排程中待轉檔的資料，並設置到通用變數
                    $convertItem = $convertList[$i];
                    self::$localUserId = $convertItem['user_id'];
                    self::$localSourcePath = $convertItem['source_path'];
                    self::$localOutputName = $convertItem['output_name'];
                    self::$targetUserId = $convertItem['target_user_id'];
                    self::$targetSourcePath = $convertItem['target_source_path'];
                    self::$targetOutputName = $convertItem['target_output_name'];
                    self::$deviceType = $convertItem['device_type'];
                    self::$insertTime = $convertItem['insert_time'];
                    self::$email = $convertItem['email'];
                    self::$requestFailedTimes = $convertItem['request_failed_times'];
                    # 確認檔案是否存在及剩餘空間是否足夠
                    $return = self::checkFreeSpace(self::$localUserId, self::$localSourcePath, self::$targetUserId);
                    # 空間不足則發送轉檔錯誤訊息，並執行下一個loop
                    if ($return !== true) {
                        self::sendMessToUser(false, $return);
                        # 更新轉檔資料狀態並移到log
                        self::moveConvertToLog($return);
                        continue;
                    }
                    # 開始要求轉檔
                    self::askConvert();
                }
            }
        }

    }

    /**
     * 開始要求轉檔，及之後的相關處理
     * @param user id, source path, output name, device type
     */
    private static function askConvert() {
        # 更新「第一次要求轉檔的時間」
        OC_MediaConvert_DB::updateConvertRequestTime(self::$localOutputName, self::$targetOutputName, self::$insertTime);
        # 防呆，刪除link和output file(記得先刪除link才能刪file)
        self::deleteStreamingLink(self::$localOutputName);
        self::deleteOutputMedia(self::$localOutputName);
        # 要求Tomcat Server轉檔
        $result = self::curlAskConvert();
        # 如果向Tomcat Server要求轉檔，回傳的是轉檔中
        if ((int)$result === self::converting) {
            # 更新轉檔伺服器資料
            OC_MediaConvertServer::updateConvertServer(self::$pid, self::$startTime, $result, self::$serverIp);
            # 更新轉檔資料
            OC_MediaConvert_DB::updateConvert(self::$serverIp, self::$pid, self::$startTime, null, self::$localOutputName, self::$targetOutputName, self::$insertTime);
            # 更新轉檔資料的狀態
            OC_MediaConvert_DB::updateConvertStatus($result, self::$localOutputName, self::$targetOutputName, self::$insertTime);
        } else {
            # Tomcat Server沒回應或格式錯誤
            if ((int)$result === self::ask_convert_media_failed) {
                # 計數要求失敗次數
                self::$requestFailedTimes++;
                OC_MediaConvert_DB::updateConvertRequestFailedTimes(self::$requestFailedTimes, self::$localOutputName, self::$targetOutputName, self::$insertTime);
                if (self::$requestFailedTimes > self::convertMaxFailedTimes()) {
                    # 更新轉檔資料狀態並移到log
                    self::moveConvertToLog($result);
                }
            }
            # 轉檔server為錯誤狀態
            if ((int)$result === self::convert_server_error) {
                # 更新轉檔server狀態(但不將轉檔資料移到log，這樣下次才能繼續轉檔)
                OC_MediaConvertServer::updateConvertServerStatus($result, self::$serverIp);
            }
            # 通知user
            self::sendMessToUser(false, $result);
        }
    }

    /**
     * 格式精靈前置執行(使用者執行格式精靈後的判斷流程)
     * @param 目錄,檔案名稱,指定輸出格式
     * @return array(success or error,message)
     */
    static function convertMedia($dir, $fileName, $deviceType = 'phone') {
        # 使用者在頁面看到的路徑
        $sourcePath = OC_Helper::pathForbiddenChar($dir . '/' . $fileName);
        # 是否有開啟HLS轉檔
        $useHlsConvert = self::useHlsConvert();
        # 寫入DB的時間
        self::$insertTime = OC_Helper::formatDateTimeLocalToUTC();
        # 轉檔格式
        self::$deviceType = $deviceType;
        # 點選轉檔的使用者帳號
        self::$targetUserId = OC_User::getUser();
        # 點選轉檔的使用 者，當時看到的檔案路徑
        self::$targetSourcePath = $sourcePath;
        # 此targetPath即為轉檔後要放在格式精靈資料夾底下的檔案路徑
        self::$targetPath = self::targetPath(self::$targetUserId, $sourcePath, $deviceType);
        # 轉檔完成後，之後要設置的輸出檔名稱
        self::$targetOutputName = self::outputName(self::$targetUserId, self::$targetPath, $deviceType);
        self::$email = OC_User::getUserEmail();

        # 已存在相同的輸出檔案在格式精靈底下
        if (self::ifSameTarget()) {
            return self::returnMessArr('error', self::same_target_file_exists);
        }
        # 取得轉檔真正所屬的user id, source path, 及要輸出的檔名
        self::$localUserId = OC_LocalSystem::getLocalUserIdByPath($sourcePath);
        self::$localSourcePath = OC_LocalSystem::getLocalPath($sourcePath);
        self::$localOutputName = self::outputName(self::$localUserId, self::$localSourcePath, $deviceType);
        # 如果有開啟HLS轉檔，則先判斷是否已有待轉檔/轉檔中資料，否則的話新增convert資料
        if ($return = self::checkConvertDataAndNew()) {
            return $return;
        }
        # 確認檔案是否存在及剩餘空間是否足夠
        $return = self::checkFreeSpace(self::$localUserId, self::$localSourcePath, self::$targetUserId);
        if ($return !== true) {
            return self::setConvertError($return);
        }
        # 確認是否為影片
        if (!self::isMedia($sourcePath)) {
            return self::setConvertError(self::source_not_media_type);
        }

        # 判斷user是否能轉檔
        if ($useHlsConvert) {
            $return = self::ifUserCanConvert(self::$targetUserId);
            if ($return === true) {
                return self::setConvertMediaAndReturnMess();
            }
            # user不能轉檔，將convert移到log並回傳訊息
            return self::setConvertError($return);
        }
        return self::setConvertMediaAndReturnMess1_6($dir, $fileName, $deviceType);
    }

    /**
     * rename轉檔資料
     * 此步驟的目的是，當檔案正在待轉檔或是轉檔中的時候
     * 如果user移動或更名來源檔(甚至跨user移動)
     * 可以依據新的user id和ouput name取得新的output file path
     * 這樣子當轉檔完成後，才能正確的執行後續的動作
     * @param old user id, old source path, new user id, new source path
     */
    static function renameConvert($oldUserId, $oldSourcePath, $newUserId, $newSourcePath) {
        # 先取得該檔案的所有轉檔資料(不區分device type)
        $convertItems = OC_MediaConvert_DB::getConvertItemsByUserIdAndSourcePath($oldUserId, $oldSourcePath);
        if (count($convertItems)) {
            foreach ($convertItems as $index => $convertItem) {
                $oldOutputName = $convertItem['output_name'];
                $targetOutputName = $convertItem['target_output_name'];
                $deviceType = $convertItem['device_type'];
                $insertTime = $convertItem['insert_time'];
                $status = $convertItem['status'];

                # 取得rename後，檔案的output name
                $newOutputName = self::outputName($newUserId, $newSourcePath, $deviceType);
                # 如果轉檔資料狀態是「轉檔中」，則需要將output file更名
                # 如果是待轉檔，則代表還沒有output file，只需要更新DB資料
                # 如果是轉檔完成，則代表該output file已轉為streaming用的outpu file，不需要動作
                if ($status == self::converting) {
                    # rename輸出檔
                    self::renameOutputMedia($oldOutputName, $newOutputName);
                }
                # 如果轉檔資料狀態是「待轉檔」或「轉檔中」
                if ($status == self::waiting_convert || $status == self::converting) {
                    # 更新轉檔資料
                    OC_MediaConvert_DB::updateConvertForRename($newUserId, $newSourcePath, $newOutputName, $oldOutputName, $targetOutputName, $insertTime);
                }
            }
        }
    }

    /**
     * delete轉檔資料
     * 當轉檔資料為待轉檔時，則更新DB資料為「刪除」
     * 轉檔中的時候， 更新DB資料為「刪除」並刪除相關的轉檔檔案
     * @param user id, source path
     */
    static function delConvert($userId, $sourcePath) {
        # 先取得該檔案的所有轉檔資料(不區分device type)
        $convertItems = OC_MediaConvert_DB::getConvertItemsByUserIdAndSourcePath($userId, $sourcePath);
        if (count($convertItems)) {
            foreach ($convertItems as $index => $convertItem) {
                $outputName = $convertItem['output_name'];
                $targetOutputName = $convertItem['target_output_name'];
                $insertTime = $convertItem['insert_time'];
                $serverIp = $convertItem['server_ip'];
                $status = $convertItem['status'];

                if ($status == self::converting) {
                    # delete輸出檔
                    self::deleteOutputMedia($outputName);
                }

                # 如果轉檔資料狀態是「待轉檔」或「轉檔中」
                if ($status == self::waiting_convert || $status == self::converting) {
                    # 更新轉檔資料為刪除狀態
                    OC_MediaConvert_DB::updateConvertStatus(self::convert_deleted, $outputName, $targetOutputName, $insertTime);
                    # 更新轉檔server為初始狀態
                    OC_MediaConvertServer::setConvertServerToDefault($serverIp);
                }
            }
        }
    }

    /**
     * 確認是否有已有轉檔中的資料,有的話回傳訊息，沒有的話就寫入轉檔資料
     * 此步驟為預先進入轉檔排程，在後面流程處理發生錯誤時可寫入錯誤狀態
     * @return array
     */
    private static function checkConvertDataAndNew() {
        $useHlsConvert = self::useHlsConvert();
        if ($useHlsConvert) {
            $canInsertConvert = false;
            $convertItems = OC_MediaConvert_DB::getConvertItemsByOutputName(self::$localOutputName, self::$targetOutputName);
            if ($convertItems) {
                foreach ($convertItems as $index => $convertItem) {
                    $convertStatus = $convertItem['status'];
                    # 如果有「待轉檔」或「轉檔中」的資料
                    if ($convertStatus == self::converting || $convertStatus == self::waiting_convert) {
                        return self::returnMessArr('success', (int)$convertStatus);
                    }
                }
                $canInsertConvert = true;
            } else {
                $canInsertConvert = true;
            }
            if ($canInsertConvert) {
                # 寫入轉檔資料，預設DB資料為「待轉檔」
                if (!OC_MediaConvert_DB::insertConvert(self::$localUserId, self::$localSourcePath, self::$localOutputName, self::$targetUserId, self::$targetSourcePath, self::$targetPath, self::$targetOutputName, self::$deviceType, self::$insertTime, self::$email)) {
                    return self::returnMessArr('error', self::convert_media_failed);
                }
            }
        }
    }

    /**
     * 確認是否有相同的target file name在格式精靈資料夾底下
     */
    private static function ifSameTarget() {
        $targetFullPath = OC_LocalSystem::getFullPathByUserId(self::$targetUserId, self::$targetPath);
        if (file_exists($targetFullPath)) {
            return true;
        }
    }

    /**
     * 錯誤訊息寫到log，並回傳錯誤訊息
     * @param 狀態,輸出檔名
     * @return array('error',error message)
     */
    private static function setConvertError($status) {
        # 有開啟HLS轉檔，則將DB資料移到log
        if (self::useHlsConvert()) {
            self::moveConvertToLog($status);
        }
        return self::returnMessArr('error', $status);
    }

    /**
     * 回傳轉檔的輸出檔名
     * @param 使用者ID,來源路徑,輸出格式
     * @return string
     */
    private static function outputName($userId, $sourcePath, $deviceType = 'phone') {
        if (self::useHlsConvert()) {
            # 新版streaming 的output name做為轉檔後輸出檔的檔名及後續link的檔名
            return $outputName = md5($userId . $sourcePath) . "_$deviceType.mp4";
        }
        # 舊版的streaming的output name即為link的名稱
        return $outputName = md5($userId . $sourcePath) . ".mp4";
    }

    /**
     * 判斷格式精靈資料並回傳訊息1.6版
     * @param 目錄,檔案名稱,指定輸出格式
     * @return array(success or error,message)
     */
    private static function setConvertMediaAndReturnMess1_6($dir, $fileName, $deviceType) {
        $sourcePath = OC_Helper::pathForbiddenChar($dir . '/' . $fileName);
        $userId = OC_LocalSystem::getLocalUserIdByPath($sourcePath);
        $sourcePath = OC_LocalSystem::getLocalPath($sourcePath);

        # 確認是否轉檔中
        $sourceFullPath = OC_LocalSystem::getLocalFullPath($sourcePath);
        $converterItem = OC_MediaConvert_DB::getConverterItem1_6($userId, $sourceFullPath, $deviceType);
        if ($converterItem) {
            return self::returnMessArr('success', self::converting);
        }
        # 寫入convert資料,並回傳訊息
        return self::setConvertData1_6($userId, $sourceFullPath, $deviceType);
    }

    /**
     * 寫入轉檔資料到DB,Tomcat Server會自動抓取DB資料做轉檔處理
     * @param 使用者ID,,來源路徑,來源完整路徑,輸出格式,指定轉檔後的檔案路徑
     * @return array(status,message)
     */
    private static function setConvertData1_6($userId, $sourceFullPath, $deviceType) {
        $deviceTypeItem = self::getDeviceType($deviceType);
        $frameRate = $deviceTypeItem['frame_rate'];
        $frameSize = $deviceTypeItem['frame_size'];
        $videoCodec = $deviceTypeItem['video_codec'];
        $bitRate = $deviceTypeItem['bit_rate'];
        $userDataDirPath = OC_LocalSystem::getDataDirFullPathByUserId($userId);
        $destFolder = OC_Helper::pathForbiddenChar($userDataDirPath . self::convertDirPath());
        $email = OC_User::getUserEmail($userId);

        $insertConvert = OC_MediaConvert_DB::insertConvert1_6($userId, $sourceFullPath, $destFolder, $deviceType, $frameRate, $frameSize, $videoCodec, $bitRate, $email);
        if ($insertConvert) {
            # 寫入成功,回傳成功訊息
            return self::returnMessArr('success', self::converting);
        }
        # 寫入失敗,回傳錯誤訊息
        return self::returnMessArr('error', self::ask_convert_media_failed);
    }

    /**
     * 判斷格式精靈資料並回傳訊息
     * 先判斷是否有現成的streaming資料可利用
     * 1. 資料為轉檔成功的話，則複製streaming資料，轉為convert的串流資料
     * 2. 資料為轉檔中，則向Tomcat Server確認，再依回傳的狀態處理
     * 3. 資料為其它狀態，則乎略Streaming資料(convert資料依然為「待轉檔」)
     * 4. 沒有streaming資料，convert資料依然為「待轉檔」
     * @param user id, source path, device type
     * @return array(success or error,message)
     */
    private static function setConvertMediaAndReturnMess() {
        # 確認是否已存在streaming資料可以利用
        $result = self::ifStreamingCanUseAndReturnStatus();
        if ($result) {
            return self::returnMessArr('success', $result);
        }
        # 沒有streaming資料，或streaming狀態不為轉檔中或轉檔完成，則convert資料保持原狀(待轉檔)
        return self::returnMessArr('success', self::waiting_convert);
    }

    /**
     * 判斷是否有現成的streaming資料，有的話則進行處理
     * 1. 資料為轉檔成功的話，則複製streaming資料，轉為convert的串流資料
     * 2. 資料為轉檔中，則向Tomcat Server確認，再依回傳的狀態處理
     * 3. 資料為其它狀態
     */
    private static function ifStreamingCanUseAndReturnStatus() {
        # 確認是否已存在streaming資料可以利用
        if ($streamingItem = OC_MediaStreaming_DB::getStreamingByOutputName(self::$localOutputName)) {
            $status = $streamingItem['status'];
            # 串流資料為轉檔成功，則複製現有的streaming資料，轉為convert的串流資料
            if ($status == self::convert_success) {
                if (self::coverStreamingToConvertData($streamingItem) === true) {
                    return self::convert_success;
                }
            }
            # 串流資料為轉檔中，再次向Tomcat Server確認streaming轉檔是否完成
            if ($status == self::converting) {
                $serverIp = $streamingItem['server_ip'];
                $pid = $streamingItem['pid'];
                $startTime = $streamingItem['start_time'];
                $userId = $streamingItem['user_id'];
                $sourcePath = $streamingItem['source_path'];
                $outputName = $streamingItem['output_name'];
                # 向Tomcat Server確認是否轉檔結束(在輸出資料夾)
                $result = self::curlCheckIfStreamingDone($serverIp, $pid, $startTime, $userId, $sourcePath, $outputName);
                # 向Tomcat Server確認後，回傳「轉檔成功」，則複製現有的streaming資料，轉為convert的串流資料
                if ($result == self::convert_success) {
                    # 開始產生streaming連結
                    $linkResult = self::createLink($outputName);
                    # streaming連結產生失敗
                    if ($linkResult == self::create_streaming_link_failed || $linkResult == self::output_file_not_exists) {
                        # 刪除streaming資料
                        self::deleteOutputMedia($outputName);
                        # 更新streaming資料並移到log中
                        self::moveStreamingToLog($status, $outputName);
                    }
                    # streaming連結產生成功,更新streaming狀態為「轉檔成功」
                    if (OC_MediaStreaming_DB::updateStreamingStatusByOutputName($result, $outputName)) {
                        # 將取得的Streaming資料(array)，更新為轉檔成功
                        $streamingItem['status'] = self::convert_success;
                    }
                    # 複製streaming相關資料到convert資料
                    if (self::coverStreamingToConvertData($streamingItem) === true) {
                        return self::convert_success;
                    }
                }
                # 向Tomcat Server確認後，回傳「轉檔中」，或是等待轉檔完成(已經轉好，但檔案還在暫存資料區)
                if ($result == self::converting || $result == self::waiting_streaming_finish) {
                    if (self::covertStreamingToConvertDb($streamingItem, $result) === true) {
                        return self::converting;
                    }
                }
                # 向Tomcat Server確認後，為其它狀態
            }
        }
        return false;
    }

    /**
     * 複製現有的串流資料，轉為格式精靈要用的的串流檔案及DB資料
     * @param 串流資料(array)
     * @return bool
     */
    private static function coverStreamingToConvertData($streamingItem) {
        $serverIp = $streamingItem['server_ip'];
        $pid = $streamingItem['pid'];
        $startTime = $streamingItem['start_time'];
        $hlsUrl = $streamingItem['hls_url'];
        $sourceFootage = $streamingItem['source_footage'];
        $outputFootage = $streamingItem['output_footage'];
        $status = $streamingItem['status'];
        # 複製streaming相關資料,成功會回傳string(link的路徑),失敗的話會回傳int(錯誤訊息的代號)
        $copyStreaming = self::copyStreaming(self::$localUserId, self::$localSourcePath, self::$targetUserId, self::$targetPath, self::$deviceType);
        if (!is_string($copyStreaming)) {
            return false;
        }
        # 複製streaming的output file到格式精靈資料夾底下
        if (!self::copyOutputToConvertTargetPath()) {
            return false;
        }
        # 將Streaming資料寫入Convert資料中
        if (!self::covertStreamingToConvertDb($streamingItem, $status)) {
            return false;
        }
        return true;
    }

    /**
     * 將Streaming資料寫入Convert資料中
     * @return bool
     */
    private static function covertStreamingToConvertDb($streamingItem, $status) {
        $serverIp = $streamingItem['server_ip'];
        $pid = $streamingItem['pid'];
        $startTime = $streamingItem['start_time'];
        $hlsUrl = $streamingItem['hls_url'];
        $sourceFootage = $streamingItem['source_footage'];
        $outputFootage = $streamingItem['output_footage'];
        # 如果狀態是等待轉檔完成，則改為待轉檔，再寫入轉檔DB
        if ($status == self::waiting_streaming_finish) {
            $status = self::waiting_convert;
        }
        $updateConvert = OC_MediaConvert_DB::updateConvert($serverIp, $pid, $startTime, $hlsUrl, self::$localOutputName, self::$targetOutputName, self::$insertTime);
        $updateConvertFootage = OC_MediaConvert_DB::updateConvertFootage($sourceFootage, $outputFootage, self::$localOutputName, self::$targetOutputName, self::$insertTime);
        $updateConvertStatus = OC_MediaConvert_DB::updateConvertStatus($status, self::$localOutputName, self::$targetOutputName, self::$insertTime);
        if ($updateConvert && $updateConvertFootage && $updateConvertStatus) {
            OC_Log::write('covertStreamingToConvertDb', '轉換DB資料成功', 1);
            return true;
        }
        return false;
    }

    /**
     * 取得格式精靈轉檔後所放的檔案路徑
     * @param user id, file path, device type, if return full path
     * @return string
     */
    private static function targetPath($userId, $filePath, $deviceType, $forFullPath = false) {
        # 以filePath為輸出的檔名
        $filePath = trim($filePath, '/');
        $fileName = preg_replace('#' . preg_quote('/') . '#', '_', $filePath);
        $fileName = pathinfo($fileName, PATHINFO_FILENAME);
        $targetPath = self::convertDirPath() . '/' . $fileName . "_$deviceType.mp4";
        if (!$forFullPath) {
            return $targetPath;
        }
        $userDataDirPath = OC_LocalSystem::getDataDirFullPathByUserId($userId);
        $targetPath = OC_Helper::pathForbiddenChar($userDataDirPath . '/' . $targetPath);
        return $targetPath;
    }

    /**
     * 將轉檔後的mp4輸出檔，copy到格式精靈資料夾
     * @return bool
     */
    private static function copyOutputToConvertTargetPath() {
        $outputFullPath = self::outputFullPath(self::$targetOutputName);
        $targetFullPath = OC_LocalSystem::getFullPathByUserId(self::$targetUserId, self::$targetPath);
        if (file_exists($outputFullPath) && $targetFullPath && copy($outputFullPath, $targetFullPath)) {
            # 輸出檔權限為644,所以複製的target要改為755
            @chmod($targetFullPath, 0755);
            @chown($targetFullPath, 'apache');
            return true;
        }
        return false;
    }

    /**
     * 將今天之前的轉檔資料搬到oc_media_streaming_convert_log
     */
    private static function moveConvertListBeforeToday() {
        OC_MediaConvert_DB::copyConvertsToLogBeforeToday();
        OC_MediaConvert_DB::delConvertsBeforeToday();

    }

    /**
     * 更新Streaming convert的狀態，並搬到oc_media_streaming_convert_log
     * @param status
     */
    private static function moveConvertToLog($status) {
        if (OC_MediaConvert_DB::updateConvertStatus($status, self::$localOutputName, self::$targetOutputName, self::$insertTime)) {
            OC_MediaConvert_DB::copyConvertToLog(self::$localOutputName, self::$targetOutputName, self::$insertTime);
            OC_MediaConvert_DB::delConvert(self::$localOutputName, self::$targetOutputName, self::$insertTime);
        }
    }

    /**
     * 通知user轉檔訊息
     * @param if for success
     */
    private static function sendMessToUser($forSuccess = true, $error = null) {
        if (self::sendEmailAfterConvert()) {
            if ($error) {
                # 將error代碼轉為訊息
                $error = self::streamingStatusArr($error);
            }
            self::sendJmail($forSuccess, $error);
        }
        if (self::notificationAfterConvert()) {
            self::addNotification($forSuccess);
        }
    }

    /**
     * 新增訊息到通知中心
     * @param if for success
     */
    private static function addNotification($forSuccess = true) {
        if (OC_App::isEnabled('notification')) {
            $targetName = basename(self::$targetPath);
            $message = '<Your>[' . $targetName . ']';
            if ($forSuccess) {
                $message .= '<convert success>';
            } else {
                $message .= '<convert failed>';
            }
            $link = '/files/index.php?dir=' . self::convertDirPath();
            OC_Notification::addNotification('media_streaming', 'Convert Wizard', self::$targetUserId, $message, $link);
        }
    }

    /**
     * 利用Jmail寄發通知信
     * @param if for success,error message
     */
    private static function sendJmail($forSuccess = true, $error = null) {
        if (self::$email) {
            $l = new OC_L10N(OC_MediaStreaming::appId);
            $targetName = basename(self::$targetPath);
            $emailSubject = '';
            $emailBody = $l -> t("Dear") . " " . self::$targetUserId . "：\n\n";

            if ($forSuccess) {
                $emailSubject .= $l -> t("Your file") . "：" . $targetName . " " . $l -> t("convert succeed");
                $emailBody .= "    " . $l -> t("Your file") . "：" . $targetName . " " . $l -> t("convert succeed by Convert Wizard") . "!\n\n";
                $emailBody .= "    " . $l -> t("Please check out your media in Convert Wizard") . "!\n\n";
            } else {
                $emailSubject = $l -> t("Your file") . "：" . $targetName . " " . $l -> t("convert failed");
                $emailBody .= "    " . $l -> t("Your file") . "：" . $targetName . " " . $l -> t("convert failed by Convert Wizard") . "!\n\n";
                # 如果有錯誤訊息的話
                if ($error) {
                    $emailBody .= "    " . $l -> t("Error") . ":" . $l -> t($error) . "\n\n";
                }
                $emailBody .= "    " . $l -> t("Please check your source file and free space") . "\n\n";

            }
            $emailBody .= "    " . $l -> t("Thanks for you using") . "\n\n";
            $emailBody .= "            " . $l -> t("Work Team") . ' - ' . OC_Helper::siteTitle();

            $adminEmail = OC_Config::getValue('adminEmail', '');
            $adminName = $l -> t("Convert Wizard");
            OC_Util::sendJmail($adminEmail, $adminName, self::$email, $emailSubject, $emailBody);
        }
    }

    /**
     * 判斷user是否能轉檔
     * @param user id
     */
    private static function ifUserCanConvert($userId) {
        $paidSystemEnable = OC_Helper::paidSystemEnable();
        # 付費機制有打開
        if ($paidSystemEnable) {
            $limitTimes = self::convertLimitTimes();
            $converTimes = self::getDailyConverTimes($userId);
            # 因為在這邊確認user的轉檔次數之前，已經先把該筆轉檔資料寫入DB，所以取得的筆數會是實際轉檔次數+1
            if (!OC_User::isPaidUser($userId) && $converTimes > $limitTimes) {
                return self::convert_times_over_limit;
            }
        }
        return true;
    }

    /**
     * 向Tomcat server確認檔案是否還在轉檔中
     * @return convert_server_error,ask_if_converting_failed or convert_media_failed or converting or convert_success
     */
    private static function curlCheckConvert() {
        $serverIp = self::$serverIp;
        $pid = self::$pid;
        $startTimeForTomcat = self::coverTimeToTomcatFormat(self::$startTime);
        $sourceFullPath = OC_LocalSystem::getFullPathByUserId(self::$localUserId, self::$localSourcePath);
        $sourceFullPath = urlencode($sourceFullPath);
        $outputFullPath = self::outputFullPath(self::$targetOutputName);
        $toURL = "http://" . self::TomcatServer() . ":8080/LiveStreamTransCode/MediaProgressMain";
        $toURL .= "?serverIp=$serverIp&pid=$pid&startTime=$startTimeForTomcat&sourcePath=$sourceFullPath&outputPath=$outputFullPath";
        $result = self::curlToConvertServer($toURL);
        $result = self::coverCheckConvertResult($result);
        if ($result == self::ask_if_converting_failed || $result == self::convert_media_failed) {
            # email給管理者
            self::sendMailToAdmin($result, self::$targetUserId, self::$targetSourcePath, $serverIp, $pid, self::$startTime);
        }
        return $result;
    }

    /**
     * 將Tomcat回傳的Result轉為陣列參數
     * 轉檔server有錯誤，會回傳：「error server」
     * 轉檔中，回傳的值類似：sourceFootage:0;outputFootage:0
     * 指定的source或output不存在，回傳的值類似：sourceFootage:-1;outputFootage:-1
     * 轉檔成功，回傳的值類似：sourceFootage:01:12:34;outputFootage:01:12:56
     * @param Tomcat回傳的string訊息
     * @return convert_server_error/ask_if_converting_failed/convert_media_failed/ converting/convert_success/output_not_exists/output_not_exists
     */
    private static function coverCheckConvertResult($result) {
        if ($result) {
            if (preg_match('/error/i', $result)) {
                return self::convert_server_error;
            }

            $resultArr = explode(';', $result);
            $sourceFootage = null;
            $outputFootage = null;
            foreach ($resultArr as $val) {
                if (stripos($val, 'sourceFootage') === 0) {
                    $sourceFootage = preg_replace('#' . preg_quote('sourcefootage:') . '#', '', strtolower($val));
                    if ($sourceFootage != '-1') {
                        $sourceFootage = OC_Helper::formatTimeToSeconds($sourceFootage);
                    }
                } else if (stripos($val, 'outputFootage') === 0) {
                    $outputFootage = preg_replace('#' . preg_quote('outputfootage:') . '#', '', strtolower($val));
                    if ($outputFootage != '-1') {
                        $outputFootage = OC_Helper::formatTimeToSeconds($outputFootage);
                    }
                }
            }

            # 拆解完之後都有值，代表回傳的格式正確
            if ($sourceFootage !== null && $outputFootage !== null) {
                $sourceFootage = (int)$sourceFootage;
                $outputFootage = (int)$outputFootage;
                # 設置Tomcat回傳的資料
                self::$sourceFootage = $sourceFootage;
                self::$outputFootage = $outputFootage;
                if ($sourceFootage < 0) {
                    # 指定的來源檔不存在
                    return self::source_not_exists;
                }
                if ($outputFootage < 0) {
                    # 指定的輸出檔不存在(這邊也有可能是指暫存檔)
                    return self::output_not_exists;
                }
                if ($sourceFootage === 0 && $outputFootage === 0) {
                    # 取出的來源和輸出檔影片時間都為0
                    return self::converting;
                }
                if ($sourceFootage && $outputFootage) {
                    # 將回傳的影片時間寫入轉檔資料
                    OC_MediaConvert_DB::updateConvertFootage($sourceFootage, $outputFootage, self::$localOutputName, self::$targetOutputName, self::$insertTime);
                    if (self::compareFootage($sourceFootage, $outputFootage)) {
                        return self::convert_success;
                    }
                    # 檔案有轉出來，但是影片時間差異太大
                    return self::convert_media_failed;
                }
            }
            # Tomcat回傳的格式不正確
        }
        return self::ask_if_converting_failed;
    }

    /**
     * 要求Tomcat server轉檔
     * @param user id, source path, output name, device type, convert server ip
     * @return ask_convert_media_failed, convert_server_error, or array(serverIp,pid,startTime)
     */
    private static function curlAskConvert() {
        $deviceTypeItem = self::getDeviceType(self::$deviceType);
        $frameRate = $deviceTypeItem['frame_rate'];
        $frameSize = $deviceTypeItem['frame_size'];
        $videoCodec = $deviceTypeItem['video_codec'];
        $bitRate = $deviceTypeItem['bit_rate'];

        $serverIp = self::$serverIp;
        $userId = self::$localUserId;
        $sourcePath = self::$localSourcePath;
        $outputName = self::$localOutputName;
        $deviceType = self::$deviceType;
        $targetUserId = self::$targetUserId;
        $targetOutName = self::$targetOutputName;
        $sourceFullPath = OC_LocalSystem::getFullPathByUserId($userId, $sourcePath);
        $sourceFullPath = urlencode($sourceFullPath);
        # 輸出檔路徑為「格式精靈」
        $outputFullPath = self::outputFullPath($targetOutName);
        $toURL = "http://" . self::TomcatServer() . ":8080/LiveStreamTransCode/MediaConverterDistrib";
        $toURL .= "?userId=$userId&sourcePath=$sourceFullPath&outputPath=$outputFullPath&serverIp=$serverIp";
        $toURL .= "&frameRate=$frameRate&frameSize=$frameSize&videoCodec=$videoCodec&bitRate=$bitRate";
        $result = self::curlToConvertServer($toURL);
        $result = self::coverAskConvertResult($result);
        if ($result != self::converting) {
            # email給管理者
            self::sendMailToAdmin($result, $userId, $sourcePath);
        }
        return $result;
    }

    /**
     * 將Tomcat回傳的Result轉為陣列參數
     * @param Tomcat回傳的string訊息
     * @return ask_convert_media_failed, convert_server_error, or converting
     */
    private static function coverAskConvertResult($result) {
        if ($result) {
            # 如果回傳的有包含「error」
            if (preg_match('/error/i', $result)) {
                return self::convert_server_error;
            }
            //ServerIP:192.168.11.27;pid:32319;startTime:2013-07-12T02:50:24Z;
            $resultArr = explode(';', $result);
            $serverIp = '';
            $pid = '';
            $startTime = '';
            foreach ($resultArr as $val) {
                if (stripos($val, 'ServerIP') === 0) {
                    $serverIp = preg_replace('#' . preg_quote('serverip:') . '#', '', strtolower($val));
                } else if (stripos($val, 'pid') === 0) {
                    $pid = preg_replace('#' . preg_quote('pid:') . '#', '', strtolower($val));
                } else if (stripos($val, 'startTime') === 0) {
                    $startTime = preg_replace('#' . preg_quote('starttime:') . '#', '', strtolower($val));
                    $startTime = self::coverTimeFromTomcatFormat($startTime);
                }
            }
            if ($serverIp && $pid && $startTime) {
                # 設置Tomcat回傳的資料
                self::$serverIp = $serverIp;
                self::$pid = $pid;
                self::$startTime = $startTime;
                return self::converting;
            }
        }
        return self::ask_convert_media_failed;
    }

}
?>