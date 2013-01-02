<?php

namespace HybridLogic\DB\QueryBuilder;

/**
 * DELETE Query
 *
 * Build a DELETE query.
 *
 * @package QueryBuilder
 * @author Luke Lanchester <luke@lukelanchester.com>
 **/
class Delete extends Where {


	/**
	 * @var string Table
	 **/
	protected $table;


	/**
	 * Constructor
	 *
	 * @param string Table to delete from
	 * @return void
	 **/
	public function __construct($table) {
		$this->table = $this->_escape_ref($table);
	} // end func: __construct



	/**
	 * Execute this DELETE statement
	 *
	 * @param Database DB Wrapper
	 * @return int Rows deleted
	 **/
	public function execute($db) {

		$sql = $this->sql($db);

		return $db->query($sql, \HybridLogic\DB::DELETE);

	} // end func: execute



	/**
	 * Generate SQL components
	 *
	 * @param Database DB Wrapper
	 * @return array SQL components
	 **/
	protected function _sql($db) {

		$sql = parent::_sql($db);

		$sql['delete'] = "DELETE FROM {$this->table}";

		return $sql;

	} // end func: _sql



} // end class: Delete