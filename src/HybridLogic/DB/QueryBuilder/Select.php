<?php

namespace HybridLogic\DB\QueryBuilder;

/**
 * SELECT Query
 *
 * Build a SELECT query. This type of query can be expected
 * to return a resultset.
 *
 * @package QueryBuilder
 * @author Luke Lanchester <luke@lukelanchester.com>
 **/
class Select extends Where {


	/**
	 * @var array SELECT fields
	 **/
	protected $select = array();


	/**
	 * @var string Table name
	 **/
	protected $from;


	/**
	 * @var array JOIN statements
	 **/
	protected $join = array();


	/**
	 * @var array GROUP BY statements
	 **/
	protected $group_by = array();


	/**
	 * @var string Return results as object type
	 **/
	protected $as_object;


	/**
	 * @var array Allowed JOIN types
	 **/
	public $join_types = array(
		'LEFT', 'RIGHT', 'INNER', 'OUTER',
		'LEFT INNER', 'LEFT OUTER', 'RIGHT INNER', 'RIGHT OUTER'
	);


	/**
	 * @var array Allowed JOIN conditions
	 **/
	public $join_conditions = array('=', '<', '<=', '>', '>=', '!=');


	/**
	 * Constructor
	 *
	 * @return void
	 **/
	public function __construct() {
		if(func_num_args() > 0) {
			call_user_func_array(array($this, 'select'), func_get_args());
		}
	} // end func: __construct



	/**
	 * SELECT fields
	 *
	 * Call with all the fields you wish to SELECT for this
	 * query, e.g:
	 *
	 *    ->select('id', 'user_name', 'created')
	 *
	 * Additionally supports field aliases:
	 *
	 *    ->select('id', array('user_name', 'name'))
	 *
	 * @param mixed Field reference, ...
	 * @return self
	 **/
	public function select($field) {

		$args = func_get_args();
		$fields = array();

		foreach($args as $ref) {
			$fields[] = $this->escape_ref($ref);
		}

		$this->select = $fields;

		return $this;

	} // end func: select



	/**
	 * FROM
	 *
	 * Supports either direct table name, e.g:
	 *
	 *    ->from('users')
	 *
	 * Or table aliases:
	 *
	 *     ->from(array('users', 'u'))
	 *
	 * @param mixed Table name
	 * @return self
	 **/
	public function from($from) {
		$this->from = $this->escape_ref($from);
		return $this;
	} // end func: from



	/**
	 * JOIN tables
	 *
	 * JOIN an additional table to this query, used with
	 * the on() method.
	 *
	 *    ->join(array('roles', 'r'), 'LEFT')->on('u.id', '=', 'r.user_id')
	 *
	 * @param mixed Table reference
	 * @param string Join type
	 * @return self
	 **/
	public function join($table, $join_type = null) {

		if($join_type !== null and !in_array(strtoupper($join_type), $this->join_types)) {
			$join_type = null;
		}

		$join_type = ($join_type !== null) ? "$join_type JOIN " : 'JOIN ';
		$this->join[] = $join_type . $this->escape_ref($table);

		return $this;

	} // end func: join



	/**
	 * JOIN on condition
	 *
	 * Used immeditedly after a join(), this specifies
	 * which columns to join on.
	 *
	 * See join() for docs
	 *
	 * @param mixed Field 1 reference
	 * @param string Where condition
	 * @param mixed Field 2 reference
	 * @return self
	 **/
	public function on($field1, $condition, $field2) {

		if(empty($this->join)) return $this;

		if(!in_array($condition, $this->join_conditions)) $condition = '=';

		$expr = $this->escape_ref($field1) . " $condition " . $this->escape_ref($field2);
		$this->join[count($this->join) - 1] .= " ON $expr";

		return $this;

	} // end func: on



	/**
	 * GROUP BY columns
	 *
	 * Allows for grouping of results, condition can be
	 * either a column or an Expression.
	 *
	 *    ->group_by('users.gender')
	 *
	 * @param string Column reference
	 * @return self
	 **/
	public function group_by($column) {

		if(is_object($column) and $column instanceof Expression) {
			$this->group_by[] = (string) $column;
		} else {
			$this->group_by[] = $this->escape_ref($column);
		}

		return $this;

	} // end func: group_by



	/**
	 * As Object
	 *
	 * Supports casting returned rows as objects, with
	 * optional support for specifying the class type.
	 *
	 *     ->as_object(true) // stdClass
	 *     ->as_object('Model_User')
	 *
	 * @param true/string Class name
	 * @return self
	 **/
	public function as_object($class) {

		if($class === true) $class = 'stdClass';
		if(!class_exists($class)) {
			throw new \RuntimeException("Uknown class specified for Select results: $class");
		}

		$this->as_object = $class;
		return $this;

	} // end func: as_object



	/**
	 * Execute this SELECT statement
	 *
	 * @param Database DB Wrapper
	 * @return array Select results
	 **/
	public function execute($db) {

		$sql = $this->sql($db);

		return $db->query($sql, \HybridLogic\DB::SELECT, $this->as_object);

	} // end func: execute



	/**
	 * Generate SQL components
	 *
	 * @param Database DB Wrapper
	 * @return array SQL components
	 **/
	protected function _sql($db) {

		$sql = parent::_sql($db);

		if(!empty($this->select)) {
			$sql['select'] = 'SELECT ' . implode(', ', $this->select);
		} else {
			$sql['select'] = 'SELECT *';
		}

		$sql['from'] = "FROM {$this->from}";
		if($this->group_by) $sql['group_by'] = 'GROUP BY '.implode(', ', $this->group_by);
		if($this->join) $sql['join'] = implode(' ', $this->join);

		return $sql;

	} // end func: _sql



} // end class: Select