<?php
/**
 * MysqlLayer Unit Test
 *
 * @author Nicholas Whitt <nick.whitt@gmail.com>
 * @copyright Copyright (c) 2013, Nicholas Whitt
 * @link https://github.com/nickwhitt/CRUD Source
 * @license http://www.apache.org/licenses/ Apache License Version 2.0
 */

namespace CRUD\Tests;
class MysqlLayerTest extends \PHPUnit_Framework_TestCase {
	public function setUp() {
		$this->stmt = $this->getMock('\PDOStatement');
		$this->stmt->expects($this->any())
			->method('execute')
			->will($this->returnValue(TRUE));
		
		$this->pdo = $this->getMock('\CRUD\Tests\MockPDO');
		$this->pdo->expects($this->any())
			->method('prepare')
			->will($this->returnValue($this->stmt));
		
		$this->layer = new \CRUD\MysqlLayer($this->pdo);
	}
	
	public function testConstructor() {
		$this->assertInstanceOf('CRUD\MysqlLayer', $this->layer);
	}
	
	public function testDescribeTable() {
		$expected = array();
		
		$this->stmt->expects($this->any())
			->method('fetchAll')
			->will($this->returnValue($expected));
		
		$this->assertEquals($expected, $this->layer->describeTable('test'));
	}
	
	/* Conditional Predicate Methods */
	
	public function testBuildEqualCondition() {
		$column = 'test_field';
		
		$this->assertEquals("$column = ?", $this->layer->buildEqualCondition($column), 'Testing default');
		$this->assertEquals("$column = ?", $this->layer->buildEqualCondition($column, FALSE), 'Testing boolean false');
		$this->assertEquals("$column = ?", $this->layer->buildEqualCondition($column, 0), 'Testing logical false');
		
		$this->assertEquals("$column != ?", $this->layer->buildEqualCondition($column, TRUE), 'Testing boolean true');
		$this->assertEquals("$column != ?", $this->layer->buildEqualCondition($column, 1), 'Testing logical true');
		$this->assertEquals("$column != ?", $this->layer->buildEqualCondition($column, 'invalid'), 'Testing invalid parameter');
	}
	
	public function testBuildLessThanCondition() {
		$column = 'test_field';
		
		$this->assertEquals("$column <= ?", $this->layer->buildLessThanCondition($column), 'Testing default');
		$this->assertEquals("$column <= ?", $this->layer->buildLessThanCondition($column, FALSE), 'Testing boolean false');
		$this->assertEquals("$column <= ?", $this->layer->buildLessThanCondition($column, 0), 'Testing logical false');
		
		$this->assertEquals("$column < ?", $this->layer->buildLessThanCondition($column, TRUE), 'Testing boolean true');
		$this->assertEquals("$column < ?", $this->layer->buildLessThanCondition($column, 1), 'Testing logical true');
		$this->assertEquals("$column < ?", $this->layer->buildLessThanCondition($column, 'invalid'), 'Testing invalid parameter');
	}
	
	public function testBuildGreaterThanCondition() {
		$column = 'test_field';
		
		$this->assertEquals("$column >= ?", $this->layer->buildGreaterThanCondition($column), 'Testing default');
		$this->assertEquals("$column >= ?", $this->layer->buildGreaterThanCondition($column, FALSE), 'Testing boolean false');
		$this->assertEquals("$column >= ?", $this->layer->buildGreaterThanCondition($column, 0), 'Testing logical false');
		
		$this->assertEquals("$column > ?", $this->layer->buildGreaterThanCondition($column, TRUE), 'Testing boolean true');
		$this->assertEquals("$column > ?", $this->layer->buildGreaterThanCondition($column, 1), 'Testing logical true');
		$this->assertEquals("$column > ?", $this->layer->buildGreaterThanCondition($column, 'invalid'), 'Testing invalid parameter');
	}
	
