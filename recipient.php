<?php
	header("Content-Type: text/html; charset=utf-8");

	require_once('vendor/autoload.php');
	require_once( "Receiver.class.php" );
	require_once( "DB.class.php" );

	$receiver = new Receiver();
	$receiver->listen();
?>