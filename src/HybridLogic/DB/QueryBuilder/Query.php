<?php

namespace HybridLogic\DB\QueryBuilder;

/**
 * Common Query Components
 *
 * All queries share some common traits e.g. FROM. This
 * class models those common fields.
 *
 * @package QueryBuilder
 * @author Luke Lanchester <luke@lukelanchester.com>
 **/
abstract class Query {


	/**
	 * @var array Expected SQL Component order
	 **/
	public $sql_component_order = array(
		'select',
		'insert',
		'update',
		'delete',
		'from',
		'join',
		'values',
		'set',
		'where',
		'group_by',
		'order_by',
		'limit',
		'offset',
		'on_duplicate_key_update',
	);


	/**
	 * Execute this QueryBuilder statement
	 *
	 * @param Database DB Wrapper
	 * @return mixed Result
	 **/
	abstract public function execute($db);



	/**
	 * Generate SQL
	 *
	 * @param Database DB Wrapper
	 * @return array SQL components
	 **/
	public function sql($db) {

		$sql_parts = $this->_sql($db);

		$sql = array();

		foreach($this->sql_component_order as $order) {

			if(!isset($sql_parts[$order])) continue;

			$sql[] = $sql_parts[$order];

		}

		return implode(' ', $sql);

	} // end func: sql



	/**
	 * Generate SQL components
	 *
	 * @param Database DB Wrapper
	 * @return array SQL components
	 **/
	protected function _sql($db) {
		return array();
	} // end func: _sql



	/**
	 * Escape a reference
	 *
	 * Given a reference i.e. table or field name, expand
	 * any aliases if necessary and escape the individual
	 * parts.
	 *
	 * @param mixed Reference
	 * @return string Escaped reference
	 **/
	protected function escape_ref($ref) {

		if(is_array($ref) and count($ref) === 2) {
			return $this->_escape_ref($ref[0]).' AS '.$this->_escape_ref($ref[1]);
		} else {
			return $this->_escape_ref($ref);
		}

	} // end func: escape_ref



	/**
	 * Escape table and column string
	 *
	 * E.g. given users.user_name return `users`.`user_name`
	 *
	 * @param string Reference name
	 * @return string Escaped name
	 **/
	protected function _escape_ref($ref) {

		$ref = (string) $ref;

		$ref = '`' . str_replace('.', '`.`', $ref) . '`';

		if(strpos($ref, '`*`') !== false) $ref = str_replace('`*`', '*', $ref);

		return $ref;

	} // end func: _escape_ref



} // end class: Query