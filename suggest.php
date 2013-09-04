<?php 
$limit = $_GET['limit'];
$activeOnly = $_GET['activeonly'] == "true";
if(!$limit)
	$limit = 2;
	

if(isset($_GET['lat']) && isset($_GET['lng']) && $_GET['lat'] != "" && $_GET['lng'] != ""){
	$lat = $_GET['lat']/1E6;
	$lng = $_GET['lng']/1E6;
	$requestLimit = ($activeOnly) ? ($limit * 5) : $limit;
	include_once "key.php";
	$key = Key::get();
	$url = "http://developer.cumtd.com/api/v2.1/json/GetStopsByLatLon?key=$key&lat=$lat&lon=$lng&count=".$requestLimit;
	
	include "sql.php";
	include_once "cache.php";
	$result = getAPI($url, "1 day");
	
	$response = json_decode($result);
	$stops = $response->stops;
	$output = array();
	
	if($stops){
		foreach($stops as $stop){
			if($activeOnly){
				$url = "http://developer.cumtd.com/api/v2.1/json/GetDeparturesByStop?key=$key&stop_id=".$stop->stop_id."&pt=60&count=1";
				$dep_response = getAPI($url, "1 hour");
				$departures = json_decode($dep_response)->departures;
			}
				
			if(!$activeOnly || count($departures)>0){
				$s = $output[] = new stdClass();
				$s->n = $stop->stop_name;
				$s->c = substr($stop->code, 3);
				$s->q = $stop->stop_id;
				//$point = $stop->points[0];
				//$s->lat = floor($point->stop_lat*1E6);
				//$s->lng = floor($point->stop_lon*1E6);
					
				if(count($output) >= $limit)
					break;
			}
		}
	}
	SQLhelper::release();
}else{
	$output = array();
}
echo json_encode($output);
?>
