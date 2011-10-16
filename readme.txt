	<h1>Database Query Chaining Beta 1.0</h1>

	<p>https://github.com/RSamaium/Database-Query-Chaining</p>

	<p>Required : &#8211; PHP 5 &#8211; PDO mod (http://www.php.net/manual/en/book.pdo.php)</p>

	<p>Use the PHP class to make database queries and data manipulation. </p>

	<p>Example :</p>

	<p>$db = new DB(&#8220;my_username&#8221;, &#8220;my_password&#8221;, &#8220;db_name&#8221;);<br />
$data = $db->select(&#8220;my_table&#8221;)<br />
->where(array(<br />
&#8220;id&#8221;=>  1<br />
))<br />
->fetch();<br />
// => SELECT * FROM my_table WHERE id = &#8220;1&#8221;<br />
print_r($data);<br />
&#8212;&#8212;&#8212;&#8212;&#8212;&#8212;&#8212;&#8212;&#8212;&#8212;&#8212;&#8212;&#8212;&#8212;&#8212;&#8212;&#8212;&#8212;&#8212;<br />
$db->select(&#8220;table&#8221;)<br />
->where(array(<br />
&#8220;id&#8221; =>1<br />
))<br />
->where(array(<br />
&#8220;old&#8221;=>&#8221;<50&#8221;<br />
))<br />
->fetch();<br />
// => SELECT * FROM table WHERE id = &#8220;1&#8221; AND old < &#8220;50&#8221;<br />
&#8212;&#8212;&#8212;&#8212;&#8212;&#8212;&#8212;&#8212;&#8212;&#8212;&#8212;&#8212;&#8212;&#8212;&#8212;&#8212;&#8212;&#8212;&#8212;<br />
$db->select(&#8220;table&#8221;)<br />
->orderBy(&#8220;time&#8221;, &#8220;DESC&#8221;)<br />
->orderBy(&#8220;age&#8221;)<br />
->fetchAll();<br />
// => SELECT * FROM table ORDER BY time DESC, age<br />
&#8212;&#8212;&#8212;&#8212;&#8212;&#8212;&#8212;&#8212;&#8212;&#8212;&#8212;&#8212;&#8212;&#8212;&#8212;&#8212;&#8212;&#8212;&#8212;<br />
$db->select(&#8220;table&#8221;)<br />
->where(array(<br />
&#8220;text&#8221; =>&#8220;3 > 2&#8221;<br />
), array(<br />
&#8220;secure&#8221; => true<br />
))<br />
->where(array(<br />
&#8220;age&#8221;=>&#8220;18&#8221;<br />
), array(<br />
&#8220;operator&#8221; => &#8220;OR&#8221;<br />
))<br />
->fetch();</p>

	<p>// => SELECT * FROM table WHERE text = &#8220;3 > 2&#8221; OR age = &#8220;18&#8221;<br />
&#8212;&#8212;&#8212;&#8212;&#8212;&#8212;&#8212;&#8212;&#8212;&#8212;&#8212;&#8212;&#8212;&#8212;&#8212;&#8212;&#8212;&#8212;&#8212;<br />
$db->insert(&#8220;table&#8221;)<br />
->values(array(<br />
&#8220;text&#8221;=>&#8220;foo&#8221;<br />
))<br />
->exec();<br />
// => INSERT INTO table (&#8216;text&#8217;) VALUES (&#8216;foo&#8217;)<br />
&#8212;&#8212;&#8212;&#8212;&#8212;&#8212;&#8212;&#8212;&#8212;&#8212;&#8212;&#8212;&#8212;&#8212;&#8212;&#8212;&#8212;&#8212;&#8212;<br />
No matter the order ! :</p>

	<p>$db->select(&#8220;table&#8221;, &#8220;<acronym title="price">SUM</acronym>&#8221;)<br />
->orderBy(&#8220;time&#8221;)<br />
->having(array(<br />
&#8220;<acronym title="price">SUM</acronym>&#8221; => &#8221;<1500&#8221;<br />
))<br />
->where(array(<br />
&#8220;color&#8221; =>&#8220;blue&#8221;<br />
))<br />
->limit(10)<br />
->where(array(<br />
&#8220;age&#8221;=>&#8221;>=18&#8221;<br />
))<br />
->groupBy(&#8220;column&#8221;)<br />
->fetchAll();<br />
// => SELECT <acronym title="price">SUM</acronym> FROM table WHERE color = &#8220;blue&#8221; AND age >= &#8220;18&#8221; GROUP BY column HAVING <acronym title="price">SUM</acronym> < &#8220;1500&#8221; ORDER BY time LIMIT 10</p>


 