<?php

class LDA {
	/* 单词到id的映射 */
	public $word2id;
	/* 保存着所有的菜名 */
	public $doc;
	/* 保存着每道菜的单词个数 */
	public $doc_word_num;
	/* 保存着每道菜在数据库中的位置 */
	public $doc_index; 
	/* 保存着每个单词对应的主题 */
	public $doc_word_topic;
	/* 保存着每个主题所包含的单词总数 */
	public $topic;
	/* 保存每篇文章的每一个主题对应的单词个数 */
	public $doc_topic;
	/* 保存每个单词所对应的主题，而出现的个数 */
	public $word_id_topic;

	public $M;
	public $V;
	public $K;
	public $alpha;
	public $beta;	

	public $new_word2id;
	public $new_doc;
	public $new_doc_word_topic;
	public $new_topic;
	public $new_word_id_topic;
	public $word_num;

	public $theta;
	public $new_theta;

	public function __construct() {

		$this->word2id = Array();
		$this->doc = Array();
		$this->doc_word_num = Array();
		$this->doc_index = Array();
		$this->doc_word_topic = Array();
		$this->topic = Array();
		$this->doc_topic = Array();
		$this->word_id_topic = Array();

		$this->alpha = 0.5;
		$this->beta = 0.1;
	}

	public function load_model( $model_file ) {

		$file_hand = fopen( $model_file, 'r' );

		if( !$file_hand ) {
			echo "$model_file open failed!\n";
			return -1;
		}

		$buffer = fgets( $file_hand, 1024 );
		$buffer = trim($buffer, '\n');
		$items = split('[: ]', $buffer);

		$this->V = (int)$items[0];
		$this->K = (int)$items[1];
		$this->alpha = (float)$items[2];
		$this->beta = (float)$items[3];

		$doc_id = 0;
		while( !feof($file_hand) ) {
			$buffer = fgets( $file_hand, 1024 );
			$buffer = trim($buffer, "\n");
			$items = split(' ', $buffer);
			
			$num = count($items);
			/* 保存当前文章的单词 */
			$document = Array();	
			/* 保存当前文章的主题对应的单词个数 */
			$document_topic = Array();
			$word_topic = Array();

			$this->doc_index[$doc_id] = (int)$items[0];
			$this->doc_word_num[$doc_id] = $num - 2;

			for( $i = 1; $i < $num - 1; $i++ ) {
				$pair = split(':',$items[$i]);
				$word = $pair[0];
				$topic = (int)$pair[1];
				
				$document[$i-1] = $word;
				$word_topic[$i-1] = $topic;

				/* 初始化单词--ID的映射 */
				if( !array_key_exists($word, $this->word2id) ) {
					$size = count($this->word2id);
					$this->word2id[$word] = $size;
				}
				/* 更新每个主题的单词总数 */	
				if( array_key_exists($topic, $this->topic) ) {
					$this->topic[$topic] += 1;
				}
				else {
					$this->topic[$topic] = 1;
				}
				/* 更新当前文章的主题对应的单词个数 */
				if( array_key_exists($topic, $document_topic) ) {
					$document_topic[$topic] += 1;
				}
				else {
					$document_topic[$topic] = 1;
				}
				
				$word_id = $this->word2id[$word];
				/* 更新当前单词，有多少个单词对应的同一个主题 */
				if( array_key_exists( $word_id, $this->word_id_topic ) ) {
					$topic_array = $this->word_id_topic[$word_id];
					if( array_key_exists( $topic, $topic_array ) ) {
						$this->word_id_topic[$word_id][$topic] += 1;
					}
					else {
						$this->word_id_topic[$word_id][$topic] = 1;
					}
				}
				else {
					$topic_array = Array();
					for( $k = 0; $k < $this->K; $k++ ) $topic_array[$k] = 0;

					$topic_array[$topic] = 1;
					$this->word_id_topic[$word_id] = $topic_array;
				}
			}

			$this->doc[$doc_id] = $document;
			$this->doc_topic[$doc_id] = $document_topic;
			$this->doc_topic[$doc_id] = $word_topic;

			$doc_id++;
		}

		$this->M = $doc_id;
		$this->V = count($this->word2id);
	}

