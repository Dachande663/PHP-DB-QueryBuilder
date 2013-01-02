<?php

namespace HybridLogic\DB\QueryBuilder;

/**
 * QueryBuilder Factory
 *
 * Handy Factory methods
 *
 * @package QueryBuilder
 * @author Luke Lanchester <luke@lukelanchester.com>
 **/
class Factory {


	/**
	 * Generate a SELECT Query
	 *
	 * @param mixed Field references
	 * @return Select
	 **/
	public static function select() {
		$select = new Select();
		if(func_num_args() > 0) {
			call_user_func_array(array($select, 'select'), func_get_args());
		}
		return $select;
	} // end func: select



	/**
	 * Generate an INSERT Query
	 *
	 * @param string Table reference
	 * @param array Field references
	 * @return Insert
	 **/
	public static function insert($table, array $columns) {
		return new Insert($table, $columns);
	} // end func: insert



	/**
	 * Generate an UPDATE Query
	 *
	 * @param string Table reference
	 * @return Update
	 **/
	public static function update($table) {
		return new Update($table);
	} // end func: update



	/**
	 * Generate a DELETE Query
	 *
	 * @param string Table reference
	 * @return Delete
	 **/
	public static function delete($table) {
		return new Delete($table);
	} // end func: delete



	/**
	 * Raw SQL Expression
	 *
	 * @param string SQL
	 * @param mixed Variables
	 * @return Expression
	 **/
	public static function expression($sql) {
		$params = func_get_args();
		array_shift($params);
		return new Expression($sql, $params);
	} // end func: expression



} // end class: Factory