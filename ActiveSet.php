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
class ActiveSet extends ActiveBase {
	protected $conditions = array();
	protected $parameters = array();
	protected $orders = array();
	
	/**
	 * Statically generate class constructor
	 *
	 * @param void
	 * @return static extended class
	 */
	public static function create(DatabaseLayer $dba, $table) {
		return new static($dba, $table);
	}
	
	/**
	 * Query termination method
	 *
	 * Executes the constructed query and returns the $styled results
	 *
	 * @param int $style
	 * @return mixed
	 */
	public function fetchAll($style=\PDO::FETCH_OBJ) {
		$records = array();
		foreach ($this->dba->selectKeys($this->table, $this->conditions, $this->parameters, $this->orders, $this->primary_key) as $row) {
			$records[] = new ActiveModel($this->dba, $this->table, $row->{$this->primary_key});
		}
		
		return $records;
	}
	
	/**
	 * Query termination method
	 *
	 * Executes the contructed query and returns the $styled result
	 *
	 * @param int $style
	 * @return mixed
	 */
	public function fetchOne($style=\PDO::FETCH_OBJ) {
		$row = $this->dba->selectKey($this->table, $this->conditions, $this->parameters, $this->orders, $this->primary_key);
		return new ActiveModel($this->dba, $this->table, $row->{$this->primary_key});
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
		if (is_array($condition)) {
			return $this->filterByList($attribute, $condition, $negate);
		}
		
		if (is_null($condition) OR strtolower($condition) == 'null') {
			return $this->filterByNull($attribute, $negate);
		}
		
		return $this->filterByString($attribute, $condition, $negate);
	}
	
	/**
	 * Filters results by the conditional string
	 *
	 * Creates clauses like:
	 *   where X [!]= :Y
	 *   where X [not] like :Y
	 *
	 * @param str $attribute
	 * @param str $condition
	 * @param bool $negate
	 * @return self
	 */
	public function filterByString($attribute, $condition, $negate=FALSE) {
		$this->parameters[] = $condition;
		$this->conditions[] = strpos($condition, '%') !== FALSE
			? $this->dba->buildLikeCondition($attribute, $negate)
			: $this->dba->buildEqualCondition($attribute, $negate);
		
		return $this;
	}
	
	/**
	 * Filters NULL results
	 *
	 * Creates clause like: where X is [not] null
	 *
	 * @param str $attribute
	 * @param bool $negate
	 * @return self
	 */
	public function filterByNull($attribute, $negate=FALSE) {
		$this->conditions[] = $this->dba->buildNullCondition($attribute, $negate);
		return $this;
	}
	
	/**
	 * Filters results by the conditional list
	 *
	 * Creates clause like: where X [not] in (:Y1[, :Yn])
	 *
	 * @param str $attribute
	 * @param array $condition
	 * @param bool $negate
	 * @return self
	 */
	public function filterByList($attribute, array $conditions, $negate=FALSE) {
		if (empty($conditions)) {
			trigger_error('Attempting to filter by an empty list');
			return $this;
		}
		
		$this->conditions[] = $this->dba->buildInCondition($attribute, count($conditions), $negate);
		foreach ($conditions as $paramter) {
			$this->parameters[] = $paramter;
		}
		
		return $this;
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
	 * @param array $condition associative keys "min" and/or "max"
	 * @return self
	 */
	public function filterByInterval($attribute, array $condition) {
		// min: where X >= :Y
		// max: where X <= :Y
	}
	
	/**
	 * Orders results by given attribute
	 *
	 * @param str $attribute
	 * @param bool $reverse direction to sort
	 * @return self
	 */
	public function orderBy($attribute, $reverse=FALSE) {
		$this->orders[] = sprintf('%s %s', $attribute, $reverse === FALSE ? 'asc' : 'desc');
		return $this;
	}
}
