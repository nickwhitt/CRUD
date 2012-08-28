<?php
/**
 * 
 *
 * @author Nick Whitt
 */

require_once 'ActiveModel.php';
require_once 'Query.php';

CRUD\Query::init('mysql:dbname=crud_test;host=127.0.0.1', 'root', 'root');

try {
	$invalid = new Crud\ActiveModel('fail');
} catch (Exception $e) {
	var_dump('Expected Exception: ' . $e->getMessage());
}

try {
	$invalid = new CRUD\ActiveModel('test', 0);
} catch (Exception $e) {
	var_dump('Expected Exception: ' . $e->getMessage());
}


$row = new CRUD\ActiveModel('test', 1);
var_dump($row);

$row->test_int = 123;
var_dump($row);
var_dump($row->save());
var_dump($row);


$model = new CRUD\ActiveModel('test');
var_dump($model);
var_dump($model->save());
var_dump($model);

var_dump($model->delete());


var_dump('Test Complete');
