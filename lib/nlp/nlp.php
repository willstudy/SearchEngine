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
	chdir("/var/www/");
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
	if( $title / $min_probe <= 1.618 )
	{
		$result['title'] = $title;
	}
	if( $material / $min_probe <= 1.618 )
	{
		$result['material'] = $material;
	}
	if( $type / $min_probe <= 1.618 )
	{
		$result['type'] = $type;
	}

	fclose( $hand_read );

	return $result;
}

/*
 * 输入：文件指针
 * 输出：解析后，保存词关联度的数据结构
 */
function parse_association( $file_hand )
{
	$result = array();

	while( !feof($file_hand) )
	{
		$buffer = fgets( $file_hand, 1024 );
		$buffer = trim( $buffer );
		$temp = split( '[:]', $buffer );
		
		$num = count( $temp );
		
		$word_list = array();
		for( $i = 1; $i < $num; $i += 2 )
		{
			$word_list[$temp[$i]] = $temp[$i+1];	
		}
		$result[$temp[0]] = $word_list;
	}

	return $result;
}

/*
 * 得到指定分类的词关联度
 */
function get_association( $classify )
{
	chdir('/var/www');
	$title_flag = 0;
	$material_flag = 0;
	$type_flag = 0;

	$association = array();
	$num = count($classify);	

	for( $i = 0; $i < $num; $i++ )
	{
		if( array_key_exists('title', $classify ) && $title_flag == 0 )
		{
			$title_file = fopen('./lib/nlp/db/associate_title.txt', 'r');

			if( !$title_file )
			{
				echo "file associate_title.txt open failed!\n";
				exit(1);
			}

			$title_flag++;
			$association['title'] = parse_association( $title_file );
		}

		if( array_key_exists('material', $classify ) && $material_flag == 0 )
		{
			$material_file = fopen('./lib/nlp/db/associate_material.txt', 'r');

			if( !$material_file )
			{
				echo "file associate_material.txt open failed!\n";
				exit(1);
			}

			$material_flag++;
			$association['material'] = parse_association( $material_file );
		}
		if( array_key_exists('type', $classify ) && $type_flag == 0 )
		{
			$type_file = fopen('./lib/nlp/db/associate_type.txt', 'r');

			if( !$type_file )
			{
				echo "file associate_type.txt open failed!\n";
				exit(1);
			}

			$type_flag++;
			$association['type'] = parse_association( $type_file );
		}
	}

	return $association;
}

/*
 * 根据同义词扩展后的词条、类别和词关联表，来生成一个sql查询语句数组，
 * 并分配合适的权重。
 */
