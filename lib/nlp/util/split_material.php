<?php

chdir("../db");

$hand_read = fopen( "material.txt", 'r' );
$hand_write = fopen( "split_material.txt", 'w+' );

$so = scws_new();
$so->set_charset('utf8');

if( !$hand_read )
{
	echo "file read open failed!";
}

if( !$hand_write )
{
	echo "file write open failed!";
}

while( !feof($hand_read) )
{
	$buffer = fgets( $hand_read, 1024 );
	$buffer = trim( $buffer, '\n' );
	$so->send_text( $buffer );

	while( $tmp = $so->get_result() )
	{
		$num = count( $tmp );

		for( $i = 0; $i < $num; $i++ )
		{
			if( $tmp[$i]['word'] == "\n" ||
			    $tmp[$i]['word'] == "。" ||
			    $tmp[$i]['word'] == "，" ||
			    $tmp[$i]['word'] == "、" ||
			    $tmp[$i]['word'] == "？" ||
			    $tmp[$i]['word'] == "【" ||
			    $tmp[$i]['word'] == "】" ||
			    $tmp[$i]['word'] == "：" ) continue;

			fwrite( $hand_write, $tmp[$i]['word'] );
			fwrite( $hand_write, ' ' );
		}
	}
	fwrite( $hand_write, "\n" );
}

$so->close();

fclose( $hand_read );
fclose( $hand_write );

?>