	public function initial( $final_word )
	{
		$this->new_word2id = Array();
		$this->new_doc = $final_word;
		$this->new_word_id_topic = Array();
		$this->new_doc_word_topic = Array();
		$this->new_topic = Array();

		$num  = count($final_word);
		srand(time(NULL));
		
		/* 初始化 new_doc_word_topic[][] */
		for( $i = 0; $i < $num; $i++ ) {
			$word_topic = Array();
			for( $j = 0; $j < $this->K; $j++ ){
				$word_topic[$j] = 0;
			}
			$this->new_doc_word_topic[$i] = $word_topic;
		}
		/* 初始化 new_topic[] */
		for( $i = 0; $i < $this->K; $i++ )
		{
			$this->new_topic[$i] = 0;
		}

		$this->word_num = $num;

		for( $i = 0; $i < $num; $i++ ) {
			if( !array_key_exists($final_word[$i], $this->new_word2id) ) {
				$size = count($this->new_word2id);
				$this->new_word2id[$final_word[$i]] = $size;
			}

			$topic = rand(0, $this->K - 1);
			
			if( array_key_exists($topic, $this->new_topic) ) {
				$this->new_topic[$topic] += 1;
			}
			else {
				$this->new_topic[$topic] = 1;
			}

			$this->new_doc_word_topic[$i] = $topic;
			$word_id = $this->new_word2id[$final_word[$i]];

			if( array_key_exists($word_id, $this->new_word_id_topic) ) {
				$topic_array = $this->new_word_id_topic[$word_id];

				if( array_key_exists($topic, $topic_array) ) {
					$this->new_word_id_topic[$word_id][$topic] += 1;
				}
				else {
					$this->new_word_id_topic[$word_id][$topic] = 1;
				}
			}
			else {
				$topic_array = Array();
				for( $k = 0; $k < $this->K; $k++ ) $topic_array[$k] = 0;

				$topic_array[$topic] = 1;
				$this->new_word_id_topic[$word_id] = $topic_array;
			}
		}
	}

	public function sampling( $index )
	{
		$topic = $this->new_doc_word_topic[$index];
		$word = $this->new_doc[$index];
		
		$word_id = -1;
		if( array_key_exists($word, $this->word2id) ) 
			$word_id = $this->word2id[$word];
		else return -1;

		$new_word_id = $this->new_word2id[$word];

		$this->new_topic[$topic] -= 1;
		$this->new_word_id_topic[$new_word_id][$topic] -= 1;
		$this->word_num -= 1;

		$Vbeta = $this->V * $this->beta;
		$Kalpha = $this->K * $this->alpha;

		$probe = Array();
		for( $i = 0; $i < $this->K; $i++ ) {
			$p = ($this->word_id_topic[$word_id][$i] 
				+ $this->new_word_id_topic[$new_word_id][$i] 
				+ $this->beta);
			$p /= ($this->topic[$i] + $this->new_topic[$i] + $Vbeta);

			$p *= (($this->new_topic[$i] + $this->alpha) / ($this->word_num + $Kalpha)); 

			$probe[$i] = $p;
		}

		for( $i = 1; $i < $this->K; $i++ ) {
			$probe[$i] += $probe[$i-1];
		}

		$priot = ((float)rand() / getrandmax() ) * $probe[$this->K - 1]; 

		for( $i = 0; $i < $this->K; $i++ ){
			if( $probe[$i] > $priot ) break;
		}

		$this->new_topic[$i] += 1;
		$this->new_word_id_topic[$new_word_id][$i] += 1;
		$this->word_num += 1;

		return $i;
	}

	public function compute_theta() 
	{
		$this->new_theta = Array();

		for( $i = 0; $i < $this->K; $i++ ) 
		{
			$this->new_theta[$i] = ($this->new_topic[$i] + $this->alpha) / 
				($this->word_num + $this->alpha * $this->K);
		}
	}

	public function inference() 
	{
		$num = count($this->new_doc_word_topic);
		$max_iter = 100;

		for( $iter = 0; $iter < $max_iter; $iter++ ) {
			for( $i = 0; $i < $num; $i++ ) {
				$topic = $this->sampling($i);
				if( $topic == -1 ) continue;
				$this->new_doc_word_topic[$i] = $topic;
			}
		}

		$this->compute_theta();
	}

	public function load_theta( $theta_file )
	{
		$file_hand = fopen( $theta_file, 'r' );

		if( !$file_hand ) 
		{
			echo "$theta_file open failed!\n";
			return;
		}

		$doc_id = 0;
		while( !feof($file_hand) ) {
			$buffer = fgets( $file_hand, 1024 );
			$buffer = trim($buffer, '\n');
			$items = split(' ', $buffer );

			$theta_array = array_slice( $items, 1, $this->K );

			$this->theta[$doc_id] = $theta_array;
			$doc_id += 1;
		}
	}
	
	public function get_distance( $theta_array )
	{
		$distance = 0.0;

		for( $i = 0; $i < $this->K; $i++ ) {
			$offset = $this->new_theta[$i] - $theta_array[$i];
			$distance += $offset * $offset; 
		}

		return $distance;
	}

	public function get_similar()
	{
		$result_array = Array();

		$num = count($this->theta);

		for( $i = 0; $i < $num; $i++ ) {
			$doc_index = $this->doc_index[$i];
			$result_array[$doc_index] = $this->get_distance($this->theta[$i]);
		}
		
		asort($result_array);

		$result = Array();
		$index = 0;

		foreach($result_array as $key=>$value)
		{
			$result[$index++] = $key;
			if( $index > 50 ) return $result;
		}
	}
}
/*
$lda = new LDA();

$lda->load_model('/var/www/model/LDA/data/recai_model.txt');
$lda->load_theta('/var/www/model/LDA/data/recai_theta.txt');

$final_word = Array();
$final_word[0] = '西红柿';
$final_word[1] = '番茄';

$lda->initial( $final_word );
$lda->inference();

print_r($lda->get_similar());
*/
?>
