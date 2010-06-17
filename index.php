<?php
/*------------------------------------------------------------------------------
	Execute
------------------------------------------------------------------------------*/
	
	include_once 'libs/autoindex.php';
	include_once 'libs/markdown.php';
	
	$view = new View();
	$view->load('standard');
	
/*----------------------------------------------------------------------------*/
	
	function markdown_closure($text) {
		return Markdown($text);
	}
	
	function html_closure($text) {
		return $text;
	}
	
	// Ignore OSX meta data:
	$view->ignore('%/\.(Apple|DS_)%');
	$view->ignore('%/(Network Trash Folder|Temporary Items)$%');
	
	// Ignore hidden files:
	$view->ignore('%/\.(?!autoindex)%');
	
	// Ignore itself:
	//$view->ignore('%/\.autoindex(/|$)%');
	
	// Add readme files:
	$view->readme('%/readme(\.txt)?$%i');
	$view->readme('%/readme\.md$%i', 'markdown_closure');
	$view->readme('%/readme\.html?$%i', 'html_closure');
	
	$view->execute();
	$view->display();
	
/*----------------------------------------------------------------------------*/
?>