<?php
require_once("nlp.php");

$str = array();
/*
$str[0] = "西红柿";
$str[1] = "蛋";
*/
$str[0] = "排毒";
$str[1] = "养颜";

$result = gather( $str );

$title = 0;
$material = 0;
$type = 0;

$num = count( $result );
for( $i = 0; $i < $num; $i++ )
{
	if( array_key_exists( 'title', $result ) && $title == 0 )
	{
		echo "title : ";
		echo $result['title'];
		echo "\n";
		$title++;
	}
	if( array_key_exists( 'material', $result ) && $material == 0 )
	{
		echo "material : ";
		echo $result['material'];
		echo "\n";
		$material++;
	}
	if( array_key_exists( 'type', $result ) && $type == 0 )
	{
		echo "type : ";
		echo $result['type'];
		echo "\n";
		$type++;
	}
}
?>
