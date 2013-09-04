<?php 
// google local search api key
$key = "ABQIAAAAeDvQeIC6C0vxFzLt38d5IhTdJ2MDNgc91n-GDdQ_XpteSQEWXxS54cJ-tmNH77eWSR-k1aHKxS_zNA";

$place = trim($_GET['place']);
$place = str_replace(" ", "_", $place);
$url = "http://ajax.googleapis.com/ajax/services/search/local?v=1.0&q=$place%20urbana%20IL&key=$key";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_REFERER, "http://lam25.projects.cs.illinois.edu/");
$body = curl_exec($ch);
curl_close($ch);

$places = json_decode($body);
if($places->responseStatus == 200){
	$responseData = $places->responseData;
	$results = $responseData->results;

	$outputs = array();
	foreach($results as $location){
		$title = $location->titleNoFormatting;
		$street = $location->streetAddress;
		$city = $location->city;
		$lat = $location->lat;
		$lng = $location->lng;
		$outputs[] = array("title"=>$title, "street"=>$street, "city"=>$city, 
			"latitude"=>$lat*1E6, "longitude"=>$lng*1E6);
	}
	echo json_encode($outputs);
}
?>