<?php
/*------------------------------------------------------------------------------
	Execute
------------------------------------------------------------------------------*/
	
	include_once 'autoindex.php';
	
	$view = new View();
	$view->load('standard');
	
/*----------------------------------------------------------------------------*/
	
	// Ignore OSX meta data:
	$view->ignore('%/\.(Apple|DS_)%');
	$view->ignore('%/(Network Trash Folder|Temporary Items)$%');
	
	// Ignore hidden files:
	$view->ignore('%/\.%');
	
	// Ignore itself:
	$view->ignore('%/autoindex(/|$)%');
	
	// Add readme files:
	$view->readme('%/readme(\.txt)?$%i');
	
	$view->execute();
	$view->display();
	
/*----------------------------------------------------------------------------*/
?>