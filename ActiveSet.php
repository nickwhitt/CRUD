<?php
/**
 * Active Record Set ORM
 *
 * @author Nicholas Whitt <nick.whitt@gmail.com>
 * @copyright Copyright (c) 2012, Nicholas Whitt
 * @link https://github.com/nickwhitt/CRUD Source
 * @license http://www.apache.org/licenses/ Apache License Version 2.0
 */

namespace CRUD;
class ActiveSet extends Base {
	protected $conditions = array();
	
	/**
	 * Statically generate class constructor
	 *
	 * @param void
	 * @return static extended class
	 */
	public static function create($table) {
		return new static($table);
	}
	
	/**
	 * Query termination method
	 *
	 * Executes the constructed query and returns the $styled results
	 *
	 * @param int $style
	 * @return mixed
	 */
	public function fetchAll($class=NULL, $style=\PDO::FETCH_OBJ) {
		$sql = sprintf(
			'select %s from %s %s order by %s',
			$this->primary_key,
			$this->table,
			$this->buildWhere(),
			$this->primary_key
		);
		
		$records = array();
		foreach (Query::query($sql, $this->conditions, $style) as $row) {
			$records[] = is_null($class)
				? new ActiveModel($this->table, $row->id)
				: new $class($row->id);
		}
		
		return $records;
	}
	
	/**
	 * Default where statement
	 *
	 * @param str $statement
	 * @param mixed $condition
	 * @return self
	 */
	protected function buildWhere() {
		return empty($this->conditions) ? '' : sprintf(
			'where %s',
			implode(' and ', array_map(array('self', 'buildCondition'), array_keys($this->conditions)))
		);
	}
	
	protected function buildCondition($value) {
		return sprintf('`%s` = %s', substr($value, 1), $value);
	}
	
	/**
	 * Filter Helper
	 *
	 * Determines the appropriate filter method based on $attribute type
	 *
	 * @param str $attribute
	 * @param mixed $condition
	 * @param bool $negate Negates the filter
	 * @return self
	 */
	public function filterBy($attribute, $condition, $negate=FALSE) {
		return $this->filterByString($attribute, $condition, $negate);
	}
	
	public function filterByString($attribute, $condition, $negate=FALSE) {
		// where X [!]= :Y
		// where X [not] like :Y
		// where X is [not] null
		$this->conditions[":$attribute"] = $condition;
		return $this;
	}
	
	public function filterByList($attribute, array $condition, $negate=FALSE) {
		// where X [not] in (:Y1[, :Yn])
	}
	
	/**
	 * Filter by boolean value
	 *
	 * @param str attribute
	 * @param mixed $condition string, bool, or int
	 * @return self
	 */
	public function filterByBool($attribute, $condition) {
		// for "yes", "on", "true", true, 1: where X = 0
		// for "no", "off", "false", false, 0: where X = 1
	}
	
	/**
	 * Filter by temporal condition
	 *
	 * @param str $attribute
	 * @param mixed $condition string, timestamp, or DateTime object
	 * @param bool $negate Negates the filter
	 * @return self
	 */
	public function filterByDate($attribute, $condition, $negate=FALSE) {
		// where X [!]= :Y
	}
	
	/**
	 * Filter by interval condition
	 *
	 * @param str $attribute
	 * @return array $condition associative keys "min" and/or "max"
	 */
	public function filterByInterval($attribute, array $condition) {
		// min: where X >= :Y
		// max: where X <= :Y
	}
}
