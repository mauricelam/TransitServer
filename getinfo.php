<?php 

include_once 'restful.php';
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

?>