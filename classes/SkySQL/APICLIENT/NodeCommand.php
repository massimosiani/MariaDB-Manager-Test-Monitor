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

use SkySQL\APICLIENT\RequestPost;
use SkySQL\APICLIENT\Node;

class NodeCommand extends RequestPost {
	
	/**
	 * 
	 */
	public function __construct($commandName, $parameters, $apiKeyId, $apiKey) {
		parent::__construct("command/" . $commandName, $parameters, $apiKeyId, $apiKey);
	}
	
	/**
	 * For a given amount of time, continuously retrieves the state of the node
	 * to check whether it reached the desidered final state. Returns true if
	 * the state has been reached in the given timeout, false otherwise.
	 * 
	 * @param string $state			the wanted final state
	 * @param integer $maxTimeout	the timeout for the state to change
	 * @return boolean		true if the state changed to given one, false otherwise
	 */
	public function waitForState(string $state, integer $maxTimeout = 60) {
		$node = new Node ( $systemid, $nodeid, $apikeyid, $apikey );
		for($count = 0; $count < $maxTimeout; $count ++) {
			sleep ( 1 );
			$nodeInfo = $node->go ();
			$nodeState = $nodeInfo ['node'] ['state'];
			if ($nodeState == $state) {
				return true;
			}
		}
		return false;
	}
}