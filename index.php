<!DOCTYPE html>
<html>
<head lang="en">
    <meta charset="UTF-8">
    <title>基于语义的搜索引擎</title>
    <link rel="stylesheet" type="text/css" href="./css/style.css">
</head>
<body>
<div id="container">

    <div id="heading">

        <h1>语 义 搜 索</h1>

    </div>

    <div id="content">
      <form action="./server.php" method="post">

        <input class="search_text" name="search_text" type="text">
        <input class="search_button" name="search_button" type="submit" value="搜 索" >

      </form>
    </div>

    <div id="footing">

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
                  "<div style='text-align: center'><font color=#0000FF style='font-size:9pt;font-family:fantasy'> ",
                  today.getFullYear(),"年 ",
                  today.getMonth()+1,"月 ",
                  today.getDate(),"日 ",
                  d[today.getDay()+1],
                  "</font></div>" );
      </script>
      <div style='text-align: center;margin-top:50px'>
        <font color=grey style='font-size:8pt;'>
          Copyright by USTC 2015 - 2016
        </font>
      </div>
    </div>

</div>
</body>
</html>
