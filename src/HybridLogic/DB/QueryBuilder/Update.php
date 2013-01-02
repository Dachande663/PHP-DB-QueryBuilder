<?php

namespace HybridLogic\DB\QueryBuilder;

/**
 * UPDATE Query
 *
 * Build an UPDATE query.
 *
 * @package QueryBuilder
 * @author Luke Lanchester <luke@lukelanchester.com>
 **/
class Update extends Where {


	/**
	 * @var string Table
	 **/
	protected $table;


	/**
	 * @var array Fields to update
	 **/
	protected $fields = array();


	/**
	 * @var bool Whether duplicate rows should be ignored
	 **/
	protected $ignore = false;


	/**
	 * Constructor
	 *
	 * @param string Table to update
	 * @return void
	 **/
	public function __construct($table) {
		$this->table = $this->_escape_ref($table);
	} // end func: __construct



	/**
	 * Set a field value(s)
	 *
	 * @param array Column names => new values
	 * @return self
	 **/
	public function set(array $fields) {

		foreach($fields as $col => $val) {
			$col = $this->_escape_ref($col);
			$this->fields[$col] = $val;
		}

		return $this;

	} // end func: set



	/**
	 * Set whether UPDATE IGNORE should be used
	 *
	 * @param bool Enable IGNORE?
	 * @return self
	 * @author Luke Lanchester
	 **/
	public function ignore($ignore = true) {
		$this->ignore = (bool) $ignore;
		return $this;
	} // end func: ignore



	/**
	 * Execute this UPDATE statement
	 *
	 * @param Database DB Wrapper
	 * @return int Rows updated
	 **/
	public function execute($db) {

		if(empty($this->fields)) return false;

		$sql = $this->sql($db);

		return $db->query($sql, \HybridLogic\DB::UPDATE);

	} // end func: execute



	/**
	 * Generate SQL components
	 *
	 * @param Database DB Wrapper
	 * @return array SQL components
	 **/
	protected function _sql($db) {

		$sql = parent::_sql($db);

		$sql['update'] = ($this->ignore === true) ? "UPDATE IGNORE {$this->table}" : "UPDATE {$this->table}";

		$set = array();
		foreach($this->fields as $col => $val) {
			if(is_object($val) and $val instanceof Expression) {
				$set[] = "$col = " . $val->sql($db);
			} else {
				$set[] = "$col = " . $db->escape($val);
			}
		}
		$sql['set'] = 'SET ' . implode(', ', $set);

		return $sql;

	} // end func: _sql



} // end class: Update