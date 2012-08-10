<?php
/**
 * 
 *
 * @author Nick Whitt
 */

require_once 'ActiveModel.php';
require_once 'Query.php';

CRUD\Query::init('mysql:dbname=crud_test;host=127.0.0.1', 'root', 'root');

$model = new CRUD\ActiveModel('test');
var_dump($model);

$row = new CRUD\ActiveModel('test', 1);
var_dump($row);

try {
	$row = new CRUD\ActiveModel('test', 2);
} catch (Exception $e) {
	echo "<table>$e->xdebug_message</table>";
}
