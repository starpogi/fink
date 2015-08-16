<?php
# Basic MySQL PHP Connector
# from Database Journal

$username = "root";
$password = "";
$hostname = "localhost";

//$hostname = "localhost";
//$username = "journeys_rootMan";
//$password = "Z623cihXB&z5";	

$dbh = @mysql_connect($hostname, $username, $password) or die("Unable to connect to MySQL");
$selected = @mysql_select_db("finance", $dbh) or die("Could not select database");
?>
