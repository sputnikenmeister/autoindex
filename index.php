<?php
/*------------------------------------------------------------------------------
	Execute
------------------------------------------------------------------------------*/
	
	include_once 'libs/autoindex.php';
	include_once 'libs/markdown.php';
	
	$view = new View();
	$view->load('standard');
	
/*----------------------------------------------------------------------------*/
	
	// Allow anything:
	$view->allow('%.%');
	
	// Ignore OSX meta data:
	$view->deny('%/\.(Apple|DS_)%');
	$view->deny('%/(Network Trash Folder|Temporary Items)$%');
	
	// Ignore hidden files:
	$view->deny('%/\.%');
	
	// Allow itself:
	$view->allow('%/\.?autoindex(/|$)%');
	
	// Add readme files:
	$view->readme('%/readme(\.txt)?$%i');
	$view->readme('%/readme\.md$%i', function($text) {
		return Markdown($text);
	});
	$view->readme('%/readme\.html?$%i', function($text) {
		return $text;
	});
	
	$view->execute()->display();
	
/*----------------------------------------------------------------------------*/
?>