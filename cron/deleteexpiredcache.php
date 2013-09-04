<?php 

// function deleteExpiredCache(){
// 	include __DIR__ . "/../sql.php";
// 	$sql = "DELETE FROM `apicache` WHERE (`changeset` = '' AND `updated` < DATE_SUB(NOW(), INTERVAL 30 second)) OR `updated` < DATE_SUB(NOW(), INTERVAL 2 day)";
// 	$query = mysql_query($sql);
// 	SQLHelper::release();
// }

// $log = __DIR__ . "/last_delete";

// $lastDelete = intval(file_get_contents($log));

// if (time() - $lastDelete > 600) {
// 	echo "Deleting expired cache";
// 	deleteExpiredCache();
// 	file_put_contents($log, time());
// } else {
// 	echo "not 10 minutes yet";
// }

?>