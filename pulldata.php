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

$enabled = $_GET['enabled'] === 'true';

if ($enabled) {

    $create_platforms = "CREATE TABLE IF NOT EXISTS `platforms2` (
                          `id` int(255) unsigned NOT NULL AUTO_INCREMENT,
                          `name` varchar(255) NOT NULL,
                          `stopid` int(255) unsigned NOT NULL,
                          `latitude` int(11) NOT NULL,
                          `longitude` int(11) NOT NULL,
                          PRIMARY KEY (`id`)
                        ) ENGINE=MyISAM  DEFAULT CHARSET=latin1;";
    mysql_query($create_platforms);

    $create_stops = "CREATE TABLE IF NOT EXISTS `stops2` (
                      `id` int(255) unsigned NOT NULL AUTO_INCREMENT,
                      `name` varchar(255) NOT NULL,
                      `code` int(4) unsigned zerofill NOT NULL,
                      `query` varchar(255) NOT NULL,
                      `size` int(10) unsigned NOT NULL,
                      `latitude` int(11) NOT NULL,
                      `longitude` int(11) NOT NULL,
                      `updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                      PRIMARY KEY (`id`),
                      UNIQUE KEY `query` (`query`),
                      UNIQUE KEY `code` (`code`),
                      FULLTEXT KEY `name` (`name`)
                    ) ENGINE=MyISAM  DEFAULT CHARSET=latin1;";
    mysql_query($create_stops);

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