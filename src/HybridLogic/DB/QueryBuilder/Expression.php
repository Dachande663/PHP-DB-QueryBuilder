<?php

namespace HybridLogic\DB\QueryBuilder;

/**
 * An SQL Expression
 *
 * For when you need to insert a bit of SQL that doesn't
 * quite sit with the regular model. Anything passed in as
 * an Expression will be output as part of the final query
 * untouched.
 *
 * @package QueryBuilder
 * @author Luke Lanchester <luke@lukelanchester.com>
 **/
class Expression {


	/**
	 * @var string SQL (with placeholders)
	 **/
	protected $sql;


	/**
	 * @var array Params to replace with
	 **/
	protected $params;


	/**
	 * Constructor
	 *
	 * @param string SQL
	 * @return void
	 **/
	public function __construct($sql, array $params = array()) {
		$this->sql = $sql;
		$this->params = $params;
	} // end func: __construct



	/**
	 * Generate SQL
	 *
	 * @param Database DB Wrapper
	 * @return string SQL
	 **/
	public function sql($db) {
		return $db->prepare($this->sql, $this->params);
	} // end func: sql



} // end class: Expression