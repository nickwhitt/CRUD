<?php
/**
 * Local Database Wrapper
 *
 * Extends base mysqli object with debug and counter methods.
 *
 * @author Nicholas Whitt <nick.whitt@gmail.com>
 * @copyright Copyright (c) 2012, Nicholas Whitt
 * @link https://github.com/nickwhitt/CRUD Source
 * @license http://www.gnu.org/licenses/lgpl.html Lesser General Public License, version 3
 */

class Database extends mysqli {
	protected $queries=0;
	
	/**
	 * Extends parent::query() method
	 *
	 * Increments query count with each call. Allows developer to debug query
	 * when not in production environment.
	 *
	 * @param str $sql
	 * @param bool $debug
	 * @return mixed
	 */
	public function query($sql, $debug=FALSE) {
		$this->queries++;
		if ($debug !== FALSE AND ENVIRONMENT != 'production') {
			var_dump($sql);
		}
		
		try {
			if (!$result = parent::query($sql)) {
				throw new Exception("MySQLi error: $this->error");
			}
		} catch (Exception $e) {
			throw new Exception($e->getMessage());
		}
		
		return $result;
	}
	
	/**
	 * Returns the total number of queries executed
	 *
	 * @param void
	 * @return int
	 */
	public function get_query_count() {
		return $this->queries;
	}
}
