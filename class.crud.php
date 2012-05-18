<?php
/**
 * Database Object Map
 *
 * Provides a basic database object layer mapping a table row.
 *
 * Depends on an existing mysqli object named $mysqli.
 *
 * @author Nicholas Whitt <nick.whitt@gmail.com>
 * @copyright Copyright (c) 2012, Nicholas Whitt
 * @link https://github.com/nickwhitt/CRUD Source
 * @license http://www.gnu.org/licenses/lgpl.html Lesser General Public License, version 3
 */

class CRUD {
	protected $id;
	protected $table;
	protected $primary_key;
	protected $attributes;
	
	/**
		Magic Methods
	 */
	
	public function __construct($table, $id=NULL) {
		$this->table = $table;
		$this->fetch_table_structure();
		
		if (!is_null($id)) {
			$this->id = $id;
			$this->fetch_attributes();
		}
	}
	
	/**
	 * Generic getter method
	 *
	 * @param str $property
	 * @return mixed
	 */
	public function __get($property) {
		if ($this->attribute_exists($property)) {
			return $this->attributes[$property];
		}
		
		$this->validate_property($property);
		return $this->$property;
	}
	
	/**
	 * Helper function for proper display formatting
	 *
	 * @param str $attribute
	 * @param str $default
	 * @return mixed
	 */
	public function display($attribute, $default='N/A') {
		return $this->$attribute ? $this->$attribute : $default;
	}
	
	/**
	 * Helper function for proper date display formatting
	 *
	 * By default, assumes the attribute is stored as a date string.
	 *
	 * @param str $attribute
	 * @param bool $timestamp Indicates if the attribute is a timestamp
	 * @param str $format Desired display format
	 * @param str $default
	 * @return str
	 */
	public function display_date($attribute, $timestamp=FALSE, $format='m/d/Y h:i a', $default='N/A') {
		$time = $timestamp ? $this->$attribute : strtotime($this->$attribute);
		return $this->$attribute ? date($format, $time) : $default;
	}
	
	/**
	 * Generic setter method
	 *
	 * @param str $property
	 * @param mixed $value
	 * @return bool
	 */
	public function __set($property, $value) {
		if ($this->attribute_exists($property)) {
			return $this->attributes[$property] = $value;
		}
		
		$this->validate_property($property);
		return $this->$property = $value;
	}
	
	
	/**
		CRUD Methods
	 */
	
	/**
	 * Helper function to update/create record
	 *
	 * @param void
	 * @return int escalation ID
	 */
	public function save() {
		if (is_null($this->id)) {
			return $this->create();
		}
		
		return $this->update();
	}
	
	/**
	 * Create a new database record
	 *
	 * @param void
	 * @return int
	 */
	public function create() {
		global $mysqli;
		
		if (!is_null($this->id)) {
			throw new Exception('Cannot re-create existing record');
		}
		
		$values = array();
		foreach ($this->attributes as $value) {
			$values[] = is_null($value) ? 'NULL' : sprintf('"%s"', $mysqli->real_escape_string($value));
		}
		
		if (!$mysqli->query(sprintf(
			'INSERT INTO `%s` (%s) VALUES (%s)',
			$mysqli->real_escape_string($this->table),
			implode(',', array_keys($this->attributes)),
			implode(',', $values)
		))) {
			return FALSE;
		}
		
		$this->id = $mysqli->insert_id;
		return $this->id;
	}
	
	/**
	 * Update existing database record
	 *
	 * @param void
	 * @return bool
	 */
	public function update() {
		global $mysqli;
		
		if (is_null($this->id)) {
			throw new Exception('Cannot update non-existing record');
		}
		
		$values = array();
		foreach ($this->attributes as $attribute => $value) {
			$values[] = sprintf(
				'`%s` = %s',
				$mysqli->real_escape_string($attribute),
				is_null($value) ? 'NULL' : sprintf('"%s"', $mysqli->real_escape_string($value))
			);
		}
		
		return $mysqli->query(sprintf(
			'UPDATE `%s` SET %s WHERE `%s` = "%d" LIMIT 1',
			$mysqli->real_escape_string($this->table),
			implode(', ', $values),
			$mysqli->real_escape_string($this->primary_key),
			$mysqli->real_escape_string($this->id)
		));
	}
	
	/**
	 * Delete existing database record
	 *
	 * @param void
	 * @return bool
	 */
	public function delete() {
		global $mysqli;
		
		if (is_null($this->id)) {
			throw new Exception('Cannot delete non-existing record');
		}
		
		return $mysqli->query(sprintf(
			'DELETE FROM `%s` WHERE `%s` = "%d" LIMIT 1',
			$mysqli->real_escape_string($this->table),
			$mysqli->real_escape_string($this->primary_key),
			$mysqli->real_escape_string($this->id)
		));
	}
	
	
	/**
		Protected Methods
	 */
	
	/**
	 * Internal sanity checking
	 *
	 * @deprecated in favor of validate_attribute()
	 * @param str $property
	 * @return void
	 */
	protected function validate_property($property) {
		if (!property_exists($this, $property)) {
			$class_name = get_class();
			throw new Exception("Invalid property $class_name::$property");
		}
	}
	
	/**
	 * Internal sanity checking
	 *
	 * @param str $attribute
	 * @return void
	 */
	protected function validate_attribute($attribute) {
		if (!$this->attribute_exists($attribute)) {
			$class_name = get_class();
			throw new Exception("Invalid attribute $class_name::$attribute");
		}
	}
	
	/**
	 * Attribute validation test
	 *
	 * @param str $attribute
	 * @return bool
	 */
	protected function attribute_exists($attribute) {
		return array_key_exists($attribute, $this->attributes);
	}
	
	/**
	 * Reads the table structure from the database
	 *
	 * Populates object's $attributes array with database defaults.
	 *
	 * @param void
	 * @return void
	 */
	protected function fetch_table_structure() {
		global $mysqli;
		
		$this->attributes = array();
		
		if (!$result = $mysqli->query(sprintf('DESCRIBE `%s`', $mysqli->real_escape_string($this->table)))) {
			throw new Exception("MySQLi error: $mysqli->error");
		}
		
		while ($attribute = $result->fetch_object()) {
			if ($attribute->Key == 'PRI') {
				$this->primary_key = $attribute->Field;
			} elseif (is_numeric($attribute->Default)) {
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
	
	/**
	 * Populates object with database values
	 *
	 * @param void
	 * @return void
	 */
	protected function fetch_attributes() {
		global $mysqli;
		
		$result = $mysqli->query(sprintf(
			'SELECT * FROM `%s` WHERE `%s` = "%d" LIMIT 1',
			$mysqli->real_escape_string($this->table),
			$mysqli->real_escape_string($this->primary_key),
			$mysqli->real_escape_string($this->id)
		));
		
		if ($result->num_rows == 0) {
			throw new Exception("Cannot instantiate non existent record: $this->id");
		}
		
		$row = $result->fetch_object();
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
