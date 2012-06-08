<?php
/**
 * Query DBA
 *
 * @author Nick Whitt
 */

namespace CRUD;
class Query {
	protected static $conn;
	
	public static function init($dsn, $user, $password) {
		self::$conn = new \PDO($dsn, $user, $password);
	}
	
	public static function describeTable($table) {
		$query = sprintf('DESCRIBE %s', self::escapeTable($table));
		$stmt = self::$conn->query($query);
		$stmt->execute(array('table' => $table));
		return $stmt->fetchAll(\PDO::FETCH_OBJ);
	}
	
	public static function escapeTable($table) {
		return $table;
	}
}
