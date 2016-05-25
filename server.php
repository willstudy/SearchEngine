<!DOCTYPE html>
<html>

<head lang="en">

    <meta charset="UTF-8">
    <title>基于语义的搜索引擎</title>
    <link rel="stylesheet" type="text/css" href="./css/server.css">

</head>

<body>

  <div id="header">
    <form action="./server.php" method="post">
      <a id="index" href="./index.php">精灵</a>
      <input class="search_text" name="search_text" type="text">
      <input class="search_button" name="search_button" type="submit" value="搜 索" >
    </form>
  </div>

  <div id="content">
    <?php

    $log = './log/search.log';
    $error_log = './log/error.log';
    $cache = './data/cache.txt';

    $page_num = 1;
    $search_text = "";

    $handle = fopen($log, 'a+');
    $handle_error = fopen($error_log, 'a+');
    $handle_cache = fopen($cache, 'w+');

    if( ! $handle )
    {
      echo "不能打开文件$log\n";
      exit;
    }

    if( ! $handle_error )
    {
      echo "不能打开文件$error_log\n";
      exit;
    }
/*
    if( ! $handle_cache )
    {
      echo "不能打开文件$cache\n";
      exit;
    }
*/
    $db = mysql_connect("139.129.129.74:3306", "disher", "disher");

    if( !$db )
    {
    	echo "mysql_connect error!\n";
	exit();
    }

    mysql_select_db( "fairy", $db );
    mysql_query("set names 'utf8'");

    if( isset($_POST['search_text']) && $_POST['search_text'] != "" )
    {
        $search_text = $_POST['search_text'];
    }

    if( $_GET ) {
        $page_num = $_GET['page_num']? $_GET['page_num']:1 ;
        $search_text = $_GET['search_text'];
    }

    require_once("./lib/nlp/nlp.php");
    $time_now = microtime();

    /* 返回生成的SQL语句数组，以及对应的权重数组 */
    $result = nlp_hander( $search_text );
    $sql_array = $result[0];
    $weight_array = $result[1];
    $classify = $result[2];

    $search_result = array();
    $index = 0;

    if( array_key_exists('title_and', $weight_array) )
    {
    	$weight = $weight_array['title_and'];
	$sql = $sql_array['title_and'];

	/* 用and连起来的sql语句，是一个数组 */
	$sql_num = count( $sql );
	for( $i = 0; $i < $sql_num; $i++ )
	{
		$title_and_result = mysql_query( $sql[$i] );
		
		while( $row = mysql_fetch_array($title_and_result) )
		{
			$row['weight'] = $row['weight'] * $weight;	
			$search_result[$index++] = $row;
		}
	}
    }

    if( array_key_exists('title_or', $weight_array) )
    {
    	$weight = $weight_array['title_or'];
	$sql = $sql_array['title_or'];            // 用or连起来的sql语句，在数组中只占一个位置

	$title_or_result = mysql_query( $sql );
	
	while( $row = mysql_fetch_array($title_or_result) )
	{
		$row['weight'] = $row['weight'] * $weight;	
		$search_result[$index++] = $row;
	}
    }
    
    if( array_key_exists('material_and', $weight_array) )
    {
    	$weight = $weight_array['material_and'];
	$sql = $sql_array['material_and'];

	$sql_num = count( $sql );
	for( $i = 0; $i < $sql_num; $i++ )
	{
		$material_and_result = mysql_query( $sql[$i] );
		
		while( $row = mysql_fetch_array($material_and_result) )
		{
			$row['weight'] = $row['weight'] * $weight;	
			$search_result[$index++] = $row;
		}
	}
    }
    
    if( array_key_exists('material_or', $weight_array) )
    {
    	$weight = $weight_array['material_or'];
	$sql = $sql_array['material_or'];

	$material_or_result = mysql_query( $sql );
	
	while( $row = mysql_fetch_array($material_or_result) )
	{
		$row['weight'] = $row['weight'] * $weight;	
		$search_result[$index++] = $row;
	}
    }

    if( array_key_exists('type_and', $weight_array) )
    {
    	$weight = $weight_array['type_and'];
	$sql = $sql_array['type_and'];

	$sql_num = count( $sql );
	for( $i = 0; $i < $sql_num; $i++ )
	{
		$type_and_result = mysql_query( $sql[$i] );
		
		while( $row = mysql_fetch_array($type_and_result) )
		{
			$row['weight'] = $row['weight'] * $weight;	
			$search_result[$index++] = $row;
		}
	}
    }

    if( array_key_exists('type_or', $weight_array) )
    {
    	$weight = $weight_array['type_or'];
	$sql = $sql_array['type_or'];

	$type_or_result = mysql_query( $sql );
	
	while( $row = mysql_fetch_array($type_or_result) )
	{
		$row['weight'] = $row['weight'] * $weight;	
		$search_result[$index++] = $row;
	}
    }

    $row_count = count( $search_result );
    quickSort( $search_result, 0, $row_count - 1 );

    $time_end = microtime();

    $row_per_page = 8;
    $page_total = ceil( $row_count / $row_per_page );

    if( $page_num < 1 )
    {
      /* 如当前页小于1，则把当前页置1 */
      $page_num = 1;
    }

    if( $page_num > $page_total )
    {
      /*  当前页大于总页数，则当前页为总页数 */
      $page_num = $page_total;
    }
    ?>
    <p>共找到 <?php echo $row_count;?> 条记录，用时:<?php echo $time_end - $time_now; ?> 秒</p>
    <?php
    $start_now = ($page_num - 1) * $row_per_page;
    ?>
    <table id="big_table" cellspacing="0" cellpadding="0">
      <?php
      	for( $i = $start_now; $i < $start_now + 8; $i++ )
	{
            ?>
            <tr>
              <td>
                <table class="small_table" cellspacing="0" cellpadding="0">
                  <tr>
                    <td>
                      <img class="pic" src="<?php echo $search_result[$i]['picture'];?>" height="100" width="100" alt="<?php echo $search_result[$i]['title'];?>">
                    </td>
                    <td>
                      <table class="show_table">
                        <tr>
                          <td>
                            <a href="<?php echo $search_result[$i]['url'];?>" target="_blank"><b><?php echo $search_result[$i]['title']?></b></a>
                          </td>
                          <tr>
                            <td>
                              <b>原料：</b><?php echo $search_result[$i]['material']; ?>
                            </td>
                          <tr>
                            <td>
                              <b>类型：<?php echo $search_result[$i]['type']; ?></b>
                            </td>
                          </tr>
                        </table>
                    </td>
                  </tr>
                </table>
              </td>
            </tr>
      <?php
    }
      ?>
    </table>