function generate_sql( $final_word, $classify, $association )
{
	$result_sql = array();
	$result_weight = array();

	$magic = 0.618;
	$word_num = count( $final_word );

	$sql = "SELECT id,url,title,picture,material,type FROM dish WHERE";

	if( array_key_exists( 'title', $classify ) ) {

		$weight = 1000.0;
		$weight = $weight / $classify['title'];

		$weight_and = $weight;
		$weight_or = $weight;

		$word_assoc = array();
		$associate_title = $association['title'];

		$sql_title_and = $sql;
		$sql_title_or = $sql;
		$sql_title = array();

		$index = 0;
		$OR_flag = 0;
		$AND_flag = 0;

		for( $i = 0; $i < $word_num; $i++ ) {

			$sql_title_and = $sql;
			$word = $final_word[$i];

			/*  已经出现在word_assoc中，说明该词已经添加到sql_title中了  */
			if( array_key_exists( $word, $word_assoc ) ) continue;
			if( in_array( $word, $word_assoc) ) continue;

			if( ! array_key_exists( $word, $associate_title ) ) {

				if( $OR_flag > 0 ) $sql_title_or .= " OR";

				$sql_title_or .= " title like '%$word%'";
				$weight_or *= 0.618; 
				$OR_flag++;

				continue;
			}

			$word_list = $associate_title[$word];     // 该词的关联词表
			$max_count = 0;
			$word_most_like = "";

			for( $j = 0; $j < $word_num; $j++ ) {

				if( !array_key_exists($final_word[$j], $word_list) ) continue;
				/* 找出分割词条中关联度最高的那个 */
				if( $word_list[$final_word[$j]] > $max_count ) $word_most_like = $final_word[$j];
			}

			$word_assoc[$word] = $word_most_like;

			/* 找到了一个关联词 */	
			if( $word_most_like != "" )
			{
				$sql_title_and .= " title like '%$word%'" ;
				$sql_title_and .= " AND title like '%$word_most_like%'";
				$sql_title[$index++] = $sql_title_and;
				$AND_flag++;
			}
			else if( $OR_flag == 0 )	/* 在关联度列表中没有找到的情况下 */
			{
				$sql_title_or .= " title like '%$word%'" ;
				$OR_flag++;
			}
			else $sql_title_or .= " OR title like '%$word%'";
		}

		if( $AND_flag )
		{
			$result_weight['title_and'] = $weight;
			$result_sql['title_and'] = $sql_title;
		}
		if( $OR_flag )
		{
			$result_weight['title_or'] = $weight_or;
			$result_sql['title_or'] = $sql_title_or;
		}
	}

	if( array_key_exists( 'material', $classify ) ) {

		$weight = 1000.0;
		$weight = $weight / $classify['material'];

		$weight_and = $weight;
		$weight_or = $weight;

		$word_assoc = array();
		$associate_material = $association['material'];

		$sql_material_and = $sql;
		$sql_material_or = $sql;
		$sql_material = array();

		$index = 0;
		$OR_flag = 0;
		$AND_flag = 0;

		for( $i = 0; $i < $word_num; $i++ ) {

			$sql_material_and = $sql;
			$word = $final_word[$i];

			/*  已经出现在word_assoc中，说明该词已经添加到sql_material中了  */
			if( array_key_exists( $word, $word_assoc ) ) continue;
			if( in_array( $word, $word_assoc) ) continue;

			if( ! array_key_exists( $word, $associate_material ) ) {

				if( $OR_flag > 0 ) $sql_material_or .= " OR";

				$sql_material_or .= " material like '%$word%'";
				$weight_or *= 0.618; 
				$OR_flag++;

				continue;
			}

			$word_list = $associate_material[$word];     // 该词的关联词表
			$max_count = 0;
			$word_most_like = "";

			for( $j = 0; $j < $word_num; $j++ ) {

				if( !array_key_exists($final_word[$j], $word_list) ) continue;
				/* 找出分割词条中关联度最高的那个 */
				if( $word_list[$final_word[$j]] > $max_count ) $word_most_like = $final_word[$j];
			}

			$word_assoc[$word] = $word_most_like;

			/* 找到了一个关联词 */	
			if( $word_most_like != "" )
			{
				$sql_material_and .= " material like '%$word%'" ;
				$sql_material_and .= " AND material like '%$word_most_like%'";
				$sql_material[$index++] = $sql_material_and;
				$AND_flag++;
			}
			else if( $OR_flag == 0 )	/* 在关联度列表中没有找到的情况下 */
			{
				$sql_material_or .= " material like '%$word%'" ;
				$OR_flag++;
			}
			else $sql_material_or .= " OR material like '%$word%'";
		}

		if( $AND_flag )
		{
			$result_weight['material_and'] = $weight;
			$result_sql['material_and'] = $sql_material;
		}
		if( $OR_flag )
		{
			$result_weight['material_or'] = $weight_or;
			$result_sql['material_or'] = $sql_material_or;
		}
	}

	if( array_key_exists( 'type', $classify ) ) {

		$weight = 1000.0;
		$weight = $weight / $classify['type'];

		$weight_and = $weight;
		$weight_or = $weight;

		$word_assoc = array();
		$associate_type = $association['type'];

		$sql_type_and = $sql;
		$sql_type_or = $sql;
		$sql_type = array();

		$index = 0;
		$OR_flag = 0;
		$AND_flag = 0;

		for( $i = 0; $i < $word_num; $i++ ) {

			$sql_type_and = $sql;
			$word = $final_word[$i];

			/*  已经出现在word_assoc中，说明该词已经添加到sql_type中了  */
			if( array_key_exists( $word, $word_assoc ) ) continue;
			if( in_array( $word, $word_assoc) ) continue;

			if( ! array_key_exists( $word, $associate_type ) ) {

				if( $OR_flag > 0 ) $sql_type_or .= " OR";

				$sql_type_or .= " type like '%$word%'";
				$weight_or *= 0.618; 
				$OR_flag++;

				continue;
			}

			$word_list = $associate_type[$word];     // 该词的关联词表
			$max_count = 0;
			$word_most_like = "";

			for( $j = 0; $j < $word_num; $j++ ) {

				if( !array_key_exists($final_word[$j], $word_list) ) continue;
				/* 找出分割词条中关联度最高的那个 */
				if( $word_list[$final_word[$j]] > $max_count ) $word_most_like = $final_word[$j];
			}

			$word_assoc[$word] = $word_most_like;

			/* 找到了一个关联词 */	
			if( $word_most_like != "" )
			{
				$sql_type_and .= " type like '%$word%'" ;
				$sql_type_and .= " AND type like '%$word_most_like%'";
				$sql_type[$index++] = $sql_type_and;
				$AND_flag++;
			}
			else if( $OR_flag == 0 )	/* 在关联度列表中没有找到的情况下 */
			{
				$sql_type_or .= " type like '%$word%'" ;
				$OR_flag++;
			}
			else $sql_type_or .= " OR type like '%$word%'";
		}

		if( $AND_flag )
		{
			$result_weight['type_and'] = $weight;
			$result_sql['type_and'] = $sql_type;
		}
		if( $OR_flag )
		{
			$result_weight['type_or'] = $weight_or;
			$result_sql['type_or'] = $sql_type_or;
		}
	}

	return array( $result_sql, $result_weight, $classify );
}

/*
 * 输入：用户的输入
 * 输出：不同的SQL查询语句已经对应的权重
 */
function nlp_hander( $search_text )
{
	$sql_array = array();

	/* 对用户的输入进行分词  */
	$final_text = split_word( $search_text );
	/* 同义词扩展 */
	$final = syno( $final_text );
	/* 贝叶斯公式，对用户输入进行分类 */
	$classify = gather( $final );
	/* 根据分类结果，获取词关联度词典 */
	$association = get_association( $classify );
	
	$sql_array = generate_sql( $final, $classify, $association );

	return $sql_array;
}
?>
