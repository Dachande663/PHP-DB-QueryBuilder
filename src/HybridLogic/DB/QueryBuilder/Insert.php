<?php

namespace HybridLogic\DB\QueryBuilder;

/**
 * INSERT Query
 *
 * Build an INSERT query. This type of query expects one or
 * more calls to values() to provide rows to insert.
 *
 * @package QueryBuilder
 * @author Luke Lanchester <luke@lukelanchester.com>
 **/
class Insert extends Query {


	/**
	 * @var string Table
	 **/
	protected $table;


	/**
	 * @var array Table columns
	 **/
	protected $columns;


	/**
	 * @var int Number of columns
	 **/
	protected $num_columns = 0;


	/**
	 * @var array Insert rows
	 **/
	protected $values = array();


	/**
	 * @var bool Whether duplicate rows should be ignored
	 **/
	protected $ignore = false;


	/**
	 * @var array On duplicate key update columns
	 **/
	protected $on_duplicate_key_update;


	/**
	 * Constructor
	 *
	 * @param string Table to insert in to
	 * @param array Columns to insert in to
	 * @return void
	 **/
	public function __construct($table, array $columns) {

		$this->table = $this->_escape_ref($table);

		$cols = array();
		foreach($columns as $col) {
			$cols[] = $this->_escape_ref($col);
		}
		$this->columns = $cols;
		$this->num_columns = count($cols);

	} // end func: __construct



	/**
	 * Add a row to Insert
	 *
	 * @return self
	 **/
	public function values(array $values) {
		if(count($values) !== $this->num_columns) throw new \RuntimeException('Values count must match number of columns to insert');
		$this->values[] = $values;
		return $this;
	} // end func: values



	/**
	 * Set whether INSERT IGNORE should be used
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
	 * Update duplicate rows
	 *
	 * @return void
	 * @author Luke Lanchester
	 **/
	public function on_duplicate_key_update(array $columns) {

		$this->ignore(false);

		$cols = array();

		foreach($columns as $col) {
			$col = $this->_escape_ref($col);
			$cols[] = "$col = VALUES($col)";
		}

		$this->on_duplicate_key_update = $cols;
		return $this;

	} // end func: on_duplicate_key_update



	/**
	 * Execute this INSERT statement
	 *
	 * @param Database DB Wrapper
	 * @return array Result [insert_id, rows_affected]
	 **/
	public function execute($db) {

		if(empty($this->values)) return false;

		$sql = $this->sql($db);

		return $db->query($sql, \HybridLogic\DB::INSERT);

	} // end func: execute



	/**
	 * Generate SQL components
	 *
	 * @param Database DB Wrapper
	 * @return array SQL components
	 **/
	protected function _sql($db) {

		$sql = parent::_sql($db);

		$insert = 'INSERT ';
		if($this->ignore === true) $insert .= 'IGNORE ';
		$sql['insert'] = "$insert INTO {$this->table} (" . implode(', ', $this->columns) . ')';

		$rows = array();
		foreach($this->values as $row) {
			$row = array_map(array($db, 'escape'), $row);
			$rows[] = '(' . implode(', ', $row) . ')';
		}

		$sql['values'] = "VALUES " . implode(', ', $rows);

		if(!empty($this->on_duplicate_key_update)) {
			$sql['on_duplicate_key_update'] = 'ON DUPLICATE KEY UPDATE ' . implode(', ', $this->on_duplicate_key_update);
		}

		return $sql;

	} // end func: _sql



} // end class: Insert