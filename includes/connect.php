<?php
$connect = mysql_connect('localhost', 'u432359251_djole', 'djoleacab031');
if(!$connect){
	die('Could not connect to database!');
}
mysql_select_db('u432359251_stock', $connect);
?>