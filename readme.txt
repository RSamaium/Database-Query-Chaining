Database Query Chaining Beta 1.0

https://github.com/RSamaium/Database-Query-Chaining

Required :
- PHP 5
- PDO mod (http://www.php.net/manual/en/book.pdo.php)

Use the PHP class to make database queries and data manipulation. 

Example :

$db = new DB("my_username", "my_password", "db_name");
$data = $db	->select("my_table")
			->where(array(
				"id"	=> 	 1
			))
			->fetch();
// => SELECT * FROM my_table WHERE id = "1"
print_r($data);

--------------------------------------

$db	->select("table")
	->where(array(
		"id" 	=>	1
	))
	->where(array(
		"old"	=>	"<50"
	))
	->fetch();
// => SELECT * FROM table WHERE id = "1" AND old < "50"

--------------------------------------

$db	->select("table")
	->orderBy("time", "DESC")
	->orderBy("age")
	->fetchAll();
// => SELECT * FROM table ORDER BY time DESC, age
	
--------------------------------------
	
$db	->select("table")
	->where(array(
		"text" 	=>	"3 > 2"
	), array(
		"secure" 	=> true
	))
	->where(array(
		"age"		=>	"18"
	), array(
		"operator" 	=> "OR"
	))
	->fetch();
	
// => SELECT * FROM table WHERE text = "3 &gt; 2" OR age = "18"

--------------------------------------

$db	->insert("table")
	->values(array(
		"text"		=>	"foo"
	))
	->exec();
// => INSERT INTO table ('text') VALUES ('foo')

--------------------------------------

No matter the order ! :

$db	->select("table", "SUM(price)")
	->orderBy("time")
	->having(array(
		"SUM(price)" 	=> "<1500"
	))
	->where(array(
		"color" 		=>	"blue"
	))
	->limit(10)
	->where(array(
		"age"			=>	">=18"
	))
	->groupBy("column")
	->fetchAll();
// => SELECT SUM(price) FROM table WHERE color = "blue" AND age >= "18" GROUP BY column HAVING SUM(price) < "1500" ORDER BY time LIMIT 10