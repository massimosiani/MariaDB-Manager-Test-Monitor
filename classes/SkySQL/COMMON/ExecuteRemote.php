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
 * Date: April 2014
 */

namespace SkySQL\COMMON;

class ExecuteRemote {
	private static $host;
	private static $username;
	private static $password;
	private static $error;
	private static $output;

	public static function setup($host, $username=null, $password=null)	{
		self::$host = $host;
		self::$username = $username;
		self::$password = $password;
	}

	public static function executeScriptSSH($script) {
		// Setup connection string
		$connectionString = self::$host;
		$connectionString = (empty(self::$username) ? $connectionString : self::$username.'@'.$connectionString);
		// Execute script
		$cmd = "ssh $connectionString \"$script 2>&1\"";
		self::$output['command'] = $cmd;
		exec($cmd, self::$output, self::$error);
		if (self::$error) {
			throw new Exception ("\nError sshing: ".print_r(self::$output, true));
		}
		return self::$output;
	}
}