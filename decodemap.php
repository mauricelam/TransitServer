<?php 

$query = trim($_GET['text']);
if($query && $query!=""){
	include_once 'key.php';
	include 'sql.php';
	$query = preg_replace('/\\&/', '&', $query);
	
	$sql = "SELECT stops.* FROM stops INNER JOIN platforms ON stops.id=platforms.stopid WHERE platforms.name LIKE '$query' OR stops.name LIKE '$query' LIMIT 1";
	$query = mysql_query($sql);
	while($x = mysql_fetch_assoc($query)){
		$stop = new stdClass();
		$stop->n = $x['name'];
		$stop->c = $x['code'];
		$stop->q = $x['query'];
		$stop->lat = $x['latitude'];
		$stop->lng = $x['longitude'];
	}
	echo json_encode($stop);
	SQLhelper::release();
}else{
	echo 'error: query is not given';
}
?>