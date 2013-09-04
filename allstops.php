<?php 

$source = $_GET['source'];
if($source == "map"){
	include "sql.php";
	$sql = "SELECT name, code, query, latitude, longitude FROM stops";
	$query = mysql_query($sql);
	$stops = array();
	while($x = mysql_fetch_assoc($query)){
		$x2 = new stdClass();
		$x2->n = $x['name'];
		$x2->c = $x['code'];
		$x2->q = $x['query'];
		$x2->lat = $x['latitude'];
		$x2->lng = $x['longitude'];
		$stops[] = $x2;
	}
	$output = json_encode($stops);
	if ($_GET['hash'] && $_GET['hash'] == md5(utf8_encode($output))) {
		echo "cache_unchanged";
	} else {
		$gzipoutput = gzencode($output);
		header('Content-Encoding: gzip'); #
		header('Content-Length: '.strlen($gzipoutput)); #
		echo $gzipoutput;
	}
	SQLhelper::release();
}else{
	echo "[]";
}
?>