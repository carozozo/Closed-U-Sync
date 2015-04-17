<?php
/*關於驗證的處理*/
class OC_Auth {
	
	/*
	 * Joomla驗證後，轉向ownload驗證是否登入
	 * 此Function目前只用在p1環境
	 */
	static function directLogin($token) {
		DIFF_Auth::directLogin($token);
	}

}
