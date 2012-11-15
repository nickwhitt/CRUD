<?php
/**
 * Base ORM
 *
 * @author Nicholas Whitt <nick.whitt@gmail.com>
 * @copyright Copyright (c) 2012, Nicholas Whitt
 * @link https://github.com/nickwhitt/CRUD Source
 * @license http://www.apache.org/licenses/ Apache License Version 2.0
 */

namespace CRUD;
abstract class ActiveBase {
	protected $dba;
	protected $table;
	protected $primary_key;
	protected $attributes;
	
	public function __construct(Query $dba, $table) {
		$this->dba = $dba;
		$this->table = $table;
		
		$this->getTableStructure();
	}
	
	
	protected function getTableStructure() {
		$this->attributes = array();
		
		foreach ($this->dba->describeTable($this->table) as $attribute) {
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
}
