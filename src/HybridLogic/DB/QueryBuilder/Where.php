<?php

namespace HybridLogic\DB\QueryBuilder;

/**
 * WHERE Query Components
 *
 * SELECT, UPDATE and DELETE queries can all include where
 * conditionals. This class manages all such statements.
 *
 * @package QueryBuilder
 * @author Luke Lanchester <luke@lukelanchester.com>
 **/
abstract class Where extends Query {


	/**
	 * @var array WHERE conditions
	 **/
	protected $where = array();


	/**
	 * @var array ORDER BY statements
	 **/
	protected $order_by = array();


	/**
	 * @var int LIMIT results
	 **/
	protected $limit = null;


	/**
	 * @var int OFFSET results
	 **/
	protected $offset = null;


	/**
	 * @var array Allowed where conditions
	 **/
	public $allowed_conditions = array(
		'=', '<', '<=', '>', '>=', '!=', 'IN', 'NOT IN', 'BETWEEN', 'LIKE'
	);


	/**
	 * WHERE Condition
	 *
	 * A where condition can be a standard expression, such
	 * as:
	 *
	 *    ->where('user_name', '=', 'johnsmith')
	 *
	 * But can also accept a raw SQL statement that will
	 * not be escaped, i.e:
	 *
	 *    ->where(Expression('DATE(created) BETWEEN start AND end'))
	 *
	 * @param mixed Where condition
	 * @return self
	 **/
	public function where($mixed) {
		$this->where[] = func_get_args();
		return $this;
	} // end func: where



	/**
	 * ORDER BY statement
	 *
	 * Add an order by statement. If you add multiple
	 * statements, the query will be sorted by the first
	 * and any identical rows sorted by the next statement.
	 *
	 * @param string Column reference
	 * @param stirng Direction [ASC, DESC]
	 * @return self
	 **/
	public function order_by($column, $direction='ASC') {

		$column = $this->_escape_ref($column);
		$direction = (strtoupper($direction) === 'DESC') ? 'DESC' : 'ASC';

		$this->order_by[] =  "$column $direction";
		return $this;

	} // end func: order_by



	/**
	 * LIMIT results
	 *
	 * @param int Limit
	 * @return self
	 **/
	public function limit($limit) {
		$limit = abs(intval($limit));
		$this->limit = ($limit > 0) ? $limit : null;
		return $this;
	} // end func: limit



	/**
	 * OFFSET results
	 *
	 * @param int Offset
	 * @return self
	 **/
	public function offset($offset) {
		$offset = abs(intval($offset));
		$this->offset = ($offset > 0) ? $offset : null;
		return $this;
	} // end func: offset



	/**
	 * Generate SQL components
	 *
	 * @param Database DB Wrapper
	 * @return array SQL components
	 **/
	protected function _sql($db) {

		$sql = parent::_sql($db);

		if(!empty($this->where))    $sql['where']    = $this->generate_where_sql($db);
		if(!empty($this->order_by)) $sql['order_by'] = 'ORDER BY ' . implode(', ', $this->order_by);
		if($this->limit !== null)   $sql['limit']    = "LIMIT {$this->limit}";
		if($this->offset !== null)  $sql['offset']   = "OFFSET {$this->offset}";

		return $sql;

	} // end func: _sql



	/**
	 * Return WHERE SQL
	 *
	 * @param Database DB Wrapper
	 * @return string WHERE SQL
	 **/
	protected function generate_where_sql($db) {

		$sql = array();

		foreach($this->where as $where) {

			$where = $this->generate_where_sql_single($where, $db);

			if(empty($where)) continue;
			$sql[] = $where;

		}

		return 'WHERE (' . implode(') AND (', $sql) . ')';

	} // end func: generate_where_sql



	/**
	 * Generate individual WHERE clause SQL
	 *
	 * @param array WHERE condition
	 * @param Database DB Wrapper
	 * @return string SQL
	 **/
	protected function generate_where_sql_single($where, $db) {

		if(is_object($where[0]) and $where[0] instanceof Expression) {
			return $where[0]->sql($db);
		}

		list($field, $condition, $value) = $where;

		if($condition === null) return null;
		$condition = strtoupper($condition);
		if(!in_array($condition, $this->allowed_conditions)) $condition = '=';
		$field = $this->_escape_ref($field);

		switch($condition) {
			case '=':
			case '<':
			case '<=':
			case '>':
			case '>=':
			case '!=':
			case 'LIKE':
				$value = $db->escape((string) $value);
				return "$field $condition $value";
				break;

			case 'IN':
			case 'NOT IN':
				if(!is_array($value)) return null;
				$values = array_map(array($db, 'escape'), $value);
				$value = implode(", ", $values);
				return "$field $condition ($value)";
				break;

			case 'BETWEEN':
				if(!is_array($value) or count($value) !== 2) return null;

				$values = array_map(array($db, 'escape'), $value);
				$values = array_values($values);
				return "$field BETWEEN {$values[0]} AND {$values[1]}";
				break;
		}

		return $where;

	} // end func: generate_where_sql_single



} // end class: Where