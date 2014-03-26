<?php
/*
 * Part of the MariaDB Manager Test Suite.
 * 
 * This file is distributed as part of the MariaDB Manager.  It is free
 * software: you can redistribute it and/or modify it under the terms of the
 * GNU General Public License as published by the Free Software Foundation,
 * version 2.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more
 * details.
 *
 * You should have received a copy of the GNU General Public License along with
 * this program; if not, write to the Free Software Foundation, Inc., 51
 * Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
 *
 * Copyright 2014 SkySQL Corporation Ab
 *
 * Author: Massimo Siani
 * Date: March 2014
 */

namespace com\skysql\test\common;

class Request {
	protected $dateHeader;
	protected $acceptHeader = "application/json";
	protected $authorizationHeader = 'api-auth-m_apiKeyID-';
	protected $charsetHeader = "utf-8";
	protected $contentLengthHeader;
	protected $contentTypeHeader = "application/x-www-form-urlencoded";
	protected $apiVersionHeader = "1.1";
	protected $headers = array ();
	protected $apiKeyId;
	protected $apiKey;
	protected $uri;
	protected $baseUri = "http://localhost/restfulapi/";
	protected $fullUri;
	protected $method = 'GET';
	protected $parameters;
	protected $lastResponse = "";
	
	/**
	 * The following constructors have been implemented:
	 * construct()
	 * construct(string $uri)
	 * construct(mixed $apiKeyId, string $apiKey)
	 * construct($uri, $apiKeyId, $apiKey)
	 * construct($uri, string $parameters, $apiKeyId, $apiKey)
	 */
	public function __construct() {
		$numArgs = func_num_args ();
		$args = func_get_args ();
		if (method_exists ( $this, $f = 'construct' . $numArgs )) {
			call_user_func_array ( array (
					&$this,
					$f 
			), $args );
		}
	}
	protected function construct0() {
	}
	protected function construct1($uri) {
		$this->uri = $uri;
	}
	protected function construct2($apiKeyId, $apiKey) {
		$this->apiKeyId = $apiKeyId;
		$this->apiKey = $apiKey;
	}
	protected function construct3($uri, $apiKeyId, $apiKey) {
		$this->uri = $uri;
		$this->apiKeyId = $apiKeyId;
		$this->apiKey = $apiKey;
	}
	protected function construct4($uri, $parameters, $apiKeyId, $apiKey) {
		$this->construct3($uri, $apiKeyId, $apiKey);
		$this->parameters = $parameters;
	}
	
	/**
	 *
	 * @param mixed $apiKeyId        	
	 * @param string $apiKey        	
	 */
	public function setApiKeyProperty(mixed $apiKeyId, string $apiKey) {
		$this->apiKeyId = $apiKeyId;
		$this->apiKey = $apiKey;
	}
	/**
	 * Performs the request.
	 */
	public function go() {
		$this->fullUri = $this->baseUri . $this->uri;
		$this->setHeaders ();
		$this->lastResponse = $this->do_request ( $this->fullUri, $this->parameters, $this->headers );
		return json_decode($this->lastResponse, true);
	}
	
	/**
	 * Sets the headers.
	 *
	 * @param array $headers        	
	 */
	protected function setHeaders() {
		$this->setDateHeader ();
		$this->setAcceptHeader ();
		$this->setAuthHeader ();
		$this->setCharsetHeader ();
		$this->setContentLengthHeader ();
		$this->setContentTypeHeader ();
		$this->setApiVersionHeader ();
	}
	protected function setAcceptHeader() {
		$this->headers ['Accept'] = $this->acceptHeader;
	}
	protected function setCharsetHeader() {
		$this->headers ['Charset'] = $this->charsetHeader;
	}
	protected function setContentLengthHeader() {
	//	$this->contentLengthHeader = $this->message->length;
		$this->headers ['ContentLength'] = $this->contentLengthHeader;
	}
	protected function setContentTypeHeader() {
		$this->headers ['Content-Type'] = $this->contentTypeHeader;
	}
	protected function setApiVersionHeader() {
		$this->headers ['X-SkySQL-API-Version'] = $this->apiVersionHeader;
	}
	/**
	 * Sets the date.
	 */
	protected function setDateHeader() {
		$this->dateHeader = date ( 'r' );
		$this->headers ['Date'] = $this->dateHeader;
	}
	/**
	 * Sets the authorization header.
	 */
	protected function setAuthHeader() {
		if (! isset ( $this->dateHeader )) {
			setDateHeader ();
		}
		$checksum = \md5 ( $this->uri . $this->apiKey . $this->dateHeader );
		$this->authorizationHeader = 'api-auth-' . $this->apiKeyId . '-' . $checksum;
		$this->headers ['Authorization'] = $this->authorizationHeader;
	}
	
	/**
	 * @param string $url
	 * @param array $data
	 * @param array $headers
	 * @return string
	 */
	protected function do_request($url, $parameters, $headers = null) {
		$headers_option = array ();
		foreach ( $headers as $key => $value ) {
			$headers_option [] = "$key: $value";
		}
		$options = array (
				'http' => array (
						'header' => $headers_option,
						'method' => $this->method,
						'content' => $parameters
				) 
		);
		$context = stream_context_create ( $options );
		$result = file_get_contents ( $url, false, $context );
		return $result;
	}
	
	public function getLastResponse() {
		return $this->lastResponse;
	}
}