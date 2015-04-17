<?php
/**
 * ownCloud
 *
 * @author Caro Huang
 * @copyright 2013 U-Sync
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU AFFERO GENERAL PUBLIC LICENSE
 * License as published by the Free Software Foundation; either
 * version 3 of the License, or any later version.

 */
/*
 *
 * An example of config.php
 *
 $CONFIG_MAIN = array(
 "CONFIG" => array(
 "datadirectory" => '/var/www/html/s1/data',
 "dbtype" => 'mysql',
 "version" => '3.0.2',
 "installedat" => '1331789098.0487',
 "lastupdatedat" => '1332485928.3059',
 "dbname" => 'clouds1caro',
 "dbhost" => 'localhost',
 "dbtableprefix" => 'oc_',
 "dbuser" => 'oc_usync',
 "dbpassword" => '1fb64fcb44b17f3a9629e2d9230dcef8',
 "installed" => true,
 "loglevel" => '0',
 "maxZipInputSize" => '0',
 "allowZipDownload" => true,
 "forcessl" => false,
 ),
 "CONFIG_CUSTOM" => array(
 "serverType" => 's1',
 "siteTitle" => 'Caro_S1',
 "defaultLdapQuota" => '6442450944',
 "defaultLanguage" => 'zh_TW',
 "defaultTimezone" => 'Asia/Taipei',
 "publicBoard" => array(
 "最新消息" => '/var/www/html/s1/data/Docs/info',
 "影音頻道" => '/var/www/html/s1/data/Docs/videos',
 ),
 "mediaConverterLimitTimes" => '20',
 ),
 );
 *
 */

/**
 * This class is responsible for reading and writing config.php, the very basic
 * configuration file of owncloud.
 */
class OC_Config {
	//計錄config變數是否已被讀取到cache
	public static $init = false;

	// associative array key => value
	public static $cache = array();

	/**
	 * @brief Lists all available config keys
	 * @returns array with key names
	 *
	 * This function returns all keys saved in config.php. Please note that it
	 * does not return the values.
	 */
	public static function getKeys($configName = 'CONFIG') {
		self::readData();

		return array_keys(self::$cache[$configName]);
	}

	/**
	 * @brief Gets a value from config.php
	 * @param $key key
	 * @param $default = null default value
	 * @returns the value or $default
	 *
	 * This function gets the value from config.php. If it does not exist,
	 * $default will be returned.
	 */
	public static function getValue($key, $default = null, $configName = 'CONFIG') {
		try {
			self::readData();
			if (isset(self::$cache[$configName]) && array_key_exists($key, self::$cache[$configName])) {
				return self::$cache[$configName][$key];
			}
			return $default;
		} catch(exception $e) {
			return $default;
		}
	}

	/**
	 * @brief Sets a value
	 * @param $key key
	 * @param $value value
	 * @returns true/false
	 *
	 * This function sets the value and writes the config.php. If the file can
	 * not be written, false will be returned.
	 */
	public static function setValue($key, $value, $configName = 'CONFIG') {
		self::readData();
		// Add change
		self::$cache[$configName][$key] = $value;
		// Write changes
		self::writeData();
		return true;
	}

	/**
	 * @brief Removes a key from the config
	 * @param $key key
	 * @returns true/false
	 *
	 * This function removes a key from the config.php. If owncloud has no
	 * write access to config.php, the function will return false.
	 */
	public static function deleteKey($key, $configName = 'CONFIG') {
		self::readData();
		if (array_key_exists($key, self::$cache[$configName])) {
			// Delete key from cache
			unset(self::$cache[$configName][$key]);
			if (count(self::$cache[$configName]) == 0) {
				unset(self::$cache[$configName]);
			}
			// Write changes
			self::writeData();
		}
		return true;
	}

	/**
	 * @brief Loads the config file
	 * @returns true/false
	 *
	 * Reads the config file and saves it to the cache
	 */
	private static function readData() {
		//已經讀取到cache，回傳true
		if (isset(self::$init) && self::$init == true) {
			return true;
		}

		if (!file_exists(OC::$SERVERROOT . "/config/config.php")) {
			return false;
		}

		// Include the file, save the data from $CONFIG
		include (OC::$SERVERROOT . "/config/config.php");
		if (isset($CONFIG_MAIN) && is_array($CONFIG_MAIN)) {
			self::$cache = $CONFIG_MAIN;
		}
		self::$init = true;
		return true;
	}

	/**
	 * @brief Writes the config file
	 * @returns true/false
	 *
	 * Saves the config to the config file.
	 *
	 * Known flaws: Strings are not escaped properly
	 */
	public static function writeData() {
		// Create a php file ...
		$content = "<?php\n\$CONFIG_MAIN = array(\n";

		foreach (self::$cache as $configName => $array) {
			$content .= "	\n\"" . $configName . "\" => array(\n";
			foreach (self::$cache[$configName] as $key => $value) {
				if (is_bool($value)) {
					$content .= self::writeBoolToData($key, $value);
				} else if (is_array($value)) {
					$content .= self::writeArrayToData($key, $value);
				} else {
					$content .= self::writeStrToData($key, $value);
				}
			}
			$content .= "	),\n";
		}
		$content .= ");\n?>";

		// Write the file
		$result = @file_put_contents(OC::$SERVERROOT . "/config/config.php", $content);
		if (!$result) {
			$tmpl = new OC_Template('', 'error', 'guest');
			$tmpl -> assign('errors', array(1 => array(
					'error' => "Can't write into config directory 'config'",
					'hint' => "You can usually fix this by giving the webserver use write access to the config directory in owncloud"
				)));
			$tmpl -> printPage();
			exit ;
		}
		return true;
	}

	private static function writeBoolToData($key, $value) {
		$value = $value ? 'true' : 'false';
		$content = "		\"$key\" => $value,\n";
		return $content;
	}

	private static function writeStrToData($key, $value) {
		$value = str_replace("'", "\\'", $value);
		$content = "		\"$key\" => '$value',\n";
		return $content;
	}

	private static function writeArrayToData($mainKey, $arrayValue) {
		$content = "		\"$mainKey\" => array(\n";
		foreach ($arrayValue as $key => $value) {
			if (is_bool($value)) {
				$content .= self::writeBoolToData($key, $value);
			} else if (is_array($value)) {
				self::writeArrayToData($key, $value);
			} else {
				$content .= self::writeStrToData($key, $value);
			}
		}
		$content .= "		),\n";
		return $content;
	}

	//將陣列輸出為JS格式
	public static function getArrayValueToJs($array) {
		$returnVal = "{";
		foreach ($array as $key2 => $val) {
			$returnVal .= "'" . $key2 . "' : '" . $val . "', ";
		}
		$returnVal .= "}";
		return $returnVal;
	}

	//取得config.php指定的config array裡的所有值
	public static function getConfigArray($configName = 'CONFIG') {
		if (self::readData())
			return self::$cache[$configName];
		return NULL;
	}

}
