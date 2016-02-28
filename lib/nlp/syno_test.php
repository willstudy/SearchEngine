<?php

require_once("nlp.php");

$arr = array();

$arr[0] = "土豆";
$arr[1] = "红烧肉";
$arr[2] = "马铃薯";
$arr[3] = "西红柿";
$arr[4] = "番茄";
$arr[5] = "番茄";
$arr[6] = "排毒";
$arr[7] = "养颜";

$result = syno( $arr );

print_r( $result );

?>
