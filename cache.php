<?php

require_once('phpfastcache/phpfastcache.php');
// phpFastCache::setup('storage', 'files');

function cacheResult($changeset_id, $url, $response){
	$data =
		array(
			'url' => $url,
			'changeset' => $changeset_id,
			'response' => $response,
			'updated' => time(),
		);
	try {
		phpFastCache('files')->set($url, $data);
	} catch (Exception $e) {}
}

function getCache($url){
	$cache_result = null;
	try {
		$cache_result = phpFastCache('files')->get($url);
	} catch (Exception $e) {}
	return is_array($cache_result) ? $cache_result : false;
}

function curlAPI($url, $changeset_id){
	if($changeset_id)
		$url .= '&changeset_id='.$changesetid;
	$ch = curl_init($url);
	$headers = array('Expect:'); 
	curl_setopt($ch, CURLOPT_HTTPHEADER, $headers); 
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	$result = curl_exec($ch);
	curl_close($ch);
	return $result;
}

function getCacheIfAvailable($url, $timeTolerance){
	$cache = getCache($url);
	if(!$cache || !$timeTolerance || $cache['updated'] <= strtotime('-'.$timeTolerance)){
		return false;
	}else{
		return $cache;
	}
}

function getAPI($url, $timeTolerance){
	$cache = getCache($url);
	if ($cache !== false && !array_key_exists('updated', $cache)) {
		error_log('$cache: '.var_export($cache, true)."\n", 3, 'custom_error');
		error_log('$url: '.$url."\n", 3, 'custom_error');
	}
	if(!$cache || !$timeTolerance || $cache['updated'] <= strtotime('-'.$timeTolerance)){
		$oldset = false;
		if($cache)
			$oldset = $cache['changeset'];
		$response = curlAPI($url, $oldset);
		$obj = json_decode($response);
		if((!$oldset || $obj->new_changeset) && $obj->status->code == 200){
			cacheResult($obj->changeset_id, $url, $response);
			return $response;
		}else{
			return $cache['response'];
		}
	}else{
		return $cache['response'];
	}
}
?>