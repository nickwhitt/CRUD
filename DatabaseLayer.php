<?php
/**
 * Database Abstraction Layer
 *
 * @author Nicholas Whitt <nick.whitt@gmail.com>
 * @copyright Copyright (c) 2012, Nicholas Whitt
 * @link https://github.com/nickwhitt/CRUD Source
 * @license http://www.apache.org/licenses/ Apache License Version 2.0
 */

namespace CRUD;
abstract class DatabaseLayer implements Query {
	protected $conn;
	
	public function __construct(\PDO $pdo) {
		$this->conn = $pdo;
	}
	
	public function __destruct() {
		$this->conn = NULL;
	}
}