<?php echo '<pre>';

include './autoload.php';

use \HybridLogic\DB\QueryBuilder\Factory as Query;


// Connect
	$pdo = new \HybridLogic\DB\Driver\PDO(array(
		'datasource' => 'mysql:host=localhost;dbname=myapp_test',
		'username'   => 'root',
		'password'   => 'root',
	));

	$db = new \HybridLogic\DB($pdo);


// Setup
	$db->query('DROP TABLE IF EXISTS `users`');

	$db->query("
		CREATE TABLE `users` (
			`id` int(11) NOT NULL AUTO_INCREMENT,
			`user_name` VARCHAR(50) DEFAULT NULL,
			`date_of_birth` DATE DEFAULT NULL,
			`gender` ENUM('male', 'female', 'unknown') DEFAULT 'unknown',
			PRIMARY KEY(`id`)
		);
	");

	$db->query('DROP TABLE IF EXISTS `notable_for`');

	$db->query("
		CREATE TABLE `notable_for` (
			`id` int(11) NOT NULL AUTO_INCREMENT,
			`user_id` int(11) DEFAULT NULL,
			`action` VARCHAR(50) DEFAULT NULL,
			PRIMARY KEY(`id`)
		);
	");


// Insert
	$insert = Query::insert('users', array('user_name', 'date_of_birth', 'gender'))
		->values(array('Tim Berners-Lee', '1955-06-08', 'male'))
		->values(array('Steve Jobs', '1955-02-24', 'male'))
		->values(array('Marissa Mayer', '1975-05-30', 'female'))
		->values(array('Bill Gates', '1955-10-28', 'male'))
		->values(array('Rasmus Lerdorf', '1968-10-22', 'male'))
		->values(array('Guido van Rossum', '1956-01-31', 'male'))
	;
	var_dump($insert->execute($db));

	Query::insert('notable_for', array('user_id', 'action'))
		->values(array(1, 'www'))
		->values(array(2, 'apple'))
		->values(array(2, 'ipod'))
		->values(array(3, 'yahoo'))
		->values(array(4, 'windows'))
		->values(array(4, 'philanthropy'))
		->values(array(5, 'php'))
		->values(array(6, 'python'))
		->values(array(6, 'google'))
		->execute($db);


// Update
	$update = Query::update('users')
		->set(array(
			'user_name' => Query::Expression('CONCAT(user_name, ?)', ' Updated!')
		))
		->where('date_of_birth', 'BETWEEN', array('1955-01-01', '1955-12-31'))
	;
	var_dump($update->execute($db));


// Delete
	$delete = Query::delete('users')
		->where('gender', '=', 'female');
	var_dump($delete->execute($db));


// Select
	class NotableUser {
		public function printInfo() {
			return "{$this->name} notable for {$this->notable_for}";
		}
	}

	$select = Query::select(array('u.user_name', 'name'), array('n.action', 'notable_for'))
		->from(array('users', 'u'))
		->join(array('notable_for', 'n'))->on('u.id', '=', 'n.user_id')
		->where('u.date_of_birth', '<', '1960-01-01')
		->order_by('u.user_name', 'ASC')
		->limit(5)
		->as_object('NotableUser')
	;

	$users = $select->execute($db);
	foreach($users as $user) {
		echo "<li>{$user->printInfo()}</li>";
	}
