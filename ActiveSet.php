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
class ActiveSet extends ActiveBase implements \Iterator, \Countable, \ArrayAccess {
	protected $conditions = array();
	protected $parameters = array();
	protected $orders = array();
	
	protected $cursor;
	protected $model_id;
	
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
		$this->dba->traverseInit(
			$this->table,
			$this->conditions,
			$this->parameters,
			$this->orders,
			$this->primary_key
		);
		
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
	 * Filter by numerical comparison condition
	 *
	 * Creates clauses like:
	 *   min: where X >[=] :Y
	 *   max: where X <[=] :Y
	 *
	 * @param str $attribute
	 * @param array $condition associative keys "min" and/or "max"
	 * @param bool $exclusive
	 * @return self
	 */
	public function filterByComparison($attribute, array $conditions, $exclusive=FALSE) {
		if (array_key_exists('min', $conditions)) {
			$this->parameters[] = $conditions['min'];
			$this->conditions[] = $this->dba->buildGreaterThanCondition($attribute, $exclusive);
		}
		if (array_key_exists('max', $conditions)) {
			$this->parameters[] = $conditions['max'];
			$this->conditions[] = $this->dba->buildLessThanCondition($attribute, $exclusive);
		}
		
		return $this;
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
	
	
	/* Iterator Methods */
	
	/**
	 * Retrieves the current model
	 *
	 * @param void
	 * @return ActiveModel
	 */
	public function current() {
		return new ActiveModel($this->dba, $this->table, $this->model_id);
	}
	
	/**
	 * Retrieves the internal cursor position
	 *
	 * @param void
	 * @return int
	 */
	public function key() {
		return $this->cursor;
	}
	
	/**
	 * Increments the internal cursor position
	 *
	 * Stores the new model primary key.
	 *
	 * @param void
	 * @return void
	 */
	public function next() {
		$this->cursor++;
		$this->model_id = $this->dba->traverseNext();
	}
	
	/**
	 * Resets the internal cursor position
	 *
	 * @param void
	 * @return void
	 */
	public function rewind() {
		if ($this->cursor >= 0) $this->dba->traverseReset();
		$this->next();
	}
	
	/**
	 * Tests if the internal cursor points to a valid model
	 *
	 * @param void
	 * @return bool
	 */
	public function valid() {
		return (bool) $this->model_id;
	}
	
	
	/* Countable Methods */
	
	/**
	 * Returns the number of records in the set
	 *
	 * @param void
	 * @return int
	 */
	public function count() {
		return $this->dba->traverseCount();
	}
	
	
	/* ArrayAccess Methods */
	
	/**
	 * Tests if the array offset exists
	 *
	 * @param int $offset
	 * @return bool
	 */
	public function offsetExists($offset) {
		return $offset <= ($this->count() - 1);
	}
	
	/**
	 * Retrieves the offset from the model map
	 *
	 * @param int $offset
	 * @return ActiveModel
	 */
	public function offsetGet($offset) {
		if (!$this->offsetExists($offset)) {
			throw new \Exception(sprintf('Invalid offset: %s[%s]', get_class(), $offset));
		}
		
		return new ActiveModel($this->dba, $this->table, $this->dba->traverseOffset($offset, $this->parameters));
	}
	
	/**
	 * Updates the model map
	 *
	 * Not currently allowed as record set is intended to be read-only.
	 *
	 * @param int $offset
	 * @param mixed $value
	 * @return void
	 */
	public function offsetSet($offset, $value) {
		throw new \Exception(sprintf('%s is read-only', get_class()));
	}
	
	/**
	 * Updates the model map
	 *
	 * Not currently allowed as record set is intended to be read-only.
	 *
	 * @param int $offset
	 * @return void
	 */
	public function offsetUnset($offset) {
		throw new \Exception(sprintf('%s is read-only', get_class()));
	}
}