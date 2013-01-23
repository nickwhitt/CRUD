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
}