<?php 

function csvTosql($file, $tablename){
	include_once "../include.php";
	$f = fopen($file, 'r');
	$fields = rtrim(fgets($f));
	fclose($f);
	$sql = "TRUNCATE `$tablename`";
	$query = mysql_query($sql);
	$sql2 = "LOAD DATA LOCAL INFILE '$file' INTO TABLE `$tablename` FIELDS TERMINATED BY ',' LINES TERMINATED BY '\r\n' IGNORE 1 LINES ($fields)";
	echo $sql2 . ' ';
	$query2 = mysql_query($sql2);
	echo $query2. '<br>';
	echo mysql_error();
}

function buildSchedule(){
	include_once "../include.php";
	$numday = 7;
	
	$start = microtime(true);
		
	$routes = array();
	for($i = 0; $i < $numday; $i++){
		$routes[$i]['date'] = new DateTime("today +$i day");
		$routes[$i]['service'] = array();
	}
	
	$sql = "SELECT monday, tuesday, wednesday, thursday, friday, saturday, sunday, service_id FROM gtfs_calendar WHERE (gtfs_calendar.end_date >= NOW() AND DATE_ADD(NOW(), INTERVAL $numday DAY) >= gtfs_calendar.start_date)";
	$query = mysql_query($sql);
	while($x = mysql_fetch_assoc($query)){
		for($i = 0; $i < $numday; $i++){
			$dayofweek = strtolower($routes[$i]['date']->format('l'));
			if($x[$dayofweek] == '1'){
				$routes[$i]['service'][] = $x['service_id'];
			}
			$sql2 = "SELECT gtfs_calendar_dates.exception_type FROM `gtfs_calendar_dates` WHERE service_id = '".$x['service_id']."' AND date = '".$routes[$i]['date']->format('Y-m-d')."'";
			$query2 = mysql_query($sql2);
			$result = mysql_fetch_assoc($query2);
			if($result['exception_type'] == 1){
				$routes[$i]['service'][] = $x['service_id'];
			}else if($result['exception_type'] == 2){
				array_pop($routes[$i]['service']);
			}
		}
	}
	
	$schedule = array();
	for($i = 0; $i < $numday; $i++){
		foreach($routes[$i]['service'] as $s){
			$sql = "SELECT gtfs_trips.trip_id, gtfs_trips.trip_headsign, gtfs_routes.route_short_name, gtfs_routes.route_long_name FROM gtfs_trips INNER JOIN gtfs_routes ON gtfs_trips.route_id = gtfs_routes.route_id WHERE service_id = '$s'";
			$query = mysql_query($sql);
			while($x = mysql_fetch_assoc($query)){
				$item = new stdClass();
				$item->route = $x['route_short_name'].substr($x['trip_headsign'], 0, 1).' '.$x['route_long_name'];
				$item->date = $routes[$i]['date'];
				$schedule[$x['trip_id']][] = $item;
			}
		}
	}
	
	unset($routes);
	
	$rows = array();
	
	$sql = "TRUNCATE TABLE `schedule`";
	$query = mysql_query($sql);
	
	$sql = "SELECT gtfs_stop_times.departure_time, gtfs_stop_times.trip_id, gtfs_stop_times.stop_id, stops.code FROM gtfs_stop_times INNER JOIN stops ON SUBSTRING_INDEX(gtfs_stop_times.stop_id, ':', 1) = stops.query";
	$query = mysql_query($sql);
	while($x = mysql_fetch_assoc($query)){
		$items = $schedule[$x['trip_id']];
		if($items) 
		  foreach($items as $item){
			$r = new stdClass();
			$r->route = $item->route;
			$r->stop = $x['stop_id'];
			$r->date = clone $item->date;
			$r->code = $x['code'];
			$time = new DateTime($x['departure_time']);
			$r->date->setTime($time->format('H'), $time->format('i'));
			$sql2 = "INSERT INTO `schedule` SET `stopid`='{$r->stop}', `stopcode`={$r->code}, `route`='{$r->route}', `departure`='{$r->date->format('Y-m-d H:i:s')}'";
			$query2 = mysql_query($sql2);
		  }
	}

	//echo json_encode($rows);
}

/*
$url = "http://developer.cumtd.com/gtfs/google_transit.zip";
$fh = fopen('gtfs.zip', 'w');
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url); 
curl_setopt($ch, CURLOPT_FILE, $fh); 
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true); // this will follow redirects
curl_exec($ch);
curl_close($ch);
fclose($fh);

System('unzip gtfs.zip -d gtfs/');
*/

include_once "../include.php";
/*
csvTosql('gtfs/calendar_dates.txt', 'gtfs_calendar_dates');
csvTosql('gtfs/trips.txt', 'gtfs_trips');
csvTosql('gtfs/stop_times.txt', 'gtfs_stop_times');
csvTosql('gtfs/routes.txt', 'gtfs_routes');
csvTosql('gtfs/calendar.txt', 'gtfs_calendar');
*/

buildSchedule();

mysql_close();

echo "done";

?>