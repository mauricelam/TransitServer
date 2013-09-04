<?php 
class SQLhelper {
	private static $requestCount = 0, $link;
	
	static function request () {
		if (SQLhelper::$requestCount == 0) {
			SQLhelper::$link = mysql_connect($_SERVER['dbhost'], $_SERVER['dbuser'], $_SERVER['dbpass']);
			if (SQLhelper::$link === false) return false;
			mysql_select_db($_SERVER['transitdb']);
		}
		SQLhelper::$requestCount++;
		return true;
	}
	
	static function release () {
		SQLhelper::$requestCount--;
		if (SQLhelper::$requestCount == 0) {
			mysql_close(SQLhelper::$link);
		}
	}
}
?>