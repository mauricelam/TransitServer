<?php 
$limit = $_GET['limit'];
if(!$limit)
	$limit = 2;
	

if(isset($_GET['lat']) && isset($_GET['lng'])){
	$lat = $_GET['lat']/1E6;
	$lng = $_GET['lng']/1E6;
	include_once "key.php";
	$url = "http://developer.cumtd.com/api/v2.1/json/GetStopsByLatLon?key=".Key::get()."&lat=$lat&lon=$lng&count=$limit";
	
	include "sql.php";
	include_once "cache.php";
	$result = getAPI($url, "1 day");
	
	$response = json_decode($result);
	$stops = $response->stops;
	$output = array();
	
	foreach($stops as $stop){
		$s = $output[] = new stdClass();
		$s->n = $stop->stop_name;
		$s->c = substr($stop->code, 3);
		$s->q = $stop->stop_id;
		//$point = $stop->points[0];
		//$s->lat = floor($point->stop_lat*1E6);
		//$s->lng = floor($point->stop_lon*1E6);
		//$s->dist = $stop->distance;
	}
	echo json_encode($output);
	SQLhelper::release();
}
?>
