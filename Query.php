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
	
	/**
	 * Executes a Prepared Statement
	 *
	 * @param str $sql
	 * @param array $params
	 * @return PDOStatement
	 */
	public static function prepare($sql, array $params=array()) {
		// use late static binding to prevent multiple object collisions
		$stmt = static::$conn->prepare($sql);
		if (!$stmt->execute($params)) {
			// throw driver specific error
			$error = $stmt->errorInfo();
			throw new \Exception($error[2]);
		}
		
		return $stmt;
	}
	
	/**
	 * Executes Insert statement
	 *
	 * @param str $sql
	 * @param array $params
	 * @return int
	 */
	public static function insert($sql, array $params=array()) {
		self::prepare($sql, $params);
		return static::$conn->lastInsertId();
	}
	
	/**
	 * Executes Update statement
	 *
	 * @param str $sql
	 * @param array $params
	 * @return int
	 */
	public static function update($sql, array $params=array()) {
		return self::prepare($sql, $params)->rowCount();
	}
	
	/**
	 * Executes a Delete statement
	 *
	 * @param str $sql
	 * @param array $params
	 * @return int
	 */
	public static function delete($sql, array $params=array()) {
		return self::prepare($sql, $params)->rowCount();
	}
	
	/**
	 * Returns Prepared Statement Results
	 *
	 * Returns an array of PDO objects of the given fetch style.
	 *
	 * @param str $sql
	 * @param array $params
	 * @param str $style
	 * @return array
	 */
	public static function query($sql, array $params=array(), $style=\PDO::FETCH_OBJ) {
		// sacrificing performance for ease of escaping parameters
		return self::prepare($sql, $params)->fetchAll($style);
	}
	
	/**
	 * Retrieves the table description
	 *
	 * Each table column is returned as an array element of the given fetch style.
	 *
	 * @param str $table
	 * @param str $style
	 * @return array
	 */
	public static function describeTable($table, $style=\PDO::FETCH_OBJ) {
		return self::query(sprintf('describe %s', $table));
	}
	
	/**
	 * Retrives a single database record
	 *
	 * The record is returned in the given fetch style.
	 *
	 * @param str $table
	 * @param mixed $id
	 * @param str $primary_key
	 * @param str $style
	 * @return mixed
	 */
	public static function fetchByPrimaryKey($table, $id, $primary_key='id', $style=\PDO::FETCH_OBJ) {
		return self::prepare(
			sprintf(
				'select * from %s where %s = :id limit 1',
				$table,
				$primary_key
			),
			array(':id' => $id)
		)->fetch($style);
	}
}
