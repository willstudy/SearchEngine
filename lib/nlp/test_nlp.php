<?php
require_once("./nlp.php");
$search_text = "家常豆腐怎么吃啊";

$result = nlp_hander($search_text);

echo "weight : ";
print_r( $result[1] );

echo "sql :";
print_r( $result[0] );

?>
