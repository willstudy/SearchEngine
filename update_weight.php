<?php

$db = mysql_connect("139.129.129.74:3306", "disher", "disher");

if( !$db )
{
	echo "mysql_connect error!\n";
	exit();
}

mysql_select_db( "fairy", $db );
mysql_query("set names 'utf8'");

$url = "";

if( isset($_POST['name']) && $_POST['name'] != "" )
{
	$url = $_POST['name'];
	$sql = "UPDATE dish SET weight=weight+1 WHERE url='$url'";
	
	mysql_query( $sql );
}

?>