<?php
/**
 * Query DBA
 *
 * @author Nicholas Whitt <nick.whitt@gmail.com>
 * @copyright Copyright (c) 2012, Nicholas Whitt
 * @link https://github.com/nickwhitt/CRUD Source
 * @license http://www.apache.org/licenses/ Apache License Version 2.0
 */

namespace CRUD;
class Query {
	protected $conn;
	
	public function __construct($dsn, $username, $password) {
		$this->conn = new \PDO($dsn, $username, $password);
	}
	
	
	/**
	 * Executes a Prepared Statement
	 *
	 * @param str $sql
	 * @param array $params
	 * @return PDOStatement
	 */
	public function prepare($sql, array $params=array()) {
		$stmt = $this->conn->prepare($sql);
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
	public function insert($sql, array $params=array()) {
		$this->prepare($sql, $params);
		return $this->conn->lastInsertId();
	}
	
	/**
	 * Executes Update statement
	 *
	 * @param str $sql
	 * @param array $params
	 * @return int
	 */
	public function update($sql, array $params=array()) {
		return $this->prepare($sql, $params)->rowCount();
	}
	
	/**
	 * Executes a Delete statement
	 *
	 * @param str $sql
	 * @param array $params
	 * @return int
	 */
	public function delete($sql, array $params=array()) {
		return $this->prepare($sql, $params)->rowCount();
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
	public function query($sql, array $params=array(), $style=\PDO::FETCH_OBJ) {
		// sacrificing performance for ease of escaping parameters
		return $this->prepare($sql, $params)->fetchAll($style);
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
	public function describeTable($table, $style=\PDO::FETCH_OBJ) {
		return $this->query(sprintf('describe %s', $table));
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
	public function fetchByPrimaryKey($table, $id, $primary_key='id', $style=\PDO::FETCH_OBJ) {
		return $this->prepare(
			sprintf(
				'select * from %s where %s = :id limit 1',
				$table,
				$primary_key
			),
			array(':id' => $id)
		)->fetch($style);
	}
	
	
	/**
	 * Creates a conditional predicate for use within a where clause
	 *
	 * @param str $column
	 * @param bool $negate
	 * @return str
	 */
	public static function buildEqualCondition($column, $negate=FALSE) {
		return sprintf('%s %s= ?', $column, $negate === FALSE ? '' : '!');
	}
	
	/**
	 * Creates a comparison predicate for use within a where clause
	 *
	 * @param str $column
	 * @param bool $negate
	 * @return str
	 */
	public static function buildLikeCondition($column, $negate=FALSE) {
		return sprintf('%s %s like ', $column, $negate === FALSE ? '' : 'not');
	}
	
	/**
	 * Creates a conditional list predicate for use within a where clause
	 *
	 * @param str $column
	 * @param int $count
	 * @param bool $negate
	 * @return str
	 */
	public static function buildInCondition($column, $count, $negate=FALSE) {
		return sprintf(
			'%s %s in (%s)',
			$column,
			$negate === FALSE ? '' : 'not',
			implode(',', array_fill(0, $count, '?'))
		);
	}
	
	/**
	 * Creates a null predicate for use within a where clause
	 *
	 * @param str $column
	 * @param bool $negate
	 * @return str
	 */
	public static function buildNullCondition($column, $negate=FALSE) {
		return sprintf('%s is %s null', $column, $negate === FALSE ? '' : 'not');
	}
}
