<?php
/**
 * Query DBA
 *
 * @author Nicholas Whitt <nick.whitt@gmail.com>
 * @copyright Copyright (c) 2012, Nicholas Whitt
 * @link https://github.com/nickwhitt/CRUD Source
 * @license http://www.gnu.org/licenses/lgpl.html Lesser General Public License, version 3
 */

namespace CRUD;
class Query {
	protected static $conn;
	
	public static function init($dsn, $user, $password) {
		self::$conn = new \PDO($dsn, $user, $password);
	}
	
	public static function describeTable($table, $style=\PDO::FETCH_OBJ) {
		$stmt = self::$conn->query(sprintf(
			'DESCRIBE %s',
			self::escapeTable($table)
		));
		$stmt->execute();
		
		return $stmt->fetchAll($style);
	}
	
	public static function fetchByPrimaryKey($table, $id, $primary_key='id', $style=\PDO::FETCH_OBJ) {
		$stmt = self::$conn->prepare(sprintf(
			'SELECT * FROM `%s` WHERE `%s` = :id LIMIT 1',
			self::escapeTable($table),
			self::escapeField($primary_key)
		));
		$stmt->execute(array(':id' => $id));
		
		return $stmt->fetch($style);
	}
	
	public static function escapeTable($table) {
		return $table;
	}
	
	public static function escapeField($field) {
		return $field;
	}
}
