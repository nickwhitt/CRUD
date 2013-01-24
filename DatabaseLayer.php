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
	protected $traverse_stmt;
	
	public function __construct(\PDO $pdo) {
		$this->conn = $pdo;
	}
	
	public function __destruct() {
		$this->conn = NULL;
	}
	
	/**
	 * Executes Insert statement
	 *
	 * @param str $table
	 * @param array $columns
	 * @param array $values
	 * @return str
	 */
	public function insert($table, array $columns, array $values) {
		if (empty($columns) OR empty($values)) {
			throw new \Exception('Cannot insert when columns or values are empty');
		}
		
		$this->run($this->buildInsertQuery($table, $columns), $values);
		return $this->conn->lastInsertId();
	}
	
	/**
	 * Executes Update statement
	 *
	 * @param str $table
	 * @param mixed $id
	 * @param array $columns
	 * @param array $values
	 * @param str $primary_key
	 * @return bool
	 */
	public function update($table, $id, array $columns, array $values, $primary_key='id') {
		if (empty($columns) OR empty($values)) {
			throw new \Exception('Cannot update when columns or values are empty');
		}
		if (!$id OR !$primary_key) {
			throw new \Exception('Cannot update without a primary key');
		}
		
		$values[] = $id;
		return (bool) $this->run($this->buildUpdateQuery($table, $columns, $primary_key), $values)->rowCount();
	}
	
	/**
	 * Executes a Delete statement
	 *
	 * @param str $table
	 * @param mixed $id
	 * @param str $primary_key
	 * @return bool
	 */
	public function delete($table, $id, $primary_key='id') {
		if (!$id OR !$primary_key) {
			throw new \Exception('Cannot delete without a primary key');
		}
		
		return (bool) $this->run($this->buildDeleteQuery($table, $primary_key), array($id))->rowCount();
	}
	
	/**
	 * Retrieves the table description
	 *
	 * Each table column is returned as an array element of the given fetch style.
	 *
	 * @param str $table
	 * @param int $style
	 * @return array
	 */
	public function describeTable($table, $style=\PDO::FETCH_OBJ) {
		return $this->run($this->buildDescribeTable($table))->fetchAll($style);
	}
	
	/**
	 * Executes a prepared select statement
	 *
	 * @param str $column
	 * @param str $table
	 * @param array $conditions
	 * @param array $values
	 * @param array $orders
	 * @return PODStatement
	 */
	public function select($column, $table, array $conditions=array(), array $values=array(), array $orders=array()) {
		return $this->run($this->buildSelectQuery($column, $table, $conditions, $orders), $values);
	}
	
	/**
	 * Retrieves all fields from a single record
	 *
	 * @param str $table
	 * @param mixed $id
	 * @param str $primary_key
	 * @param int $style
	 * @return mixed
	 */
	public function selectStar($table, $id, $primary_key='id', $style=\PDO::FETCH_OBJ) {
		return $this->select(
			'*',
			$table,
			array($this->buildEqualCondition($primary_key)),
			array($id)
		)->fetch($style);
	}
	
	
	/**
	 * Executes a Prepared Statement
	 *
	 * @param str $sql
	 * @param array $params
	 * @return PDOStatement
	 */
	protected function run($sql, array $params=array()) {
		$stmt = $this->conn->prepare($sql);
		if (!$stmt->execute($params)) {
			// throw driver specific error
			$error = $stmt->errorInfo();
			throw new \Exception($error[2]);
		}
		
		return $stmt;
	}
	
	
	/* Traversal Methods */
	
	/**
	 * Prepares a PDOStatement for ActiveSet traversal
	 *
	 * @param str $table
	 * @param array $conditions
	 * @param array $values
	 * @param array $orders
	 * @param str $primary_key
	 * @return void
	 */
	public function traverseInit($table, array $conditions, array $values, array $orders, $primary_key='id') {
		$this->traverse_stmt = $this->select($primary_key, $table, $conditions, $values, $orders);
	}
	
	/**
	 * Initializes the traversal statement result set for iteration
	 *
	 * @param void
	 * @return void
	 */
	public function traverseReset() {
		$this->traverse_stmt->execute();
	}
	
	/**
	 * Retrieves the next row from the traversal statement
	 *
	 * @param void
	 * @return mixed primary key
	 */
	public function traverseNext() {
		return ($row = $this->traverse_stmt->fetch(\PDO::FETCH_NUM))
			? $row[0]
			: NULL;
	}
	
	/**
	 * Retrieves a traversal statement row by offset
	 *
	 * @param int $offset
	 * @param array $values
	 * @return mixed primary key
	 */
	public function traverseOffset($offset, array $values) {
		$stmt = $this->run(sprintf('%s %s', $this->traverse_stmt->queryString, $this->buildLimitClause(1, $offset)), $values);
		return ($row = $stmt->fetch(\PDO::FETCH_NUM))
			? $row[0]
			: NULL;
	}
	
	/**
	 * Retrieves the number of rows from the traversal statement
	 *
	 * @param void
	 * @return int
	 */
	public function traverseCount() {
		return $this->traverse_stmt->rowCount();
	}
}