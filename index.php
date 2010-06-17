<?php
/*------------------------------------------------------------------------------
	Execute
------------------------------------------------------------------------------*/
	
	include_once 'libs/autoindex.php';
	include_once 'libs/markdown.php';
	
	$view = new View();
	$view->load('standard');
	
/*----------------------------------------------------------------------------*/
	
	// Ignore OSX meta data:
	$view->ignore('%/\.(Apple|DS_)%');
	$view->ignore('%/(Network Trash Folder|Temporary Items)$%');
	
	// Ignore hidden files:
	//$view->ignore('%/\.%');
	
	// Ignore itself:
	//$view->ignore('%/\.autoindex(/|$)%');
	
	// Add readme files:
	$view->readme('%/readme(\.txt)?$%i');
	$view->readme('%/readme\.md$%i', function($text) {
		return Markdown($text);
	});
	$view->readme('%/readme\.html?$%i', function($text) {
		return $text;
	});
	
	$view->execute();
	$view->display();
	
/*----------------------------------------------------------------------------*/
?>