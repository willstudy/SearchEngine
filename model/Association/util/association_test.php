<?php

require_once('../../../lib/nlp/nlp.php');
/*
chdir('/var/www/lib/nlp/db');

$file_hand = fopen( 'associate_title.txt', 'r' );

if( !$file_hand )
{
	echo "file open failed!\n";
	exit(1);
}

$result = parse_association( $file_hand );
*/

$classify = array();
$classify['title'] = 0.06;
$classify['material'] = 0.05;
$classify['type'] = 0.01;

$association = get_association( $classify );

echo "==========title==============\n";
if( array_key_exists('title', $association) )
{
	$tmp = $association['title'];

	print_r( $tmp['豆腐'] );
	echo "========================\n";
	print_r( $tmp['春季'] );
}
echo "==========material==============\n";
if( array_key_exists('material', $association) )
{
	$tmp = $association['material'];

	echo "========================\n";
	print_r( $tmp['白米'] );
}
echo "==========type==============\n";
if( array_key_exists('type', $association) )
{
	$tmp = $association['type'];

	print_r( $tmp['清明'] );
	echo "========================\n";
	print_r( $tmp['技巧'] );
}

?>
