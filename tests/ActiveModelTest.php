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
class ActiveModelTest extends \PHPUnit_Framework_TestCase {
	public function setUp() {
		$this->layer = $this->getMockBuilder('\CRUD\MysqlLayer')
			->setConstructorArgs(array($this->getMock('\CRUD\Tests\MockPDO')))
			->getMock();
		
		$this->layer->expects($this->any())
			->method('describeTable')
			->will($this->returnValue(array()));
		
		$this->model = new \CRUD\ActiveModel($this->layer, 'test');
	}
	
	public function testConstructor() {
		$this->assertInstanceOf('CRUD\ActiveModel', $this->model);
	}
}