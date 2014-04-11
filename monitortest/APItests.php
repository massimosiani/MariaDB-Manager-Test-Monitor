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
require_once '../API/define/Define.php';

foreach ( glob ( "../API/{classes,monitors,nodes,systems,tasks}/*.php", GLOB_BRACE ) as $file ) {
	require_once ($file);
}
foreach ( glob ( "./classes/{nodes,systems}/*.php", GLOB_BRACE ) as $file ) {
	require_once ($file);
}

use com\skysql\test\classes\nodes\ExecuteRemote;
use com\skysql\test\common\monitors\MonitorData;
use com\skysql\test\common\tasks\NodeIsolate;
use com\skysql\test\common\tasks\NodeRejoin;
use com\skysql\test\common\tasks\NodeRestart;
use com\skysql\test\common\tasks\NodeStart;
use com\skysql\test\common\tasks\NodeStop;
use com\skysql\test\common\nodes\Node;
use com\skysql\test\common\systems\System;

gc_enable ();
$apikeyid = 5;
$apikey = "84e915085ab3d2673ac5d5f99946e359";
$adminUser = "admin";
$systemid = 1;
$nodeid = 4;
$commandParameters = "systemid=$systemid&username=$adminUser";
$singleCommandParameters = $commandParameters . "&nodeid=$nodeid";
$timeout = 40;
$numberOfTests = 0;
$failedTests = 0;
/*
// Ensure there are at least four nodes
$systemInfo = new System ( $systemid, $apikeyid, $apikey );
$lastSystemInfo = $systemInfo->go ();
if (count ( $lastSystemInfo ['system'] ['nodes'] ) < 4) {
	echo "ERROR: there must be at least four nodes.\n";
	exit ( 1 );
}
// Ensure the nodes and the system are in the down state
if ($lastSystemInfo ['system'] ['state'] != "down") {
	echo "ERROR: the system must be down.\n";
	exit ( 1 );
}

// Nodes up, system running
for($count = 1; $count <= 4; $count ++) {
	echo "Node $count Start: ";
	$nodeStart = new NodeStart ( $commandParameters . "&nodeid=$count", $apikeyid, $apikey );
	$nodeStart->go ();
	sleep ( $timeout * 2 );
	$nodeInfo = new Node ( $systemid, $count, $apikeyid, $apikey );
	$lastNodeState = $nodeInfo->go ();
	if ($lastNodeState ['node'] ['state'] == "joined") {
		echo "SUCCESS\n";
	} else {
		echo "FAIL, try with a longer timeout?\n";
		$failedTests ++;
	}
	$numberOfTests ++;
}
$lastSystemInfo = $systemInfo->go ();
if ($lastSystemInfo ['system'] ['state'] == "running") {
	echo "System Running state: SUCCESS" . "\n";
} else {
	echo "System Running state: FAIL, try with a longer timeout?" . "\n";
	$failedTests ++;
}
$numberOfTests ++;

// Isolate
echo "Node $nodeid Isolate: ";
$nodeIsolate = new NodeIsolate ( $singleCommandParameters, $apikeyid, $apikey );
$nodeIsolate->go ();
sleep ( $timeout * 2 );
$lastNodeState = $nodeInfo->go ();
if ($lastNodeState ['node'] ['state'] == "isolated") {
	echo "SUCCESS" . "\n";
} else {
	echo "FAIL, try with a longer timeout?" . "\n";
	$failedTests ++;
}
$numberOfTests ++;

// Rejoin
echo "Node $nodeid Rejoin: ";
$nodeRejoin = new NodeRejoin ( $singleCommandParameters, $apikeyid, $apikey );
$nodeRejoin->go ();
sleep ( $timeout * 2 );
$lastNodeState = $nodeInfo->go ();
if ($lastNodeState ['node'] ['state'] == "joined") {
	echo "SUCCESS" . "\n";
} else {
	echo "FAIL, try with a longer timeout?" . "\n";
	$failedTests ++;
}
$numberOfTests ++;

// Restart
echo "Node $nodeid Restart: ";
$nodeRestart = new NodeRestart ( $singleCommandParameters, $apikeyid, $apikey );
$nodeRestart->go ();
sleep ( $timeout * 2 );
$lastNodeState = $nodeInfo->go ();
if ($lastNodeState ['node'] ['state'] == "joined") {
	echo "SUCCESS" . "\n";
} else {
	echo "FAIL, try with a longer timeout?" . "\n";
	$failedTests ++;
}
$numberOfTests ++;

// Stop all
for($count = 4, $remainingNodes = 4; $count >= 1; $count --) {
	echo "Node $count Stop: ";
	$nodeStop = new NodeStop ( $commandParameters . "&nodeid=$count", $apikeyid, $apikey );
	$nodeStop->go ();
	sleep ( $timeout );
	$nodeInfo = new Node ( $systemid, $count, $apikeyid, $apikey );
	$lastNodeState = $nodeInfo->go ();
	if ($lastNodeState ['node'] ['state'] == "down") {
		echo "SUCCESS" . "\n";
		$remainingNodes --;
	} else {
		echo "FAIL, try with a longer timeout?" . "\n";
		$failedTests ++;
	}
	$numberOfTests ++;
	if ($remainingNodes == 0) {
		$expectedSystemState = "down";
	} elseif ($remainingNodes > 0 && $remainingNodes <= 2) {
		$expectedSystemState = "limited-availability";
	} elseif ($remainingNodes >= 3) {
		$expectedSystemState = "available";
	}
	$lastSystemInfo = $systemInfo->go ();
	echo "System $expectedSystemState state: ";
	if ($lastSystemInfo ['system'] ['state'] == $expectedSystemState) {
		echo "SUCCESS\n";
	} else {
		echo "FAIL, try with a longer timeout?\n";
		$failedTests ++;
	}
	$numberOfTests ++;
}

sleep($timeout);*/
// Node Monitors
$monitorsMetadata = array (
		"availability" => array (
				"expected" => "0.0",
				"system" => "0.0" 
		),
		"capacity" => array (
				"expected" => "0.0" 
		),
		"connections" => array (
				"expected" => "0.0" 
		),
		"clustersize" => array (
				"expected" => "0.0" 
		) 
);
$deltaMonitorsMetadata = array (
		"com_create_db" => array (
				"expected" => "0.0",
				"system" => "0.0"
		)
);
foreach ( $monitorsMetadata as $monitorKey => $value ) {
	for($count = 1; $count <= 4; $count ++) {
		$nodeMonitorObj = new MonitorData ( $systemid, $count, $monitorKey, $apikeyid, $apikey );
		$failedTests += nodeMonitor ( $nodeMonitorObj, $value ['expected'] );
		$numberOfTests ++;
	}
}
foreach ( $monitorsMetadata as $monitorKey => $value ) {
	if (isset ( $value ['system'] )) {
		$systemMonitorObj = new MonitorData ( $systemid, $monitorKey, $apikeyid, $apikey );
		$failedTests += systemMonitor ( $systemMonitorObj, $value ['system'] );
		$numberOfTests ++;
	}
}
foreach ( $deltaMonitorsMetadata as $monitorKey => $value ) {
	for($count = 1; $count <= 4; $count ++) {
		$nodeMonitorObj = new MonitorData ( $systemid, $count, $monitorKey, $apikeyid, $apikey );
		$failedTests += nodeMonitor ( $nodeMonitorObj, $value ['expected'] );
		$numberOfTests ++;
	}
}
foreach ( $deltaMonitorsMetadata as $monitorKey => $value ) {
	if (isset ( $value ['system'] )) {
		$systemMonitorObj = new MonitorData ( $systemid, $monitorKey, $apikeyid, $apikey );
		$failedTests += systemMonitor ( $systemMonitorObj, $value ['system'] );
		$numberOfTests ++;
	}
}
function nodeMonitor(&$monitorDataObj, $valueExpected) {
	$lastNodeMonitor = $monitorDataObj->go ();
	$valueObs = end ( $lastNodeMonitor );
	echo "Node " . $monitorDataObj->getNodeId () . " " . $monitorDataObj->getMonitorKey ();
	echo ":    expected $valueExpected    got $valueObs:    ";
	if ($valueObs == $valueExpected) {
		echo "SUCCESS\n";
		return 0;
	} else {
		echo "FAIL\n";
		return 1;
	}
}
function systemMonitor(&$monitorDataObj, $valueExpected) {
	$lastSystemMonitor = $monitorDataObj->go ();
	$valueObs = end ( $lastSystemMonitor );
	echo "System " . $monitorDataObj->getMonitorKey ();
	echo ":    expected $valueExpected    got $valueObs:    ";
	if ($valueObs == $valueExpected) {
		echo "SUCCESS\n";
		return 0;
	} else {
		echo "FAIL\n";
		return 1;
	}
}

function executeCommand ($systemid, $nodeid, $apikeyid, $apikey, $command = null) {
	$node = new Node($systemid, $nodeid, $apikeyid, $apikey);
	$nodeInfo = $node->go();
	$nodeIP = $nodeInfo['node']['privateip'];
	ExecuteRemote::setup($nodeIP, 'root', 'skysql');
	$output = ExecuteRemote::executeScriptSSH("echo 1");
}
executeCommand ($systemid, $nodeid, $apikeyid, $apikey);

$successfulTests = $numberOfTests - $failedTests;
echo "Tests done: $numberOfTests, Successful: $successfulTests, Failed: $failedTests \n";