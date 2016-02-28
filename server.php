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

    if( ! $handle_cache )
    {
      echo "不能打开文件$cache\n";
      exit;
    }

    $db = mysql_connect("139.129.129.74:3306", "****", "****");

    if( !$db )
    {
      die('Could not connect: ' . mysql_error());
    }

    mysql_select_db("fairy", $db);
    mysql_query("set names 'utf8'");

    if( isset($_POST['search_text']) && $_POST['search_text'] != "" )
    {
        $search_text = $_POST['search_text'];
    }

    if( $_GET ) {
        $page_num = $_GET['page_num']? $_GET['page_num']:1 ;
        $search_text = $_GET['search_text'];
    }

    $time_now = time();

    require_once("./lib/nlp/nlp.php");
    /* 对用户的输入进行分词  */
    $final_text = split_word( $search_text );
    /* 同义词扩展 */
    $final = syno( $final_text );
    /* 贝叶斯公式，对用户输入进行分类 */
    $classify = gather( $final );

    $title = 0;
    $material = 0;
    $type = 0;

    $sql = "SELECT id,url,title,picture,material,type FROM dish WHERE";

    $num = count( $classify );
    $word_num = count( $final );

    for( $i = 0; $i < $num; $i++ )
    {
    	if( array_key_exists( 'title', $classify ) && $title == 0 )
	{
		$sql .= " title LIKE '%$final[0]%'";

		for( $i = 1; $i < $word_num; $i++ )
		{
			$sql .= " OR title LIKE '%$final[$i]%'";
		}
	}
    	if( array_key_exists( 'material', $classify ) && $material == 0 )
	{
		if( $num > 1 )
		{
			for( $i = 0; $i < $word_num; $i++ )
			{
				$sql .= " OR material LIKE '%$final[$i]%'";
			}
		}
		else
		{
			$sql .= " material LIKE '%$final[0]%'";

			for( $i = 1; $i < $word_num; $i++ )
			{
				$sql .= " OR material LIKE '%$final[$i]%'";
			}
		}

		$material++;
	}
    	if( array_key_exists( 'type', $classify ) && $type == 0 )
	{
		if( $num > 1 )
		{
			for( $i = 0; $i < $word_num; $i++ )
			{
				$sql .= " OR type LIKE '%$final[$i]%'";
			}

		}
		else
		{
			$sql .= " type LIKE '%$final[0]%'";

			for( $i = 1; $i < $word_num; $i++ )
			{
				$sql .= " OR type LIKE '%$final[$i]%'";
			}
		}

		$type++;
	}
    }

    $result = mysql_query($sql);
    $row_count = mysql_num_rows($result);
    $time_end = time();

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

    $sql_new = $sql;
    $sql_new .= " LIMIT $start_now,$row_per_page";
    $result_new = mysql_query($sql_new);
    ?>
    <table id="big_table" cellspacing="0" cellpadding="0">
      <?php
        if( $myrow = mysql_fetch_array($result_new) )
        {
          $i = 0;
          do {
            $i++;
            ?>
            <tr>
              <td>
                <table class="small_table" cellspacing="0" cellpadding="0">
                  <tr>
                    <td>
                      <img class="pic" src="<?php echo $myrow['picture'];?>" height="100" width="100" alt="<?php echo $myrow['title'];?>">
                    </td>
                    <td>
                      <table class="show_table">
                        <tr>
                          <td>
                            <a href="<?php echo $myrow['url'];?>" target="_blank"><b><?php echo $myrow['title']?></b></a>
                          </td>
                          <tr>
                            <td>
                              <b>原料：</b><?php echo $myrow['material']; ?>
                            </td>
                          <tr>
                            <td>
                              <b>类型：<?php echo $myrow['type']; ?></b>
                            </td>
                          </tr>
                        </table>
                    </td>
                  </tr>
                </table>
              </td>
            </tr>
      <?php
      }while( $myrow = mysql_fetch_array($result_new) );
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
