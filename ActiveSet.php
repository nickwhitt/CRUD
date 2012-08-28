<?php
/**
 * Active Record Set ORM
 *
 * @author Nick Whitt
 */

class ActiveSet {
	protected $filters = array();
	protected $conditions = array();
	
	/**
	 * Query termination method
	 *
	 * Executes the constructed query and returns the $styled results
	 *
	 * @param int $style
	 * @return mixed
	 */
	public function fetch($style=\PDO::FETCH_OBJ) {
		
	}
	
	/**
	 * Default where statement
	 *
	 * @param str $statement
	 * @param mixed $condition
	 * @return self
	 */
	public function where($statement, $condition) {
		$this->filters[] = $statement;
		$this->conditions = array_merge(
			$this->conditions,
			is_array($condition) ? $condition : array($condition)
		);
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
		
	}
	
	public function filterByString($attribute, $condition, $negate=FALSE) {
		// where X [!]= :Y
		// where X [not] like :Y
		// where X is [not] null
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
