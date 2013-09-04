<?php

require_once('phpfastcache/phpfastcache.php');
phpFastCache::setup('storage', 'auto');

// my key: fe18d20a15974099a63329fd612d1702
// ryan's key: e6e671f102454587b38fc538027a31db
// Transit's key: 930e06ead51f412098ab59679e684e45
// caelitus' key: 5107f159c03746d3ab775d907d6d0d9a

class Key {
	private $key;
	
	static function get () {
		if (!$key) {
			$cache = phpFastCache();
			$key = $cache->get('apikey');
		}
		if (!$key) {
			include "sql.php";
			$sql = "SELECT `key` FROM `apikey` WHERE turn=0";
			$query = mysql_query($sql);
			$key = mysql_fetch_array($query);
			$key = $key[0];
			SQLhelper::release();
			$cache->set('apikey', $key);
		}
		return $key;
	}
	
	static function next () {
		include "sql.php";
		
		$sql = "SELECT COUNT(*) FROM `apikey`";
		$query = mysql_query($sql);
		$numrows = mysql_fetch_array($query);
		$numrows = $numrows[0];
		
		$sql = "UPDATE `apikey` SET `turn`=MOD(`turn`+1,$numrows)";
		$query = mysql_query($sql);
		$key = false; // invalidate current key

		$cache = phpFastCache();
		$cache->delete('apikey');
		
		SQLhelper::release();
	}
}

?>