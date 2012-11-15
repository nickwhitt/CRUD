<?php
/**
 * Mysql Abstraction Layer
 *
 * @author Nicholas Whitt <nick.whitt@gmail.com>
 * @copyright Copyright (c) 2012, Nicholas Whitt
 * @link https://github.com/nickwhitt/CRUD Source
 * @license http://www.apache.org/licenses/ Apache License Version 2.0
 */

namespace CRUD;
class MysqlLayer extends DatabaseLayer {
	/**
	 * Executes Insert statement
	 *
	 * @param str $table
	 * @param array $columns
	 * @param array $values
	 * @return int
	 */
	public function insert($table, array $columns, array $values) {
		if (empty($columns) OR empty($values)) {
			throw new \Exception('Cannot insert when columns or values are empty');
		}
		
		$this->run(
			sprintf(
				'insert into %s (%s) values (%s)',
				$table,
				implode(', ', $columns),
				implode(',', array_fill(0, count($columns), '?'))
			),
			$values
		);
		
		return $this->conn->lastInsertId();
	}
	
	/**
	 * Executes Update statement
	 *
	 * @param str $table
	 * @param int $id
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
		return (bool) $this->run(
			sprintf(
				'update %s set %s=? where %s = ? limit 1',
				$table,
				implode('=?, ', $columns),
				$primary_key
			),
			$values
		)->rowCount();
	}
	
	/**
	 * Executes a Delete statement
	 *
	 * @param str $table
	 * @param int $id
	 * @param str $primary_key
	 * @return bool
	 */
	public function delete($table, $id, $primary_key='id') {
		if (!$id OR !$primary_key) {
			throw new \Exception('Cannot delete without a primary key');
		}
		
		return (bool) $this->run(
			sprintf(
				'delete from %s where %s = ? limit 1',
				$table,
				$primary_key
			),
			array($id)
		)->rowCount();
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
		return $this->run(sprintf('describe `%s`', $table))->fetchAll($style);
	}
	
	/**
	 * Retrieves a list of records by condition
	 *
	 * @param str $table
	 * @param array $conditions
	 * @param array $values
	 * @param array $orders
	 * @param str $primary_key
	 * @param int $style
	 * @return array
	 */
	public function selectKeys($table, array $conditions, array $values, array $orders, $primary_key='id', $style=\PDO::FETCH_OBJ) {
		return $this->select($primary_key, $table, $conditions, $values, $orders)->fetchAll($style);
	}
	
	/**
	 * Retrives a single record by condition
	 *
	 * @param str $table
	 * @param array $conditions
	 * @param array $values
	 * @param array $orders
	 * @param str $primary_key
	 * @param int $style
	 * @return mixed
	 */
	public function selectKey($table, array $conditions, array $values, array $orders, $primary_key='id', $style=\PDO::FETCH_OBJ) {
		return $this->select($primary_key, $table, $conditions, $values, $orders)->fetch($style);
	}
	
	/**
	 * Retrieves all fields from a single record
	 *
	 * @param str $table
	 * @param int $id
	 * @param str $primary_key
	 * @param int $style
	 * @return mixed
	 */
	public function selectAll($table, $id, $primary_key='id', $style=\PDO::FETCH_OBJ) {
		return $this->run(
			sprintf(
				'select * from `%s` %s',
				$table,
				$this->buildWhereClause(array(
					$this->buildEqualCondition($primary_key)
				))
			),
			array($id)
		)->fetch($style);
	}
	
	
	/**
	 * Creates a conditional predicate for use within a where clause
	 *
	 * @param str $column
	 * @param bool $negate
	 * @return str
	 */
	public function buildEqualCondition($column, $negate=FALSE) {
		return sprintf('%s %s= ?', $column, $negate === FALSE ? '' : '!');
	}
	
	/**
	 * Creates a comparison predicate for use within a where clause
	 *
	 * @param str $column
	 * @param bool $negate
	 * @return str
	 */
	public function buildLikeCondition($column, $negate=FALSE) {
		return sprintf('%s %s like ?', $column, $negate === FALSE ? '' : 'not');
	}
	
	/**
	 * Creates a conditional list predicate for use within a where clause
	 *
	 * @param str $column
	 * @param int $count
	 * @param bool $negate
	 * @return str
	 */
	public function buildInCondition($column, $count, $negate=FALSE) {
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
	public function buildNullCondition($column, $negate=FALSE) {
		return sprintf('%s is %s null', $column, $negate === FALSE ? '' : 'not');
	}
	
	
	/**
	 * Generates the where clause
	 *
	 * @param array $conditions
	 * @return str
	 */
	protected function buildWhereClause(array $conditions) {
		return empty($conditions) ? '' : sprintf(
			'where %s',
			implode(' and ', $conditions)
		);
	}
	
	/**
	 * Generates the order clause
	 *
	 * @param array $orders
	 * @return str
	 */
	protected function buildOrderClause(array $orders) {
		return empty($orders) ? '' : sprintf(
			'order by %s',
			implode(', ', $orders)
		);
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
	protected function select($column, $table, array $conditions, array $values, array $orders) {
		return $this->run(sprintf(
				'select `%s` from `%s` %s %s',
				$column,
				$table,
				$this->buildWhereClause($conditions),
				$this->buildOrderClause($orders)
			),
			$values
		);
	}
}