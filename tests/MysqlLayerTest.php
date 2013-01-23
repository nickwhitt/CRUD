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
		$this->layer = new \CRUD\MysqlLayer($this->getMock('\CRUD\Tests\MockPDO'));
	}
	
	public function testConstructor() {
		$this->assertInstanceOf('CRUD\MysqlLayer', $this->layer);
	}
}