<?php 

include_once 'key.php';

if(isset($_GET['trip'])){
    $url = "http://developer.cumtd.com/api/v2.2/json/GetTrip?key=".Key::get()."&trip_id=".$_GET['trip'];
    include 'sql.php';
    include_once 'cache.php';
    $result = getAPI($url, '5 min');

    $response = json_decode($result);
    $trip = $response->trips[0];

    $shape = $trip->shape_id;
    if ($shape) {
        $stops = array();
        $url = "http://developer.cumtd.com/api/v2.2/json/GetShape?key=".Key::get()."&shape_id=".urlencode($shape);
        $shapeResult = getAPI($url, '5 min');
        $shapeResponse = json_decode($shapeResult);
        foreach ($shapeResponse->shapes as $shape) {
            if ($shape->stop_id) {
                $stopid = explode(':', $shape->stop_id);
                $stop = getStopFromQuery($stopid[0]);
                $stops[] = $stop;
            }
        }
        $vipstops = getImportantStops($stops, 7, $_GET['stop']);
    }

    $output = array();
    $headsign = $trip->trip_headsign;
    $headsign = explode('-', $headsign, 2);
    $headsign = trim($headsign[1]);
    $output['h'] = $headsign;
    $output['s'] = $vipstops or array();
    
    echo json_encode($output);
    SQLhelper::release();
}

/**
 * Get the important stops out of the list of all stops, based on size.
 * 
 * @param  Stop[] $stops       The array of stops to find important stops from.
 * @param  int    $resultSize  Approximate number of stops to return. This number is not precise and
 *                             there is no guarantee for the actualy number of returned stops.
 * @param  string $currentStop Query (ID) of the current stop. Will only find important stops from
 *                             after this stop.
 * @return Stop[]              List of important stops.
 */
function getImportantStops($stops, $resultSize, $currentStop=false) {
    // Start from the current stop
    $startpos = 0;
    if ($currentStop) {
        foreach ($stops as $i => $stop) {
            if ($stop['query'] == $currentStop) {
                $startpos = $i + 1;
                break;
            }
        }
    }

    $vipstops = array($currentStop);
    $endPos = count($stops);
    $stopCount = $endPos - $startpos;
    $resultSize /= 2;
    $groupSize = max(floor($stopCount / $resultSize), 1);
    for ($i = $startpos; $i < $endPos;) {
        $stop = maxStopBySize($stops, $i, $i + $groupSize);
        $vipstops[] = $stops[$i]['query'];
        $i += ceil($groupSize / 2);
    }

    // Add the terminal to the result
    if (end($vipstops) != end($stops)['query']) {
        $vipstops[] = end($stops)['query'];
    } 
    return $vipstops;
}

function maxStopBySize($stops, $from, $to) {
    $maxsize = -1;
    $maxstop = -1;
    for ($i = $from; $i < $to; $i++) { 
        $stop = $stops[$i];
        if ($stop['size'] > $maxsize) {
            $maxsize = $stop['size'];
            $maxstop = $i;
        }
    }
    return $maxstop;
}

function getStopFromQuery($query){
    if (SQLhelper::request()) {
        $sql01 = "SELECT name, code, query, size FROM stops WHERE query='$query'";
        $query01 = mysql_query($sql01);
        $result = mysql_fetch_assoc($query01);
        SQLhelper::release();
        return $result;
    }
}
?>
