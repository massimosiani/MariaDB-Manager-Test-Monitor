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

namespace SkySQL\Manager\Testing\Monitor;

use SkySQL\COMMON\ExecuteRemote;
use SkySQL\APICLIENT\MonitorData;
use SkySQL\APICLIENT\NodeIsolate;
use SkySQL\APICLIENT\NodeRejoin;
use SkySQL\APICLIENT\NodeRestart;
use SkySQL\APICLIENT\NodeStart;
use SkySQL\APICLIENT\NodeStop;
use SkySQL\APICLIENT\Node;
use SkySQL\APICLIENT\System;

class MonitorTest {
	protected $apiKeyId = 5;
	protected $apiKey = "84e915085ab3d2673ac5d5f99946e359";
	protected $adminUser = "admin";
	protected $systemid = 1;
	protected $nodeid = 4;
	protected $commandParameters;
	protected $singleCommandParameters;
	protected $timeout = 40;
	protected $numberOfTests = 0;
	protected $failedTests = 0;
	
	/**
	 */
	public function __construct() {
		gc_enable ();
		// Prevent diagnostic output leaking out
		ob_start ();
		ob_implicit_flush ( false );
		// Setting of defined symbols
		define ( 'ABSOLUTE_PATH', str_replace ( '\\', '/', dirname ( __FILE__ ) ) );
		require_once (ABSOLUTE_PATH . '/configs/TestingMonitorDefinitions.php');
		if (! defined ( 'CLASS_BASE' ))
			define ( 'CLASS_BASE', ABSOLUTE_PATH );
		$testtimestamp = filemtime ( ABSOLUTE_PATH . '/APItests.php' );
		define ( '_TEST_CODE_ISSUE_DATE', date ( 'D, j F Y H:i', $testtimestamp ) );
		// Can't work without JSON
		if (! function_exists ( 'json_decode' )) {
			die ( "ERROR: PHP JSON functions are not available" );
			exit ( 1 );
		}
		// Set up a simple class autoloader
		spl_autoload_register ( array (
				__CLASS__,
				'simpleAutoload' 
		) );
		$this->init();
	}
	
	/**
	 * Sets the parameters for running the tests.
	 * Override this to load different parameters.
	 */
	protected function init() {
		$this->apiKeyId = 3; // hard code in the test suite
		$conf = parse_ini_string(file_get_contents("http://localhost/manager.ini", false), true);
		$this->apiKey = $conf['apikeys']["$this->apiKeyId"];
		$this->adminUser = "admin";
		$systems = new System(1, $this->apiKeyId, $this->apiKey);
		$sysArray = $systems->getAll();
		$this->systemid = $sysArray['systems'][0]['systemid'];
		$systems = new System($this->systemid, $this->apiKeyId, $this->apiKey);
		$nodesArray = $systems->getAllNodes();
		$this->nodeid = end($nodesArray);
		$this->commandParameters = "systemid=$this->systemid&username=$this->adminUser";
		$this->singleCommandParameters = $this->commandParameters . "&nodeid=$this->nodeid";
		$this->timeout = 40;
		$this->numberOfTests = 0;
		$this->failedTests = 0;
	}
	
	/**
	 * Class autoload.
	 * The $classname parameter must also include the Namespace.
	 *
	 * @param unknown $classname
	 *        	classname with full Namespace
	 */
	public static function simpleAutoload($classname) {
		$classname = str_replace ( '\\', '/', $classname );
		if (is_readable ( CLASS_BASE . '/customclasses/' . $classname . '.php' )) {
			return require_once (CLASS_BASE . '/customclasses/' . $classname . '.php');
		}
		if (is_readable ( CLASS_BASE . '/classes/' . $classname . '.php' )) {
			return require_once (CLASS_BASE . '/classes/' . $classname . '.php');
		}
		return false;
	}
	
