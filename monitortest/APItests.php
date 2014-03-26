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

require_once '../API/tasks/Define.php';

foreach(glob("../API/{classes,nodes,systems,tasks}/*.php", GLOB_BRACE) as $file) {
	require_once($file);
}

use com\skysql\test\common\tasks\NodeIsolate;
use com\skysql\test\common\tasks\NodeRejoin;
use com\skysql\test\common\tasks\NodeRestart;
use com\skysql\test\common\tasks\NodeStart;
use com\skysql\test\common\tasks\NodeStop;
use com\skysql\test\common\nodes\Node;
use com\skysql\test\common\systems\System;

$apikeyid = 5;
$apikey = "84e915085ab3d2673ac5d5f99946e359";
$adminUser = "admin";
$systemid = 1;
$nodeid = 1;
$commandParameters = "systemid=$systemid&nodeid=$nodeid&username=$adminUser";
$timeout = 15;

$node = new Node( $systemid, $nodeid, $apikeyid, $apikey );
$numberOfTests = 0;
$failedTests = 0;

$nodeStart = new NodeStart( $commandParameters, $apikeyid, $apikey );
$nodeStart->go ();
sleep($timeout * 2);
$lastNodeState = $node->go ();
if ($lastNodeState['node']['state'] == "joined") {
	echo "Node Start: SUCCESS" . "\n";
} else {
	echo "Node Start: FAIL, try with a longer timeout?" . "\n";
	$failedTests++;
}
$numberOfTests++;

$nodeIsolate = new NodeIsolate( $commandParameters, $apikeyid, $apikey );
$nodeIsolate->go ();
sleep($timeout * 2);
$lastNodeState = $node->go ();
if ($lastNodeState['node']['state'] == "isolated") {
	echo "Node Isolate: SUCCESS" . "\n";
} else {
	echo "Node Isolate: FAIL, try with a longer timeout?" . "\n";
	$failedTests++;
}
$numberOfTests++;

$nodeRejoin = new NodeRejoin( $commandParameters, $apikeyid, $apikey );
$nodeRejoin->go ();
sleep($timeout * 2);
$lastNodeState = $node->go ();
if ($lastNodeState['node']['state'] == "joined") {
	echo "Node Rejoin: SUCCESS" . "\n";
} else {
	echo "Node Rejoin: FAIL, try with a longer timeout?" . "\n";
	$failedTests++;
}
$numberOfTests++;

$nodeRestart = new NodeRestart( $commandParameters, $apikeyid, $apikey );
$nodeRestart->go ();
sleep($timeout * 2);
$lastNodeState = $node->go ();
if ($lastNodeState['node']['state'] == "joined") {
	echo "Node Restart: SUCCESS" . "\n";
} else {
	echo "Node Restart: FAIL, try with a longer timeout?" . "\n";
	$failedTests++;
}
$numberOfTests++;

$nodeStop = new NodeStop( $commandParameters, $apikeyid, $apikey );
$nodeStop->go ();
sleep($timeout);
$lastNodeState = $node->go ();
if ($lastNodeState['node']['state'] == "down") {
	echo "Node Stop: SUCCESS" . "\n";
} else {
	echo "Node Stop: FAIL, try with a longer timeout?" . "\n";
	$failedTests++;
}
$numberOfTests++;


$successfulTests = $numberOfTests - $failedTests;
echo "Tests done: $numberOfTests, Successful: $successfulTests, Failed: $failedTests \n";