# Neat Index

- Version: 0.1
- Author: Rowan Lewis <me@rowanlewis.com>
- Date: 18th June 2010
- Github Repository: <http://github.com/rowan-lewis/autoindex>

*Neat Index* is an alternative to the Apache (or other web server) auto index module.
You can show readme files in your directory listing, control what files can be
listet, and completely customise the generated output using XSLT.

### Server Requirements

- PHP 5.3 or above
- PHP's LibXML and LibXSL extensions
- A UNIX like operating system

### Installation on Apache

1.	Download Neat Index from GitHub and extract the archive
2.	Move the extracted directory to the document root of your webserver and rename to `.autoindex`
3.	Disable Apache's own auto index module:
	
		Options -Indexes
	
4.	Add `.autoindex/index.php` as a directory index:

		DirectoryIndex index.html index.php /.autoindex/index.php

5.	Sit back and marvel at your fancy looking index pages.

### Screenshot

![Screenshot](http://github.com/rowan-lewis/autoindex/raw/master/screenshot.png)