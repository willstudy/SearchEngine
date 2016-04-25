<?php
/*
 *  LDA 推断模块
 *
 *  包含两个部分：
 *
 *  1：朴素贝叶斯分类器，将菜名分为：热菜、汤羹、小吃
 *
 *  2：LDA 模型的推断实现，以及返回一个菜名主题相关的近似结果
 *
 */

function title_classifier( $final_word )
{

	chdir("./model/Bayes/bayes_son/data");

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
		list( $name, $recai_proba, $tanggeng_proba, $xiaochi_proba ) = split( '[ :]',$buffer );

		$temp = array();

		$temp['recai'] = $recai_proba;
		$temp['tanggeng'] = $tanggeng_proba;
		$temp['xiaochi'] = $xiaochi_proba;

		$gather_container[$name] = $temp;
	}
	/* 每个类型的总条数 */
	$recai_num = 66550;
	$tanggeng_num = 23848;
	$xiaochi_num = 34423;

	$total = $recai_num + $tanggeng_num + $xiaochi_num;
	/* 这里的比重越小，说明权重越大 */
	$recai_percent = 0.3;
	$tanggeng_percent = 0.3;
	$xiaochi_percent = 0.4;

	$recai = $recai_percent;
	$tanggeng = $tanggeng_percent;
	$xiaochi = $xiaochi_percent;
	$base = 2.7183;

	$num = count( $str );

	for( $i = 0; $i < $num; $i++ )
	{
		$word = $str[$i];

		if( array_key_exists( $word, $gather_container ) )
		{
			$probe_recai = $gather_container[$word]['recai'];
			$probe_tanggeng = $gather_container[$word]['tanggeng'];
			$probe_xiaochi = $gather_container[$word]['xiaochi'];
		}
		else
		{
			$probe_recai = 1 / ( $recai_num * 2 ) ;
			$probe_tanggeng = 1 / ( $tanggeng_num * 2 ) ;
			$probe_xiaochi = 1 / ( $xiaochi_num * 2 ) ;
		}

		$recai *= log( $probe_recai , $base );
		$tanggeng *= log( $probe_tanggeng, $base );
		$xiaochi *= log( $probe_xiaochi, $base );
	}
	/* 值越小，说明越趋近某个分类 */
	$recai = abs( $recai );
	$tanggeng = abs( $tanggeng );
	$xiaochi = abs( $xiaochi );

	$result = array();

	$min_probe = min( $recai, $tanggeng, $xiaochi );
	/* 如果概率相差不大，就可以加入此类型中 */
	if( $recai / $min_probe <= 1.618 )
	{
		$result['recai'] = $recai;
	}
	if( $tanggeng / $min_probe <= 1.618 )
	{
		$result['tanggeng'] = $tanggeng;
	}
	if( $xiaochi / $min_probe <= 1.618 )
	{
		$result['xiaochi'] = $xiaochi;
	}

	fclose( $hand_read );

	return $result;
}

function load_model()
{
}

/*
 *  返回的是一个保存菜名id的数组
 *  这个数组中保存的id所对应的菜，均与参数菜名有近似的主题分布
 *
 */
function lda_inference( $final_text )
{
}

?>
