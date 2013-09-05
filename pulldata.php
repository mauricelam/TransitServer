<?php 

/**
 * This should be run when stops from CUMTD are updated. Currently there are no automated way to do
 * so. Note that this script inserts into tables named platforms2 and stops2 and should be manually
 * renamed back into platforms and stops.
 */

function addPlatform($id, $point){
    $lat = floor($point->stop_lat * 1E6);
    $lng = floor($point->stop_lon * 1E6);
    $sql2 = "INSERT INTO platforms2 SET name='{$point->stop_name}', stopid=$id, latitude=$lat, longitude=$lng";
    mysql_query($sql2);
}

include 'sql.php';
$key = 'fe18d20a15974099a63329fd612d1702';

$enabled = false;

if ($enabled) {
    $url = "http://developer.cumtd.com/api/v2.0/json/GetStops?key=$key";

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $result = curl_exec($ch);

    $response = json_decode($result);

    if ($response->stops) {
        foreach ($response->stops as $stop) {
            $name = $stop->stop_name;
            $code = intval(substr($stop->code, 3));
            $query = $stop->stop_id;
            $point = $stop->points[0];
            $lat = floor($point->stop_lat * 1E6);
            $lng = floor($point->stop_lon * 1E6);
            
            $sql = "INSERT INTO stops2 SET name='$name', code=$code, query='$query', latitude=$lat, longitude=$lng";
            echo $sql."<br>\n";
            $query = mysql_query($sql);
            $lastId = mysql_insert_id();
            foreach ($stop->points as $point) {
                addPlatform($lastId, $point);
            }
        }
    }
} else {
    echo 'not enabled';
}
    
SQLhelper::release();
?>