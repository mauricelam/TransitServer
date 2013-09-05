<?php 
function send_mail($emailaddress, $fromaddress, $emailsubject, $body)
{
	$eol="\r\n";
	$mime_boundary=md5(time());

	$headers .= 'From: '.$fromaddress.$eol;
	$headers .= 'Reply-To: '.$fromaddress.$eol;
	$headers .= 'Return-Path: '.$fromaddress.$eol;    // these two to set reply address
	$headers .= "Message-ID: <".$now." TheSystem@".$_SERVER['SERVER_NAME'].">".$eol;
	$headers .= "X-Mailer: PHP v".phpversion().$eol;          // These two to help avoid spam-filters

	$headers .= 'MIME-Version: 1.0'.$eol;
	$headers .= "Content-Type: text/html; charset=\"ISO-8859-1\"; boundary=\"".$mime_boundary."\"".$eol;
	$msg = "";
	$msg .= $body.$eol.$eol;
	ini_set(sendmail_from,$fromaddress);  // the INI lines are to force the From Address to be used !
	mail($emailaddress, $emailsubject, $msg, $headers);
	ini_restore(sendmail_from);
}

function store_mail($emailaddress, $body)
{
	send_mail($emailaddress, 'transitcumtd@gmail.com', 'Transit bug report', $body);
}

function bug_report($error) {
	store_mail('mauriceprograms@gmail.com', $error);
}
?>