<?php
/**
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

namespace SkySQL\APICLIENT;

use SkySQL\APICLIENT\Request;

class RequestGet extends Request {
	
	/**
	 * The following constructors have been implemented:
	 * construct()
	 * construct($uri)
	 * construct(mixed $apiKeyId, string $apiKey)
	 * construct($uri, $apiKeyId, $apiKey)
	 */
	public function __construct($uri, $apiKeyId, $apiKey) {
		parent::__construct($uri, $apiKeyId, $apiKey);
		$this->method = 'GET';
	}
}