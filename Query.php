<?php
/**
 * Query Implementation
 *
 * @author Nicholas Whitt <nick.whitt@gmail.com>
 * @copyright Copyright (c) 2012, Nicholas Whitt
 * @link https://github.com/nickwhitt/CRUD Source
 * @license http://www.apache.org/licenses/ Apache License Version 2.0
 */

namespace CRUD;
interface Query {
	// Query Methods
	public function buildDescribeTable($table);
	public function buildSelectQuery($column, $table, array $conditions=array(), array $orders=array());
	public function buildInsertQuery($table, array $columns);
	public function buildUpdateQuery($table, array $columns, $primary_key='id');
	public function buildDeleteQuery($table, $primary_key='id');
	
	// Generation Methods
	public function buildWhereClause(array $conditions=array());
	public function buildOrderClause(array $orders=array());
	public function buildLimitClause($limit=1, $offset=0);
	
	// Conditional Predicate Methods
	public function buildEqualCondition($column, $negate=FALSE);
	public function buildLessThanCondition($column, $exclusive=FALSE);
	public function buildGreaterThanCondition($column, $exclusive=FALSE);
	public function buildLikeCondition($column, $negate=FALSE);
	public function buildInCondition($column, $count, $negate=FALSE);
	public function buildNullCondition($column, $negate=FALSE);
}