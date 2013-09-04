<?php 
ini_set('display_errors', '0');

include_once 'departures.php';
include_once 'key.php';

$urlcodes = $_REQUEST['c'];
$codes = explode(",", $urlcodes);

$refs = $_REQUEST['r'];
$referrers = ($refs == '') ? array() : explode(',', $refs);

if($urlcodes != '' && $codes && count($codes) > 0){
	$deps = new Departures();
	foreach($codes as $i => $c){
		$deps->addStopByCode($c, $referrers[$i]);
	}
	$response = $deps->execute();
	echo json_encode($response);
}

// silent: delete expired cache
ob_start();
include 'cron/deleteexpiredcache.php';
ob_end_clean();

?>