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
	
	// Allow anything:
	$view->rule('%.%', true);
	
	// Ignore OSX meta data:
	$view->rule('%/\.(Apple|DS_)%', false);
	$view->rule('%/(Network Trash Folder|Temporary Items)$%', false);
	
	// Ignore hidden files:
	$view->rule('%/\.%', false);
	
	// Ignore itself:
	$view->rule('%/\.?autoindex(/|$)%', true);
	
	// Add readme files:
	$view->readme('%/readme(\.txt)?$%i');
	$view->readme('%/readme\.md$%i', true, 'markdown_closure');
	$view->readme('%/readme\.html?$%i', true, 'html_closure');
	
	$view->execute()->display();
	
/*----------------------------------------------------------------------------*/
?>