<br/>
<br/>
    <table>
      <tr>
        <?php

        $start_page = $page_num - floor($row_per_page / 2);
        $start_page = $start_page < 1 ? 1 : $start_page;

        $end_page = $page_num + floor($row_per_page / 2);
        $end_page = $end_page > $page_total ? $page_total : $end_page;

        /* 当前的显示的页数不够最大页码时，进行左右调整 */
        $current_page = $end_page - $start_page + 1;

        /* 进行向左调整 */
        if( $current_page < $row_per_page && $start_page > 1 )
        {
          $start_page = $start_page - ($row_per_page - $current_page);
          $start_page = $start_page < 1 ? 1 : $start_page;
          $current_page = $end_page - $start_page + 1;
        }
        /* 向右调整 */
        if( $current_page < $row_per_page && $end_page < $page_total )
        {
          $end_page = $end_page + ($row_per_page - $current_page);
          $end_page = $end_page > $page_total ? $page_total : $end_page;
        }

          /* 最多显示8页 */
        for( $j = $start_page; $j <= $end_page; $j++ )
        {
        ?>
          <td>
            <a href="./server.php?page_num=<?php echo $j;?>&search_text=<?php echo $search_text;?>"><p id="pagenum"><?php echo $j; ?></p></a>
          </td>
        <?php
        }
        ?>
        <td>
          <p>共 <?php echo $page_total;?> 页</p>
        </td>
      </tr>
    </table>

  </div>
<!--
        $write_log = $now.">>".$search_text.";\r\n";

        if( ! mysql_error() )
        {
            fwrite($handle, $write_log);
        }
        else
        {
          fwrite( $handle_error, $write_log );
          echo mysql_error()."\n";
        }
-->

  <div id="footer">
<!--
    <script language=JavaScript>
        today=new Date();
        function initArray(){
            this.length=initArray.arguments.length
            for(var i=0;i<this.length;i++)
                this[i+1]=initArray.arguments[i] }
        var d=new initArray(
                "星期日",
                "星期一",
                "星期二",
                "星期三",
                "星期四",
                "星期五",
                "星期六");
        document.write(
                "<font color=#0000FF style='font-size:9pt;font-family:fantasy'> ",
                today.getFullYear(),"年 ",
                today.getMonth()+1,"月 ",
                today.getDate(),"日 ",
                d[today.getDay()+1],
                "</font>" );
        </script>
-->
    <font color=grey style='font-size:8pt;'>
      Copyright by USTC 2015 - 2016
    </font>
    </div>

</body>
</html>
