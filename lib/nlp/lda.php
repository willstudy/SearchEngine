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
}
?>
