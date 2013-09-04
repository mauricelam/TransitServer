<?php 

$query = trim($_GET['text']);
if($query && $query!=""){
	include "sql.php";
	$stops = array();
	
	$code = $query;
	if(stripos($code, "MTD")===0){
		$code = substr($code, 3);
	}
	if(is_numeric($code)){
		$code = intval($code);
		$sql = "SELECT * FROM stops WHERE code = $code";
	}else{
	
		$query = mysql_real_escape_string($query);
		$ftquery = "+".str_replace(' ', ' +', $query);
		$sql = "SELECT stops.*, (MATCH(stops.name) AGAINST('$ftquery' IN BOOLEAN MODE) + SUM(suggestions.frequency)) as score FROM stops"
			. " INNER JOIN suggestions ON stops.code = suggestions.stopcode WHERE"
			. " ((MATCH(suggestions.query) AGAINST('$ftquery' IN BOOLEAN MODE) OR suggestions.query LIKE '$query%') AND suggestions.frequency > 0)"
			. " OR (MATCH(stops.name) AGAINST('$ftquery' IN BOOLEAN MODE)) GROUP BY stops.id ORDER BY score DESC LIMIT 50";
	}
	$query = mysql_query($sql);

	while($x = mysql_fetch_assoc($query)){
		//var_dump($x);
		$s = $stops[] = new stdClass();
		$s->n = $x['name'];
		$s->c = $x['code'];
		$s->q = $x['query'];
		//$s->s = $x['score'];
	}
	echo json_encode($stops);
	SQLhelper::release();
}else{
	echo "[]";
}
?>