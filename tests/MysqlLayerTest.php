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
	
	
	/* Protected methods */
	
	public function testBuildWhereClause() {
		$method = new \ReflectionMethod($this->layer, 'buildWhereClause');
		$method->setAccessible(TRUE);
		
		$this->assertEquals('', $method->invoke($this->layer), 'Testing default');
		
		$conditions = array(
			'true = 1',
			'a = b',
			'0 is not null'
		);
		
		$this->assertEquals("where {$conditions[0]}", $method->invoke($this->layer, array($conditions[0])), 'Testing single element');
		$this->assertEquals(
			"where {$conditions[0]} and {$conditions[1]} and {$conditions[2]}",
			$method->invoke($this->layer, $conditions),
			'Testing multiple elements'
		);
	}
	
	public function testBuildOrderClause() {
		$method = new \ReflectionMethod($this->layer, 'buildOrderClause');
		$method->setAccessible(TRUE);
		
		$this->assertEquals('', $method->invoke($this->layer), 'Testing default');
		
		$orders = array(
			'test_field',
			'order_field asc',
			'desc_field desc'
		);
		
		$this->assertEquals("order by {$orders[0]}", $method->invoke($this->layer, array($orders[0])), 'Testing single element');
		$this->assertEquals(
			"order by {$orders[0]}, {$orders[1]}, {$orders[2]}",
			$method->invoke($this->layer, $orders),
			'Testing multiple elements'
		);
	}
}