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
}