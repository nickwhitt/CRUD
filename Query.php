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
	public function insert($table, array $columns, array $values);
	public function update($table, $id, array $columns, array $values, $primary_key='id');
	public function delete($table, $id, $primary_key='id');
	public function describeTable($table, $style=\PDO::FETCH_OBJ);
	public function selectStar($table, $id, $primary_key='id', $style=\PDO::FETCH_OBJ);
	
	// Traversal Methods
	public function traverseInit($table, array $conditions, array $values, array $orders, $primary_key='id');
	public function traverseReset();
	public function traverseNext();
	public function traverseOffset($offset, array $values);
	public function traverseCount();
	
	// Conditional Predicate Methods
	public function buildEqualCondition($column, $negate=FALSE);
	public function buildLessThanCondition($column, $exclusive=FALSE);
	public function buildGreaterThanCondition($column, $exclusive=FALSE);
	public function buildLikeCondition($column, $negate=FALSE);
	public function buildInCondition($column, $count, $negate=FALSE);
	public function buildNullCondition($column, $negate=FALSE);
}