<?php 

include_once 'restful.php';
include_once 'key.php';
include_once 'sqlhelper.php';
include_once 'cache.php';

function getStopFromCode($code){
    if (SQLhelper::request()) {
        $padcode = str_pad($code, 4, "0", STR_PAD_LEFT);
        $sql02 = "SELECT name, code, query, latitude, longitude FROM stops WHERE code=$padcode";
        $query02 = mysql_query($sql02);
        $result = mysql_fetch_assoc($query02);
        SQLhelper::release();
        return $result;
    }
}

function parseTime($time) {
    $components = explode(':', $time);
    $hour = intval($components[0]);
    if ($hour >= 24) {
        $components[0] = (string)($hour - 24);
        return strtotime(date('Ymd ', strtotime('+1 day')) . implode(':', $components));
    }
    
    return strtotime($time);
}

function getRoutes($key) {
    $routeUrl = "http://developer.cumtd.com/api/v2.1/json/GetRoutes?key=$key";
    $routeResult = getAPI($routeUrl, '3 days');
    $routeResponse = json_decode($routeResult);
    $routes = $routeResponse->routes;
    if (!$routes) {
        error_log(  'Routes is falsy: '.var_export($routes, true)."\n".
                    'routeResponse: '.var_export($routeResponse, true));
    }
    $output = array();
    foreach ($routes as $route) {
        $output[$route->route_id] = $route;
    }
    return $output;
}

$code = $_REQUEST['c'];
$ref = $_REQUEST['r'];

if ($code) {
    $key = Key::get();
    $stop = getStopFromCode($code);
    $url = "http://developer.cumtd.com/api/v2.1/json/GetStopTimesByStop?key=$key&date=" . date('Ymd') . "&stop_id=" . $stop['query'];
    $result = getAPI($url, '10 mins');
    $response = json_decode($result);
    $departures = $response->stop_times;
    $routes = getRoutes($key);
    $buses = array();

    if($departures){
        foreach($departures as $bus){
            $route = $routes[$bus->trip->route_id];
            $departure = parseTime($bus->departure_time);
            
            $b = new stdClass();
            $b->n = $route->route_short_name . $bus->trip->direction[0] . ' ' . $route->route_long_name;
            $b->e = $departure;
            $b->t = $bus->trip->trip_id;
            $buses[] = $b;
        }
    }
    
    $output = new stdClass;
    $s = new stdClass();
    $s->n = $stop['name'];
    $s->c = $stop['code'];
    $s->q = $stop['query'];
    $s->lat = $stop['latitude'];
    $s->lng = $stop['longitude'];
    $output->s = $s;
    $output->r = $buses;
    
    echo json_encode($output);
}

?>