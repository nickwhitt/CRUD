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
class ActiveSet extends ActiveBase implements \Iterator {
	protected $conditions = array();
	protected $parameters = array();
	protected $orders = array();
	
	protected $stmt;
	protected $position;
	protected $model;
	
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
	 * @param void
	 * @return self
	 */
	public function fetch() {
		$this->cursor = -1;
		$this->stmt = $this->dba->select(
			$this->primary_key,
			$this->table,
			$this->conditions,
			$this->parameters,
			$this->orders,
			array(\PDO::ATTR_CURSOR => \PDO::CURSOR_SCROLL)
		);
		
		$this->stmt->execute();
		return $this;
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
	
	
	public function current() {
		return $this->model;
	}
	
	public function key() {
		return $this->cursor;
	}
	
	public function next() {
		$id = ($row = $this->stmt->fetch(\PDO::FETCH_OBJ, \PDO::FETCH_ORI_NEXT))
			? $row->{$this->primary_key}
			: NULL;
		
		$this->model = new ActiveModel($this->dba, $this->table, $id);
		$this->cursor = $this->model->primary_key();
	}
	
	public function rewind() {
		if (is_null($this->stmt)) {
			throw new \Exception('Error: Cannot traverse before query termination.');
		}
		
		if ($this->cursor >= 0) $this->fetch();
		$this->next();
	}
	
	public function valid() {
		return $this->model->exists();
	}
}
