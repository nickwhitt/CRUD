<?php
/**
 * Active Record ORM
 *
 * @author Nicholas Whitt <nick.whitt@gmail.com>
 * @copyright Copyright (c) 2012, Nicholas Whitt
 * @link https://github.com/nickwhitt/CRUD Source
 * @license http://www.gnu.org/licenses/lgpl.html Lesser General Public License, version 3
 */

namespace CRUD;
class ActiveModel {
	public function __construct($table, $id=NULL) {
		// fetch table structure
		var_dump(Query::describeTable($table, $id));
	}
}
