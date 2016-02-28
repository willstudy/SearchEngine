<?php
/* 分词，默认只分名词 */
function split_word( $text ) 
{
	$result = array();
	$index = 0;

	$so = scws_new();
	$so->set_charset('utf8');

	$so->send_text( $text );

	while( $tmp = $so->get_result() )
	{
		$num = count( $tmp );

		for( $i = 0; $i < $num; $i++ )
		{
			$result[$index++] = $tmp[$i]['word'];
		}
	}
	return $result;
}
/* 进行同义词扩展 */
function syno( $arr )
{

	$syno_file = "./lib/nlp/db/syno.db";
	$hand = fopen( $syno_file, 'r' );

	if( !$hand )
	{
		echo "file $syno_file open failed!";
		exit(1);
	}

	$syno = array();

	/* 初始化同义词词典  */
	while( $buffer = fgets( $hand, 1024 ) )
	{
		$buffer = trim( $buffer );
		$temp = split( '[/]', $buffer );

		$syno[$temp[0]] = $temp[1];
	}

	$arr = array_unique( $arr );
	$result = $arr;

	$num = count( $arr );
	$index = $num;

	for( $i = 0; $i < $num; $i++ )
	{
		$word = $arr[$i];

		if( array_key_exists($word, $syno) )
		{
			$text = $syno[$word];

			$result[$index] = $text;
			$index++;
		}
	}

	fclose( $hand );

	return array_unique($result);
}
/*
 *  核心模块：基于贝叶斯公式，定制的一个分类器，经测试准度较高
 */
function gather( $str ) 
{

	chdir("./lib/nlp/db");
//	chdir("db");

	$hand_read = fopen( "gather.txt", 'r' );

	if( !$hand_read )
	{
		exit("file gather.txt open failed!");
	}

	$gather_container = array();
	/* 初始化数据集 */
	while( !feof($hand_read) )
	{
		$buffer = fgets( $hand_read, 1024 );
		$buffer = trim( $buffer , "\n" );
		list( $name, $title_proba, $material_proba, $type_proba ) = split( '[ :]',$buffer );

		$temp = array();

		$temp['title'] = $title_proba;
		$temp['material'] = $material_proba;
		$temp['type'] = $type_proba;

		$gather_container[$name] = $temp;
	}
	/* 每个类型的总条数 */
	$title_num = 123320;
	$material_num = 292582;
	$type_num = 495875;

	$total = $title_num + $material_num + $type_num;
	/* 这里的比重暂时没想好,越小，说明权重越大 */
	/*
	$title_percent = $total / $title_num ;
	$material_percent = $total / $material_num ;
	$type_percent = $total / $type_num ;
	*/
	$title_percent = 0.3;
	$material_percent = 0.3;
	$type_percent = 0.4;

	$title = $title_percent;
	$material = $material_percent;
	$type = $type_percent;
	$base = 2.7183;

	$num = count( $str );

	for( $i = 0; $i < $num; $i++ )
	{
		$word = $str[$i];

		if( array_key_exists( $word, $gather_container ) )
		{
			$probe_title = $gather_container[$word]['title'];
			$probe_material = $gather_container[$word]['material'];
			$probe_type = $gather_container[$word]['type'];
		}
		else
		{
			$probe_title = 1 / ( $title_num * 2 ) ;
			$probe_material = 1 / ( $material_num * 2 ) ;
			$probe_type = 1 / ( $type_num * 2 ) ;
		}

		$title *= log( $probe_title , $base );
		$material *= log( $probe_material, $base );
		$type *= log( $probe_type, $base );
	}
	/* 值越小，说明越趋近某个分类 */
	$title = abs( $title );
	$material = abs( $material );
	$type = abs( $type );

	$result = array();

	$min_probe = min( $title, $material, $type );
	/* 如果概率相差不大，就可以加入此类型中 */
	if( $title / $min_probe <= 2 )
	{
		$result['title'] = $title;
	}
	if( $material / $min_probe <= 2 )
	{
		$result['material'] = $material;
	}
	if( $type / $min_probe <= 2 )
	{
		$result['type'] = $type;
	}

	fclose( $hand_read );

	return $result;
}

?>