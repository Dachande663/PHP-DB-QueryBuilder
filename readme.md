DB Query Builder
====================

A Query Builder for effortlessly composing SQL stamtenets
with support for executing them against HL\DB.


0.0 Table of Contents
---------------------

* Introduction
* Examples
* Running Tests
* Troubleshooting
* Changelog


1.0 Introduction
----------------

Query Builder makes it effortless to create complex SQL
statements from simple method calls. With support for
escaping and aliasing, you'll never worry about injecting
the wrong data into your database ever again.

Query Builder currently targets the MySQL version of the
SQL specification.

@todo Add composer dependancy for github.com/Dachande663/PHP-DB


2.0 Examples
------------

```php
$sql = Query::select(array('m.name', 'movie'), array('t.name', 'theatre_name'), 'visited_date')
	->from(array('visits', 'v'))
	->join(array('movies', 'm'))->on('v.movie_id', '=', 'm.id')
	->join(array('theatres', 't'))->on('v.theatre_id', '=', 't.id')
	->where('m.rating', '>=', 80)
	->order_by('v.visited_date', 'DESC')
	->limit(5)
	->as_object('MovieVisit')
	->sql();
```


3.0 Running Tests
-----------------

phpunit tests

Please note the test suite is currently incomplete.


4.0 Troubleshooting
-------------------

@todo


5.0 Changelog
-------------

* **[2012-12-08]** Initial Version
* **[2013-01-02]** First Release
