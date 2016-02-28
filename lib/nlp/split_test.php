<?php

require_once("nlp.php");

$text = "排毒养颜";

$result = split_word( $text );

print_r( $result );
?>
