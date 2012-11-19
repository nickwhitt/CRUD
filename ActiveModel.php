<?php
/**
 * Active Record ORM
 *
 * @author Nicholas Whitt <nick.whitt@gmail.com>
 * @copyright Copyright (c) 2012, Nicholas Whitt
 * @link https://github.com/nickwhitt/CRUD Source
 * @license http://www.apache.org/licenses/ Apache License Version 2.0
 */

namespace CRUD;
class ActiveModel extends ActiveBase {
	public function __construct(DatabaseLayer $dba, $table, $id=NULL) {
		parent::__construct($dba, $table);
		
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
		$id = $this->dba->insert(
			$this->table,
			array_keys($this->attributes),
			array_values($this->attributes)
		);
		
		return $this->__set($this->primary_key, $id);
	}
	
	/**
	 * Updates database with current object state
	 *
	 * @param void
	 * @return int
	 */
	public function update() {
		$this->requireExists();
		
		$columns = $values = array();
		foreach ($this->attributes as $attribute => $value) {
			if ($attribute == $this->primary_key) {
				continue;
			}
			
			$columns[] = $attribute;
			$values[] = $value;
		}
		
		return $this->dba->update($this->table, $this->primary_key(), $columns, $values, $this->primary_key);
	}
	
	/**
	 * Removes the object from the database
	 *
	 * @param void
	 * @return bool
	 */
	public function delete() {
		return $this->dba->delete($this->table, $this->primary_key(), $this->primary_key);
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
		return $this->exists() ? $this->update() : $this->create();
	}
	
	
	/**
	 * Retrieves the primary key value
	 *
	 * @param void
	 * @return mixed
	 */
	public function primary_key() {
		return $this->__get($this->primary_key);
	}
	
	/**
	 * Tests if the object exists in the database
	 *
	 * Determined by the presence of a primary key.
	 *
	 * @param void
	 * @return bool
	 */
	public function exists() {
		return (bool) $this->primary_key();
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
	
	protected function fetchAttributes() {
		if (!$row = $this->dba->selectStar($this->table, $this->primary_key(), $this->primary_key)) {
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
