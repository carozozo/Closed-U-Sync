var PFunctions = {
    // 轉換階html符號
    escapeHtml : function(unsafe) {
        return unsafe.replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;").replace(/"/g, "&quot;").replace(/'/g, "&#039;");
    },
    // 將兩個array合併(不包含重複的值)
    mergeUniqueArray : function(arr1, arr2) {
        var arr = Array();
        var mergedArr = $.merge(arr1, arr2);
        $.each(mergedArr, function(index, val) {
            if ($.inArray(val, arr) < 0)
                arr.push(val);
        });
        return arr;
    },
    // 移除字串中不符合規則的符號
    forbiddenChar : function(name) {
        // oc_forbiddenCharArray is from js.php
        if (oc_forbiddenCharArray) {
            var nameArray = name.split('');
            nameArray = $.grep(nameArray, function(val, index) {
                return ($.inArray(val, oc_forbiddenCharArray) < 0);
            });
            return name = nameArray.join('');
        }
    },
    // 回傳1970/01/01至datetimeStr的秒數
    toTimestamp : function(datetimeStr) {
        datetimeStr = datetimeStr.replace(/-/g, ' ');
        datetimeStr = datetimeStr.replace(/:/g, ' ');
        var datetimeArr = datetimeStr.split(' ');
        var year = datetimeArr[0];
        var month = datetimeArr[1] - 1;
        var day = datetimeArr[2];
        var hour = datetimeArr[3];
        var minute = datetimeArr[4];
        var second = datetimeArr[5];
        var dateTime = new Date(year, month, day, hour, minute, second);
        dateTime = Math.round(dateTime.getTime() / 1000);
        return dateTime;
    },
    // 取得現在和指定的時間差
    relative_time : function(timestamp) {
        var timediff = Math.round((new Date()).getTime() / 1000) - timestamp;
        var diffminutes = Math.round(timediff / 60);
        var diffhours = Math.round(diffminutes / 60);
        var diffdays = Math.round(diffhours / 24);
        var diffmonths = Math.round(diffdays / 31);
        var diffyears = Math.round(diffdays / 365);
        if (timediff < 60) {
            return t('files', 'seconds ago');
        } else if (timediff < 120) {
            return t('files', '1 minute ago');
        } else if (timediff < 3600) {
            return diffminutes + ' ' + t('files', 'minutes ago');
        } else if (timediff < 7200) {
            return t('files', '1 hour ago');
        } else if (timediff < 86400) {
            return diffhours + ' ' + t('files', 'hours ago');
        }
        // else if (timediff < 86400) {
        // return t('files', 'today');
        // }
        else if (timediff < 172800) {
            return t('files', 'yesterday');
        } else if (timediff < 2678400) {
            return diffdays + ' ' + t('files', 'days ago');
        } else if (timediff < 5184000) {
            return t('files', 'last month');
        }
        // else if($timediff < 31556926) { return $diffmonths.' months ago'; }
        else if (timediff < 31556926) {
            return t('files', 'months ago');
        } else if (timediff < 63113852) {
            return t('files', 'last year');
        } else {
            return diffyears + ' ' + t('files', 'years ago');
        }
    },
    // 將 bytes 轉為容量顯示
    humanFileSize : function(bytes) {
        if (bytes < 1024) {
            return bytes + ' B';
        }
        bytes = Math.round(bytes / 1024, 1);
        if (bytes < 1024) {
            return bytes + ' kB';
        }
        bytes = Math.round(bytes / 1024, 1);
        if (bytes < 1024) {
            return bytes + ' MB';
        }

        // Wow, heavy duty for owncloud
        bytes = Math.round(bytes / 1024, 1);
        return bytes + ' GB';
    },
    //取得路徑字串中資料夾路徑的部份，如果整個路徑都是資料夾，則排除最後的資料夾
    dirname : function(str) {
        var last = str.lastIndexOf('/');
        var strLen = str.length;
        if (last == strLen - 1) {
            // 如果字串最後是[/]，則移除
            str = str.substring(0, strLen - 1);
        }
        var strBase = PFunctions.basename(str, true);
        str = str.replace(strBase, '');
        return str;
    },
    // 取得字串中檔案名稱的部份，如果整個路徑都是資料夾，則回傳最後的資料夾名稱
    basename : function(str, getExtension) {
        var last = str.lastIndexOf('/');
        var strLen = str.length;
        if (last == strLen - 1) {
            // 如果字串最後是[/]，則移除
            str = str.substring(0, strLen - 1);
        }
        var base = new String(str).substring(str.lastIndexOf('/') + 1);
        // 如果沒有要取得副檔名，則移除
        if (!getExtension && base.lastIndexOf(".") != -1) {
            base = base.substring(0, base.lastIndexOf("."));
        }
        return base;
    },
    // 列出陣列/物件的所有值
    dump : function(arr, level) {
        var dumped_text = "";
        if (!level)
            level = 0;

        //The padding given at the beginning of the line.
        var level_padding = "";
        for (var j = 0; j < level + 1; j++)
            level_padding += "    ";

        if ( typeof (arr) == 'object') {//Array/Hashes/Objects
            for (var item in arr) {
                var value = arr[item];

                if ( typeof (value) == 'object') {//If it is an array,
                    dumped_text += level_padding + "'" + item + "' ...\n";
                    dumped_text += PFunctions.dump(value, level + 1);
                } else {
                    dumped_text += level_padding + "'" + item + "' => \"" + value + "\"\n";
                }
            }
        } else {//Stings/Chars/Numbers etc.
            dumped_text = "===>" + arr + "<===(" + typeof (arr) + ")";
        }
        return dumped_text;
    },
};
