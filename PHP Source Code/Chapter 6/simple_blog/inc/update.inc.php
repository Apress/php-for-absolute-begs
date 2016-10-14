<?php

// Include the functions so we can create a URL
include_once 'functions.inc.php';

if($_SERVER['REQUEST_METHOD']=='POST'
	&& $_POST['submit']=='Save Entry'
	&& !empty($_POST['page'])
	&& !empty($_POST['title'])
	&& !empty($_POST['entry']))
{
	// Create a URL to save in the database
	$url = makeUrl($_POST['title']);

	// Include database credentials and connect to the database
	include_once 'db.inc.php';
	$db = new PDO(DB_INFO, DB_USER, DB_PASS);

	// Save the entry into the database
	$sql = "INSERT INTO entries (page, title, entry, url)
			VALUES (?, ?, ?, ?)";
	$stmt = $db->prepare($sql);
	$stmt->execute(
		array($_POST['page'], $_POST['title'], $_POST['entry'], $url)
	);
	$stmt->closeCursor();

	// Sanitize the page information for use in the success URL
	$page = htmlentities(strip_tags($_POST['page']));

	// Send the user to the new entry
	header('Location: /simple_blog/'.$page.'/'.$url);
	exit;
}

else
{
	header('Location: ../');
	exit;
}

?>