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

namespace SkySQL\APICLIENT;

use SkySQL\APICLIENT\RequestGet;

/**
 * @author Massimo Siani
 *
 */
class MonitorRawData extends RequestGet {
	protected $monitorKey;
	protected $systemId;
	protected $nodeId;
	
	/**
	 * The following constructors have been implemented:
	 * construct($systemid, $monitorid, $apiKeyId, $apiKey)
	 * construct($systemid, $nodeid, $monitorid, $apiKeyId, $apiKey)
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
	/**
	 */
	private function construct4($systemid, $monitorid, $apiKeyId, $apiKey) {
		$uri = "system/${systemid}/monitor/${monitorid}/rawdata";
		parent::__construct ( $uri, $apiKeyId, $apiKey );
		$this->monitorKey = $monitorid;
		$this->systemId = $systemid;
		$this->nodeId = 0;
	}
	/**
	 */
	private function construct5($systemid, $nodeid, $monitorid, $apiKeyId, $apiKey) {
		$uri = "system/${systemid}/node/${nodeid}/monitor/${monitorid}/rawdata";
		parent::__construct ( $uri, $apiKeyId, $apiKey );
		$this->monitorKey = $monitorid;
		$this->systemId = $systemid;
		$this->nodeId = $nodeid;
	}
	
	/**
	 * Returns the Monitor unique key.
	 */
	public function getMonitorKey() {
		return $this->monitorKey;
	}
	/**
	 * Returns the System Id.
	 */
	public function getSystemId() {
		return $this->systemid;
	}
	/**
	 * Returns the Node Id.
	 */
	public function getNodeId() {
		return $this->nodeId;
	}
}