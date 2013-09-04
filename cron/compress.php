<?php

function minuteInterval($interval){
	$d = $interval->format('%a') * 24 * 60;
	$h = $interval->h * 60;
	$i = $interval->i;
	return $d + $h + $i;
}

function compressSchedule($print){
	include_once "../include.php";
	$stop = 'IU:2';
	$sql = "SELECT * FROM `schedule` WHERE `stopid`='$stop' ORDER BY `route`, `departure`";
	$query = mysql_query($sql);
	$compressed = array();
	$lastItem = null;
	
	$op = array();
	while($x = mysql_fetch_assoc($query)){
		$match = false;
		$departure = new DateTime($x['departure']);
		if($print){
			$p = new stdClass();
			$p->route = $x['route'];
			$p->time = $departure->format('Y-m-d H:i:s');
			$op[] = $p;
		}
		if($lastItem != null){
			$difference = minuteInterval($lastItem->endTime->diff($departure));
			//echo "diff: $difference<br>";
			
			if($x['route'] == $lastItem->route){
				if($lastItem->interval == -1 || $lastItem->interval == $difference){
					$match = true;
				}
			}
		}
		
		if($match){
			$lastItem->endTime = $departure;
			$lastItem->interval = $difference;
		}else{
			$lastItem = new stdClass();
			$lastItem->interval = -1;
			$lastItem->startTime = $departure;
			$lastItem->endTime = $departure;
			$lastItem->route = $x['route'];
			$compressed[] = $lastItem;
		}
	}
	if($print)
		echo json_encode($op);
	return $compressed;
}

function decompress($compressed){
	$output = array();
	foreach($compressed as $item){
		$time = $item->startTime;
		while($time <= $item->endTime){
			$obj = new stdClass();
			$obj->route = $item->route;
			$obj->time = $time->format('Y-m-d H:i:s');
			$output[] = $obj;
			$time->add(DateInterval::createFromDateString("{$item->interval} minutes"));
			if($item->interval <= 0){
				break;
			}
		}
	}
	return $output;
}

$printRaw = $_GET['raw'];
$printCompressed = $_GET['compressed'];

$compressed = compressSchedule($printRaw);
if($printCompressed){
	echo json_encode($compressed);
}
$output = decompress($compressed);
if(!$printRaw && !$printCompressed){
	echo json_encode($output);
}
mysql_close();

?>