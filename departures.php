<?php

include_once 'key.php';
include_once 'sqlhelper.php';

/**
 * This class is reponsible for pulling data for the bus stop. The main logic of the whole
 * route time application can be found here. In addition to simply fetching the API for info,
 * this uses multi-cURL to make querying multiple stops faster, and automatic key-switching to
 * overcome the fact that there is not enough API quotas.
 */
class Departures {
    private $apikey, $stops = array();
    
    function __construct(){
        $this->apikey = Key::get();
    }
    
    function alreadyAdded($stop){
        foreach($this->stops as $s){
            if($stop == $s)
                return true;
        }
        return false;
    }
    
    function addStopByCode($code, $referrer = ""){
        $stop = $this->getStopFromCode($code);
        if(! $this->alreadyAdded($stop))
            $this->addStop($stop, $referrer);
    }
    
    function addStopByQuery($query, $referrer = ""){
        $stop = $this->getStopFromQuery($query);
        if(!$this->alreadyAdded($stop))
            $this->addStop($stop, $referrer);
    }
    
    function addStop($stop, $referrer = ""){
        if($referrer){
            $stop["referrer"] = $referrer;
        }
        $this->stops[] = $stop;
    }
    
    function execute(){
        // init curl
        $curlMultiHandler = curl_multi_init();
        $data = array();
        foreach($this->stops as $stop){
            $code = intval($stop["code"]);
            $data[$code]["stop"] = $stop;
            $referrer = $stop["referrer"];
            if($referrer && $referrer != ""){
                $this->addToSuggestions($referrer, $code, $stop['query']);
            }
            $this->curlStop($data[$code], $curlMultiHandler);
        }
        $this->doCurl($data, $curlMultiHandler);
        $output = array();
        foreach($data as $c=>$d){
            $output[$c] = $this->processStop($d["result"], $d["stop"]);
        }
        return $output;
    }   
    
    function curlStop(&$data, &$curlMultiHandler){
        include_once 'cache.php';
        $query = $data['stop']['query'];
        $url = "http://developer.cumtd.com/api/v2.2/json/GetDeparturesByStop?key=".$this->apikey."&pt=60&stop_id=".$query;
        // $url = "http://developer.cumtd.com/api/v2.1/json/GetDeparturesByStop?key=".$this->apikey."&pt=60&stop_id=".$query;
        //$url = "http://developer.cumtd.com/api/v2.0/json/GetDeparturesByStop?key=".$this->apikey."&pt=60&stop_id=".$query;
        //$url = "http://developer.cumtd.com/api/v1.0/json/Departures.getListByStop?key=$key&pt=60&stop_id=".$query;
        $data['url'] = $url;

        $cache = getCacheIfAvailable($url, '15 sec');
        if (!$cache) {
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_VERBOSE, false);
            $headers = array('Expect:'); 
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers); 
            curl_multi_add_handle($curlMultiHandler, $ch);
            $data['curl'] = $ch;
        } else {
            $data['result'] = $cache['response'];
        }
    }
    
    function doCurl(&$data, $curlMultiHandler){
        // Start performing the request
        do {
            $execReturnValue = curl_multi_exec($curlMultiHandler, $running);
        } while ($execReturnValue == CURLM_CALL_MULTI_PERFORM);
        
        while($running && $execReturnValue == CURLM_OK){
            if(curl_multi_select($curlMultiHandler) != -1){
                do{
                    $mrc = curl_multi_exec($curlMultiHandler, $running);
                }while($mrc == CURLM_CALL_MULTI_PERFORM);
            }
        }
        
        foreach($data as $i => $d){
            if(!$d['result']){
                $data[$i]['result'] = curl_multi_getcontent($d['curl']);
                if(count(json_decode($data[$i]['result'])->departures) > 0){
                    cacheResult('', $d['url'], $data[$i]['result']);
                }
            }
            
            if($d['curl']){
                curl_multi_remove_handle($curlMultiHandler, $d['curl']);
                curl_close($d['curl']);
            }
        }
        
        curl_multi_close($curlMultiHandler);
    }
    
    function processStop($result, $stop){
        $response = json_decode($result);
        $departures = $response->departures;
        $buses = array();
        
        if($response->status->code == 403){
            include_once 'mail.php';
            $apikey = $this->apikey;
            Key::next();
            bug_report("Transit - CUMTD API limit reached. :( \n\n key: $apikey \n\n version: mtdbeta departures");
        }

        if($departures){
            foreach($departures as $bus){
                $b = new stdClass();
                $b->n = $bus->headsign;
                $b->e = strtotime($bus->expected);
                $b->t = $bus->trip->trip_id;
                if ($bus->is_istop) {
                    $b->f = "i";
                }
                $buses[] = $b;
            }
        }
        
        $output = new stdClass();
        $s = new stdClass();
        $s->n = $stop['name'];
        $s->c = $stop['code'];
        $s->q = $stop['query'];
        $s->lat = $stop['latitude'];
        $s->lng = $stop['longitude'];
        $output->s = $s;
        $output->r = $buses;

        $this->updateStopSize($s->q, count($buses));

        return $output;
    }
    
    function addToSuggestions($referrer, $code, $query){
        if (SQLhelper::request()) {
            $sql = "INSERT INTO suggestions (stringid, query, stopcode, frequency, stopid) VALUES ('$code.$referrer', '$referrer', $code, 1, '$query') ON DUPLICATE KEY UPDATE frequency=frequency+1";
            $query = mysql_query($sql);
            SQLhelper::release();
        }
    }

    /**
     * Roughly calculate the size of the bus stop. That is, how many bus does it serve roughly.
     * Note: this calculation method depends on when and how frequently info for this stop is
     *       polled, but it is close enough.
     */
    function updateStopSize($query, $size){
        if (SQLhelper::request()) {
            $sql = "UPDATE `stops` SET `size`=`size`*0.75+$size*0.25 WHERE `query`='$query'";
            $query = mysql_query($sql);
            SQLhelper::release();
        }
    }
    
    // private functions
    function getStopFromQuery($query){
        if (SQLhelper::request()) {
            $sql01 = "SELECT name, code, query, latitude, longitude FROM stops WHERE query='$query'";
            $query01 = mysql_query($sql01);
            $result = mysql_fetch_assoc($query01);
            SQLhelper::release();
            return $result;
        }
    }
    
    function getStopFromCode($code){
        if (SQLhelper::request()) {
            $padcode = str_pad($code, 4, '0', STR_PAD_LEFT);
            $sql02 = "SELECT name, code, query, latitude, longitude FROM stops WHERE code=$padcode";
            $query02 = mysql_query($sql02);
            $result = mysql_fetch_assoc($query02);
            SQLhelper::release();
            return $result;
        }
    }
}

?>