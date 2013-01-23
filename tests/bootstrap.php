<?php
/**
 * Test Suite Bootstrap
 *
 * @author Nicholas Whitt <nick.whitt@gmail.com>
 * @copyright Copyright (c) 2013, Nicholas Whitt
 * @link https://github.com/nickwhitt/CRUD Source
 * @license http://www.apache.org/licenses/ Apache License Version 2.0
 */

// objects
require_once dirname(__FILE__) . '/../Query.php';
require_once dirname(__FILE__) . '/../DatabaseLayer.php';
require_once dirname(__FILE__) . '/../MysqlLayer.php';
require_once dirname(__FILE__) . '/../ActiveBase.php';
require_once dirname(__FILE__) . '/../ActiveModel.php';
require_once dirname(__FILE__) . '/../ActiveSet.php';

// test doubles
require_once dirname(__FILE__) . '/MockPDO.php';