	public function testBuildLikeCondition() {
		$column = 'test_field';
		
		$this->assertEquals("$column like ?", $this->layer->buildLikeCondition($column), 'Testing default');
		$this->assertEquals("$column like ?", $this->layer->buildLikeCondition($column, FALSE), 'Testing boolean false');
		$this->assertEquals("$column like ?", $this->layer->buildLikeCondition($column, 0), 'Testing logical false');
		
		$this->assertEquals("$column not like ?", $this->layer->buildLikeCondition($column, TRUE), 'Testing boolean true');
		$this->assertEquals("$column not like ?", $this->layer->buildLikeCondition($column, 1), 'Testing logical true');
		$this->assertEquals("$column not like ?", $this->layer->buildLikeCondition($column, 'invalid'), 'Testing invalid parameter');
	}
	
	public function testBuildInCondition() {
		$column = 'test_field';
		$count = 4;
		
		$this->assertEquals("$column in (?,?,?,?)", $this->layer->buildInCondition($column, $count), 'Testing default');
		$this->assertEquals("$column in (?,?,?,?)", $this->layer->buildInCondition($column, $count, FALSE), 'Testing boolean fasle');
		$this->assertEquals("$column in (?,?,?,?)", $this->layer->buildInCondition($column, $count, 0), 'Testing logical false');
		
		$this->assertEquals("$column not in (?,?,?,?)", $this->layer->buildInCondition($column, $count, TRUE), 'Testing boolean true');
		$this->assertEquals("$column not in (?,?,?,?)", $this->layer->buildInCondition($column, $count, 1), 'Testing logical true');
		$this->assertEquals("$column not in (?,?,?,?)", $this->layer->buildInCondition($column, $count, 'invalid'), 'Testing invalid parameter');
	}
	
	public function testBuildNullCondition() {
		$column = 'test_field';
		
		$this->assertEquals("$column is null", $this->layer->buildNullCondition($column), 'Testing default');
		$this->assertEquals("$column is null", $this->layer->buildNullCondition($column, FALSE), 'Testing boolean false');
		$this->assertEquals("$column is null", $this->layer->buildNullCondition($column, 0), 'Testing logical false');
		
		$this->assertEquals("$column is not null", $this->layer->buildNullCondition($column, TRUE), 'Testing boolean true');
		$this->assertEquals("$column is not null", $this->layer->buildNullCondition($column, 1), 'Testing logical true');
		$this->assertEquals("$column is not null", $this->layer->buildNullCondition($column, 'invalid'), 'Testing invalid parameter');
	}
	
	/* Generation Methods */
	
	public function testBuildWhereClause() {
		$conditions = $this->generateConditions();
		
		$this->assertEquals('', $this->layer->buildWhereClause(), 'Testing default');
		$this->assertEquals("where {$conditions[0]}", $this->layer->buildWhereClause(array($conditions[0])), 'Testing single element');
		$this->assertEquals(
			"where {$conditions[0]} and {$conditions[1]} and {$conditions[2]}",
			$this->layer->buildWhereClause($conditions),
			'Testing multiple elements'
		);
	}
	
	public function testBuildOrderClause() {
		$orders = $this->generateOrders();
		
		$this->assertEquals('', $this->layer->buildOrderClause(), 'Testing default');
		$this->assertEquals("order by {$orders[0]}", $this->layer->buildOrderClause(array($orders[0])), 'Testing single element');
		$this->assertEquals(
			"order by {$orders[0]}, {$orders[1]}, {$orders[2]}",
			$this->layer->buildOrderClause($orders),
			'Testing multiple elements'
		);
	}
	
