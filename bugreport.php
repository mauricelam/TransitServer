<?php
include_once "include.php";
include_once "mail.php";

$sql = "SELECT * FROM batchmail";
$query = mysql_query($sql);
$body = "";
while ($x = mysql_fetch_assoc($query)) {
	$body .= $x['body']."\r\n\r\n";
}
if ($body) {
	send_mail("mauriceprograms@gmail.com", "MTDistrict", "Error in MTD app", $body);
}
$sql2 = "TRUNCATE TABLE batchmail";
$query2 = mysql_query($sql2);
mysql_close();
echo "done";
?>