	public function runTests () {
		// Ensure there are at least four nodes
		$systemInfo = new System ( $this->systemid, $this->apiKeyId, $this->apiKey );
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
			$nodeStart = new NodeStart ( $this->commandParameters . "&nodeid=$count", $this->apiKeyId, $this->apiKey );
			$nodeStart->go ();
			sleep ( $this->timeout * 2 );
			$nodeInfo = new Node ( $this->systemid, $count, $this->apiKeyId, $this->apiKey );
			$lastNodeState = $nodeInfo->go ();
			if ($lastNodeState ['node'] ['state'] == "joined") {
				echo "SUCCESS\n";
			} else {
				echo "FAIL, try with a longer timeout?\n";
				$this->failedTests ++;
			}
			$this->numberOfTests ++;
		}
		$lastSystemInfo = $systemInfo->go ();
		if ($lastSystemInfo ['system'] ['state'] == "running") {
			echo "System Running state: SUCCESS" . "\n";
		} else {
			echo "System Running state: FAIL, try with a longer timeout?" . "\n";
			$this->failedTests ++;
		}
		$this->numberOfTests ++;
		// Isolate
		echo "Node $this->nodeid Isolate: ";
		$nodeIsolate = new NodeIsolate ( $this->singleCommandParameters, $this->apiKeyId, $this->apiKey );
		$nodeIsolate->go ();
		sleep ( $this->timeout * 2 );
		$lastNodeState = $nodeInfo->go ();
		if ($lastNodeState ['node'] ['state'] == "isolated") {
			echo "SUCCESS" . "\n";
		} else {
			echo "FAIL, try with a longer timeout?" . "\n";
			$this->failedTests ++;
		}
		$this->numberOfTests ++;
		// Rejoin
		echo "Node $this->nodeid Rejoin: ";
		$nodeRejoin = new NodeRejoin ( $this->singleCommandParameters, $this->apiKeyId, $this->apiKey );
		$nodeRejoin->go ();
		sleep ( $this->timeout * 2 );
		$lastNodeState = $nodeInfo->go ();
		if ($lastNodeState ['node'] ['state'] == "joined") {
			echo "SUCCESS" . "\n";
		} else {
			echo "FAIL, try with a longer timeout?" . "\n";
			$this->failedTests ++;
		}
		$this->numberOfTests ++;
		// Restart
		echo "Node $this->nodeid Restart: ";
		$nodeRestart = new NodeRestart ( $this->singleCommandParameters, $this->apiKeyId, $this->apiKey );
		$nodeRestart->go ();
		sleep ( $this->timeout * 2 );
		$lastNodeState = $nodeInfo->go ();
		if ($lastNodeState ['node'] ['state'] == "joined") {
			echo "SUCCESS" . "\n";
		} else {
			echo "FAIL, try with a longer timeout?" . "\n";
			$this->failedTests ++;
		}
		$this->numberOfTests ++;
		// Stop all
		for($count = 4, $remainingNodes = 4; $count >= 1; $count --) {
			echo "Node $count Stop: ";
			$nodeStop = new NodeStop ( $this->commandParameters . "&nodeid=$count", $this->apiKeyId, $this->apiKey );
			$nodeStop->go ();
			sleep ( $this->timeout );
			$nodeInfo = new Node ( $this->systemid, $count, $this->apiKeyId, $this->apiKey );
			$lastNodeState = $nodeInfo->go ();
			if ($lastNodeState ['node'] ['state'] == "down") {
				echo "SUCCESS" . "\n";
				$remainingNodes --;
			} else {
				echo "FAIL, try with a longer timeout?" . "\n";
				$this->failedTests ++;
			}
			$this->numberOfTests ++;
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
				$this->failedTests ++;
			}
			$this->numberOfTests ++;
		}
		sleep ( $this->timeout );
		
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
				$nodeMonitorObj = new MonitorData ( $this->systemid, $count, $monitorKey, $this->apiKeyId, $this->apiKey );
				$this->failedTests += nodeMonitor ( $nodeMonitorObj, $value ['expected'] );
				$this->numberOfTests ++;
			}
		}
		foreach ( $monitorsMetadata as $monitorKey => $value ) {
			if (isset ( $value ['system'] )) {
				$systemMonitorObj = new MonitorData ( $this->systemid, $monitorKey, $this->apiKeyId, $this->apiKey );
				$this->failedTests += systemMonitor ( $systemMonitorObj, $value ['system'] );
				$this->numberOfTests ++;
			}
		}
		foreach ( $deltaMonitorsMetadata as $monitorKey => $value ) {
			for($count = 1; $count <= 4; $count ++) {
				$nodeMonitorObj = new MonitorData ( $this->systemid, $count, $monitorKey, $this->apiKeyId, $this->apiKey );
				$this->failedTests += nodeMonitor ( $nodeMonitorObj, $value ['expected'] );
				$this->numberOfTests ++;
			}
		}
		foreach ( $deltaMonitorsMetadata as $monitorKey => $value ) {
			if (isset ( $value ['system'] )) {
				$systemMonitorObj = new MonitorData ( $this->systemid, $monitorKey, $this->apiKeyId, $this->apiKey );
				$this->failedTests += systemMonitor ( $systemMonitorObj, $value ['system'] );
				$this->numberOfTests ++;
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
		function executeCommand($systemid, $nodeid, $apikeyid, $apikey, $command = null) {
			$node = new Node ( $systemid, $nodeid, $apikeyid, $apikey );
			$nodeInfo = $node->go ();
			$nodeIP = $nodeInfo ['node'] ['privateip'];
			ExecuteRemote::setup ( $nodeIP, 'root', 'skysql' );
			$output = ExecuteRemote::executeScriptSSH ( "echo 1" );
		}
		executeCommand ( $this->systemid, $this->nodeid, $this->apiKeyId, $this->apiKey );
		
		$successfulTests = $this->numberOfTests - $this->failedTests;
		echo "Tests done: $this->numberOfTests, Successful: $successfulTests, Failed: $this->failedTests \n";
	}
}

$monitorTest = new MonitorTest();
$monitorTest->runTests();