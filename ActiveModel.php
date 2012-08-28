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
	protected $table;
	protected $primary_key;
	protected $attributes;
	
	public function __construct($table, $id=NULL) {
		$this->table = $table;
		$this->getTableStructure();
		
		if (!is_null($id)) {
			$this->__set($this->primary_key, $id);
			$this->fetchAttributes();
		}
	}
	
	public function __get($attribute) {
		return $this->attributes[$attribute];
	}
	
	public function __set($attribute, $value) {
		return $this->attributes[$attribute] = $value;
	}
	
	
	/**
	 * Inserts current object state into database
	 *
	 * @param void
	 * @return bool
	 */
	public function create() {
		$this->requireNotExists();
		
		$columns = $attributes = $values = array();
		foreach ($this->attributes as $attribute => $value) {
			if ($attribute == $this->primary_key) {
				continue;
			}
			
			$columns[] = $attribute;
			$attributes[] = ":$attribute";
			$values[":$attribute"] = is_null($value) ? 'NULL' : $value;
		}
		
		return $this->__set($this->primary_key, Query::insert(sprintf(
			'insert into %s (%s) values (%s)',
			$this->table,
			implode(', ', $columns),
			implode(', ', $attributes)
		), $values));
	}
	
	/**
	 * Updates database with current object state
	 *
	 * @param void
	 * @return int
	 */
	public function update() {
		$this->requireExists();
		
		$updates = $values = array();
		foreach ($this->attributes as $attribute => $value) {
			$values[":$attribute"] = is_null($value) ? 'NULL' : $value;
			if ($attribute == $this->primary_key) {
				continue;
			}
			
			$updates[] = sprintf('%s = %s', $attribute, ":$attribute");
		}
		
		return Query::update(sprintf(
			'update %s set %s where %s = %s limit 1',
			$this->table,
			implode(', ', $updates),
			$this->primary_key,
			":$this->primary_key"
		), $values);
	}
	
	/**
	 * Removes the object from the database
	 *
	 * @param void
	 * @return bool
	 */
	public function delete() {
		return Query::delete(sprintf(
			'delete from %s where %s = %s limit 1',
			$this->table,
			$this->primary_key,
			":$this->primary_key"
		), array(":$this->primary_key" => $this->primary_key()));
	}
	
	/**
	 * Stores the current object state into database
	 *
	 * Determines the appropriate method to call based on whether or not the
	 * record exists.
	 *
	 * @param void
	 * @return int
	 */
	public function save() {
		if ($this->exists()) {
			return $this->update();
		}
		
		return $this->create();
	}
	
	
	public function primary_key() {
		return $this->__get($this->primary_key);
	}
	
	public function exists() {
		return $this->primary_key();
	}
	
	
	/**
	 * Tests if object exists
	 *
	 * @param void
	 * @return void
	 */
	protected function requireExists() {
		if (!$this->exists()) {
			throw new \Exception('Cannot update non-existent record');
		}
	}
	
	/**
	 * Tests if object does not exist
	 *
	 * @param void
	 * @return void
	 */
	protected function requireNotExists() {
		if ($this->exists()) {
			throw new \Exception('Record already exists');
		}
	}
	
	protected function getTableStructure() {
		$this->attributes = array();
		
		foreach (Query::describeTable($this->table) as $attribute) {
			if ($attribute->Key == 'PRI') {
				$this->primary_key = $attribute->Field;
			}
			
			if (is_numeric($attribute->Default)) {
				if (intval($attribute->Default) == floatval($attribute->Default)) {
					$this->attributes[$attribute->Field] = (int) $attribute->Default;
				} else {
					$this->attributes[$attribute->Field] = (float) $attribute->Default;
				}
			} else {
				$this->attributes[$attribute->Field] = $attribute->Default;
			}
		}
	}
	
	protected function fetchAttributes() {
		if (!$row = Query::fetchByPrimaryKey($this->table, $this->primary_key(), $this->primary_key)) {
			throw new \Exception("Cannot instantiate non-existent record");
		}
		
		foreach ($this->attributes as $attribute => $value) {
			if (is_numeric($row->$attribute)) {
				if (intval($row->$attribute) == floatval($row->$attribute)) {
					$this->attributes[$attribute] = (int) $row->$attribute;
				} else {
					$this->attributes[$attribute] = (float) $row->$attribute;
				}
			} else {
				$this->attributes[$attribute] = $row->$attribute;
			}
		}
	}
}
