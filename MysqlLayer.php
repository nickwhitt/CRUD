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
	/* Query Methods */
	
	/**
	 * Builds a describe table query
	 *
	 * @param str $table
	 * @return str
	 */
	public function buildDescribeTable($table) {
		return sprintf('describe `%s`', $table);
	}
	
	/**
	 * Builds a select query
	 *
	 * @param str $column
	 * @param str $table
	 * @param array $conditions
	 * @param array $orders
	 * @return str
	 */
	public function buildSelectQuery($column, $table, array $conditions=array(), array $orders=array()) {
		return trim(sprintf(
			'select %s from `%s` %s %s',
			$column == '*' ? '*' : "`$column`",
			$table,
			$this->buildWhereClause($conditions),
			$this->buildOrderClause($orders)
		));
	}
	
	/**
	 * Builds an insert query
	 *
	 * @param str $table
	 * @param array $columns
	 * @return str
	 */
	public function buildInsertQuery($table, array $columns) {
		return sprintf(
			'insert into %s (%s) values (%s)',
			$table,
			implode(', ', $columns),
			implode(',', array_fill(0, count($columns), '?'))
		);
	}
	
	/**
	 * Builds an update query
	 *
	 * @param str $table
	 * @param array $columns
	 * @param str $id
	 * @return str
	 */
	public function buildUpdateQuery($table, array $columns, $primary_key='id') {
		return sprintf(
			'update %s set %s=? where %s = ? limit 1',
			$table,
			implode('=?, ', $columns),
			$primary_key
		);
	}
	
	/**
	 * Builds a delete query
	 *
	 * @param str $table
	 * @param str $primary_key
	 * @return str
	 */
	public function buildDeleteQuery($table, $primary_key='id') {
		return sprintf(
			'delete from %s where %s = ? limit 1',
			$table,
			$primary_key
		);
	}
	
	
	/* Generation Methods */
	
	/**
	 * Generates the where clause
	 *
	 * @param array $conditions
	 * @return str
	 */
	public function buildWhereClause(array $conditions=array()) {
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
	public function buildOrderClause(array $orders=array()) {
		return empty($orders) ? '' : sprintf(
			'order by %s',
			implode(', ', $orders)
		);
	}
	
	/**
	 * Generates a limit clause
	 *
	 * Allows for optional $offset of records.
	 *
	 * @param int $limit
	 * @param int $offset
	 * @return str
	 */
	public function buildLimitClause($limit=1, $offset=0) {
		return sprintf('limit %d,%d', $offset, $limit);
	}
	
	
	/* Conditional Predicate Methods */
	
	/**
	 * Creates a conditional predicate for use within a where clause
	 *
	 * @param str $column
	 * @param bool $negate
	 * @return str
	 */
	public function buildEqualCondition($column, $negate=FALSE) {
		return sprintf('%s %s= ?', $column, $negate == FALSE ? '' : '!');
	}
	
	/**
	 * Creates a numerical comparision predicate for use within a where clause
	 *
	 * @param str $column
	 * @param bool $exclusive
	 * @return str
	 */
	public function buildLessThanCondition($column, $exclusive=FALSE) {
		return sprintf('%s <%s ?', $column, $exclusive == FALSE ? '=' : '');
	}
	
	/**
	 * Creates a numerical comparision predicate for use within a where clause
	 *
	 * @param str $column
	 * @param bool $exclusive
	 * @return str
	 */
	public function buildGreaterThanCondition($column, $exclusive=FALSE) {
		return sprintf('%s >%s ?', $column, $exclusive == FALSE ? '=' : '');
	}
	
	/**
	 * Creates a comparison predicate for use within a where clause
	 *
	 * @param str $column
	 * @param bool $negate
	 * @return str
	 */
	public function buildLikeCondition($column, $negate=FALSE) {
		return sprintf('%s %slike ?', $column, $negate == FALSE ? '' : 'not ');
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
			'%s %sin (%s)',
			$column,
			$negate == FALSE ? '' : 'not ',
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
		return sprintf('%s is%s null', $column, $negate == FALSE ? '' : ' not');
	}
}