	public function testBuildLimitClause() {
		$limit = 20;
		$offset = 42;
		
		$this->assertEquals('limit 0,1', $this->layer->buildLimitClause(), 'Testing default');
		$this->assertEquals('limit 0,1', $this->layer->buildLimitClause(1), 'Testing explicit default limit');
		$this->assertEquals('limit 0,1', $this->layer->buildLimitClause(1, 0), 'Testing explicit defaults');
		
		$this->assertEquals("limit 0,$limit", $this->layer->buildLimitClause($limit), 'Testing limit');
		$this->assertEquals("limit $offset,$limit", $this->layer->buildLimitClause($limit, $offset), 'Testing limit with offset');
		$this->assertEquals("limit 0,0", $this->layer->buildLimitClause('invalid', 'unknown'), 'Testing invalid parameters');
	}
	
	/* Query Methods */
	
	public function testBuildSelectQuery() {
		$table = 'test';
		$column = 'test_field';
		$conditions = $this->generateConditions();
		$orders = $this->generateOrders();
		
		$select = "select `$column` from `$table`";
		$select_where = "$select where {$conditions[0]} and {$conditions[1]} and {$conditions[2]}";
		$order = "order by {$orders[0]}, {$orders[1]}, {$orders[2]}";
		
		$this->assertEquals($select, $this->layer->buildSelectQuery($column, $table), 'Testing default');
		$this->assertEquals($select_where, $this->layer->buildSelectQuery($column, $table, $conditions), 'Testing with conditions');
		$this->assertEquals("$select  $order", $this->layer->buildSelectQuery($column, $table, array(), $orders), 'Testing with order');
		$this->assertEquals("$select_where $order", $this->layer->buildSelectQuery($column, $table, $conditions, $orders), 'Testing with conditions and order');
	}
	
	public function testBuildInsertQuery() {
		$table = 'test';
		$columns = $this->generateColumns();
		
		$expected = "insert into $table ({$columns[0]}) values (?)";
		$this->assertEquals($expected, $this->layer->buildInsertQuery($table, array($columns[0])), 'Testing single column');
		
		$expected = "insert into $table ({$columns[0]}, {$columns[1]}, {$columns[2]}) values (?,?,?)";
		$this->assertEquals($expected, $this->layer->buildInsertQuery($table, $columns), 'Testing multiple columns');
	}
	
	public function testBuildUpdateQuery() {
		$table = 'test';
		$id = 'test_key';
		$columns = $this->generateColumns();
		
		$expected = "update $table set {$columns[0]}=?, {$columns[1]}=?, {$columns[2]}=? where id = ? limit 1";
		$this->assertEquals($expected, $this->layer->buildUpdateQuery($table, $columns), 'Testing default');
		$this->assertEquals($expected, $this->layer->buildUpdateQuery($table, $columns, 'id'), 'Testing explicit key');
		
		$expected = "update $table set {$columns[0]}=? where id = ? limit 1";
		$this->assertEquals($expected, $this->layer->buildUpdateQuery($table, array($columns[0])), 'Testing single column');
		
		$expected = "update $table set {$columns[0]}=?, {$columns[1]}=?, {$columns[2]}=? where $id = ? limit 1";
		$this->assertEquals($expected, $this->layer->buildUpdateQuery($table, $columns, $id), 'Testing non-standard key');
	}
	
	public function testBuildDeleteQuery() {
		$table = 'test';
		$id = 'test_key';
		
		$this->assertEquals("delete from $table where id = ? limit 1", $this->layer->buildDeleteQuery($table), 'Testing default');
		$this->assertEquals("delete from $table where id = ? limit 1", $this->layer->buildDeleteQuery($table, 'id'), 'Testing explicit key');
		$this->assertEquals("delete from $table where $id = ? limit 1", $this->layer->buildDeleteQuery($table, $id), 'Testing non-standard key');
	}
	
	/* Test Helper Methods */
	
	protected function generateConditions() {
		return array('true = 1', 'a = b', '0 is not null');
	}
	
	protected function generateOrders() {
		return array('test_field', 'order_field asc', 'desc_field desc');
	}
	
	protected function generateColumns() {
		return array('test_one', 'test_two', 'test_three');
	}
}