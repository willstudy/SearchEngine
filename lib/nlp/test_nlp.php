<?php
require_once("./nlp.php");

$search_text = "家常豆腐怎么做的啊?";

$result = nlp_hander($search_text);

echo "weight : ";
print_r( $result[1] );

echo "sql :";
print_r( $result[0] );

/*
$row['title'] = 'xihongshi';
$row['weight'] = 30;
$search_array[0] = $row;

$row['title'] = 'tudou';
$row['weight'] = 40;
$search_array[1] = $row;

$row['title'] = 'jiachang';
$row['weight'] = 50;
$search_array[2] = $row;

$row['title'] = 'doufu';
$row['weight'] = 60;
$search_array[3] = $row;

$row['title'] = 'qinming';
$row['weight'] = 70;
$search_array[4] = $row;

print_r( $search_array );
echo '--------------------------\n';

quickSort( $search_array, 0, count($search_array) - 1 );

print_r( $search_array );
*/
/*
$arr[0] = '我'; 
$arr[1] = '家常'; 
$arr[2] = '豆腐'; 
$arr[3] = '哪里'; 
$arr[4] = '干嘛'; 

print_r($arr);
echo "----filter------\n";
$result = filter_word( $arr );
print_r($result);
*/
?>
