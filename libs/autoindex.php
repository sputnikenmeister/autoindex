<?php
/*------------------------------------------------------------------------------
	
	Copyright (c) 2008, Rowan Lewis, All rights reserved.
	
	Redistribution and use in source and binary forms, with or without
	modification, are permitted provided that the following conditions are met:
		* Redistributions of source code must retain the above copyright notice,
		  this list of conditions and the following disclaimer.
		* Redistributions in binary form must reproduce the above copyright
		  notice, this list of conditions and the following disclaimer in the
		  documentation and/or other materials provided with the distribution.
		* Neither the name of the PixelCarnage nor the names of its contributors
		  may be used to endorse or promote products derived from this software
		  without specific prior written permission.
	
	THIS SOFTWARE IS PROVIDED BY ROWAN LEWIS "AS IS" AND ANY EXPRESS OR IMPLIED
	WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF
	MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO
	EVENT SHALL ROWAN LEWIS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
	SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO,
	PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS;
	OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY,
	WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR
	OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF
	ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
	
--------------------------------------------------------------------------------
	Defines
------------------------------------------------------------------------------*/
	
	define('LOCATION_SOURCES', '/sources/%/source.php');
	define('LOCATION_VIEWS', '/views/%/view.xsl');
	
/*------------------------------------------------------------------------------
	Resource
------------------------------------------------------------------------------*/
	
	class Resource {
		protected $filename = '';
		
		static public function locateResource($handle, $template) {
			$template = str_replace('%', self::serializeResource($handle), $template);
			$directories = array(getcwd());
			$location = '';
			
			foreach ($directories as $directory) {
				if (is_readable($directory . $template)) {
					$location = $directory . $template; break;
				}
			}
			
			return $location;
		}
		
		static public function serializeResource($name) {
			$name = preg_replace('%[^\w\d\.]%', ' ', $name);
			$name = preg_replace('%\s+%', '-', $name);
			
			return trim(strtolower($name), '-');
		}
	}
	
/*------------------------------------------------------------------------------
	View
------------------------------------------------------------------------------*/
	
	class View extends Resource {
		protected $processor = null;
		protected $stylesheet = null;
		protected $document = null;
		protected $rules = array();
		protected $readmes = array();
		
		public function load($handle) {
			$this->filename = self::locateResource($handle, LOCATION_VIEWS);
			$this->stylesheet = new DOMDocument();
			$this->stylesheet->load($this->filename);
			
			$this->processor = new XSLTProcessor();
			$this->processor->importStyleSheet($this->stylesheet);
		}
		
		public function execute() {
			$remote_path = preg_replace('/\?.*/', null, $_SERVER['REQUEST_URI']);
			$local_path = realpath($_SERVER['DOCUMENT_ROOT'] . $remote_path);
			
			$this->document = new DOMDocument('1.0', 'UTF-8');
			$this->document->formatOutput = true;
			
			$root = $this->document->createElement('index');
			$root->setAttribute('resource-path', dirname($_SERVER['SCRIPT_NAME']));
			$root->setAttribute('remote-path', $remote_path);
			$root->setAttribute('local-path', $local_path);
			$root->setAttribute('name', basename($local_path));
			$this->document->appendChild($root);
			
			$this->generate($remote_path, $local_path, $root);
			
			return $this;
		}
		
		public function debug() {
			header('content-type: text/plain');
			
			echo $this->document->saveXML();
		}
		
		public function display() {
			if (isset($_REQUEST['ascending'])) {
				$this->processor->setParameter(null, 'column', $_REQUEST['ascending']);
				$this->processor->setParameter(null, 'direction', 'ascending');
			}
			
			else if (isset($_REQUEST['descending'])) {
				$this->processor->setParameter(null, 'column', $_REQUEST['descending']);
				$this->processor->setParameter(null, 'direction', 'descending');
			}
			
			echo $this->processor->transformToXML($this->document);
		}
		
		protected function generate($remote_path, $local_path, $parent) {
			chdir($local_path);
			
			$paths = glob($local_path . '/{,.}*', GLOB_BRACE);
			$readme = null;
			
			// List files:
			foreach ($paths as $path) {
				if (!$this->isAllowed($path)) continue;
				
				if (is_null($readme) and $this->isReadme($path)) {
					$readme = $this->isReadme($path);
					$readme->path = $path;
				}
				
				$name = basename($path);
				
				$item = $this->document->createElement('item');
				
				if (is_dir($path)) {
					$item->setAttribute('remote-path', "{$remote_path}{$name}/");
					$item->setAttribute('type', 'directory');
				}
				
				else {
					$item->setAttribute('remote-path', "{$remote_path}{$name}");
					$item->setAttribute('type', 'file');
				}
				
				if (is_link($path)) {
					$link = realpath(readlink($path));
					
					if (strpos($link, $local_path) === 0) {
						$link = substr($link, strlen($local_path) + 1);
						$item->setAttribute('link-path', $link);
					}
				}
				
				$item->setAttribute('local-path', $path);
				$item->setAttribute('name', $name);
				$item->setAttribute('size', filesize($path));
				$item->setAttribute('mime', mime_content_type($path));
				
				$timestamp = filemtime($path);
				$date = $this->document->createElement('date');
				$date->setAttribute('timestamp', $timestamp);
				$date->setAttribute('time', date('H:s', $timestamp));
				$date->appendChild($this->document->createTextNode(
					date('Y-m-d', $timestamp)
				));
				$item->appendChild($date);
				$parent->appendChild($item);
			}
			
			// Add readme:
			if (!is_null($readme) and is_readable($readme->path)) {
				// Load the text as HTML for sanity
				$document = new DOMDocument();
				$document->loadHTML(call_user_func(
					$readme->callback,
					file_get_contents($readme->path)
				));
				$xpath = new DOMXPath($document);
				
				// Extract the sanatised text:
				$text = ''; $nodes = $xpath->query('/html/body/node()');
				
				foreach ($nodes as $node) {
					$text .= $document->saveXML($node);
				}
				
				$fragment = $this->document->createDocumentFragment();
				$fragment->appendXML($text);
				
				$item = $this->document->createElement('readme');
				$item->appendChild($fragment);
				$parent->appendChild($item);
			}
		}
		
		public function allow($expression) {
			$this->rules[] = (object)array(
				'expression'	=> $expression,
				'allowed'		=> true
			);
			
			return $this;
		}
		
		public function deny($expression) {
			$this->rules[] = (object)array(
				'expression'	=> $expression,
				'allowed'		=> false
			);
			
			return $this;
		}
		
		public function isAllowed($path) {
			$name = basename($path);
			$state = false;
			
			if ($name == '.' or $name == '..') return false;
			
			foreach ($this->rules as $data) {
				if (preg_match($data->expression, $path)) $state = $data->allowed;
			}
			
			return $state;
		}
		
		public function readme($expression, $callback = null) {
			if (is_null($callback)) $callback = function($text) {
				return '<pre>' . htmlentities($text) . '</pre>';
			};
			
			$this->readmes[] = (object)array(
				'expression'	=> $expression,
				'callback'		=> $callback
			);
			
			return $this;
		}
		
		public function isReadme($path) {
			$state = false;
			
			foreach ($this->readmes as $data) {
				if (preg_match($data->expression, $path)) $state = $data;
			}
			
			return $state;
		}
	}
	
/*----------------------------------------------------------------------------*/
?>