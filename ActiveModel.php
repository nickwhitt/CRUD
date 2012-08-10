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
			$this->id = $id;
			$this->fetchAttributes();
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
		if (!$row = Query::fetchByPrimaryKey($this->table, $this->id, $this->primary_key)) {
			throw new \Exception("Cannot instantiate non existent record: $this->id");
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
