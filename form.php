<?php
	require_once('vendor/autoload.php');
	require_once( "Sender.class.php" );
	require_once( "DB.class.php" );
	
	
	$message = $_POST['message'];
	$sender = new Sender();
	$result = $sender->execute($message);
?>
