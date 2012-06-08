<?php
/**
 * 
 *
 * @author Nick Whitt
 */

require_once 'ActiveModel.php';
require_once 'Query.php';

// CRUD::init();
CRUD\Query::init('mysql:dbname=crud_test;host=127.0.0.1', 'root', 'root');

$model = new CRUD\ActiveModel('test');
var_dump($model);
