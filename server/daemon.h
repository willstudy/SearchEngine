#ifndef DAEMON_H
#define DAEMON_H

#include <stdio.h>
#include <stdlib.h>
#include <string.h>
#include <signal.h>
#include <sys/socket.h>
#include <netinet/in.h>
#include <unistd.h>
#include <assert.h>
#include <sys/types.h>

#include "http-tree.h"
#include "parse.h"

#define HOST_PORT 8888
#define DEBUG 1
#define BUF_SIZE 8192
#define BUF_REQ 1024
#define LISTENQ 1024

#define BUF_URL 128
#define BUF_TITLE 128
#define BUF_INTRO 2048
#define BUF_PIC 512
#define BUF_WORD 64
#define BUF_STEP 2048
#define BUF_TIP 512

#define MAX_COUNT 20      // 每道菜：类型或者食材的个数

#define DIE(msg)						               \
do{								                         \
  fprintf (stdout, "Error: %s:%d: %s\n",	 \
	         __FILE__, __LINE__, msg);	     \
          exit (0);							           \
}while(0)							                     \

struct kv{
  char *key;
  char *value;
};

typedef struct result
{
  unsigned int weight;                     // 页面权重
  char url[BUF_URL];                       // 页面url
  char title[BUF_TITLE];                   // 菜名
  char introduce[BUF_INTRO];               // 菜的介绍
  char picture[BUF_PIC];                   // 菜的图片
  char material[MAX_COUNT][BUF_WORD];      // 食材
  char type[MAX_COUNT][BUF_WORD];          // 菜的类型
  char step[BUF_STEP];                     // 做法步骤
  char tip[BUF_TIP];                       // 小窍门
} Result, * PResult;

static char *fileOk = "HTTP/1.1 200 OK\r\n\r\n";
static char *badRequest = "HTTP/1.1 400 bad request\r\n\r\n";
static struct kv temp[16];

/*
 * 将服务器变成守护进程，不改变当前目录，标准输入、输出、错误输出重定向到/dev/null
 * 当出错时，把出错信息写到log.file中，然后退出
 */
void init_daemon( FILE *fp )
{
  if( daemon(1, 0) )
  {
    fprintf(fp, "%s: daemon failed!\n", __FUNCTION__ );
    fclose( fp );
    exit(1);
  }
  return ;
}

static char *kv_lookup(char *key)
{
  int i = 0;

  while( i < 16 && temp[i].key )
  {
    if( strcmp(temp[i].key, key) == 0 )
      return temp[i].value;
    // a dirty hack
    if( temp[i].key[0]=='s' && key[0]=='s' )
      return temp[i].value;

    i++;
  }

  return 0;
}

static void getValues (Http_t tree)
{
  assert (tree);
  char *body = tree->body;
  assert (body);
  int len = strlen(body);

  char *start = body;
  int num = 0;

  while( start < body+len )
  {
    char *end = strchr(start, '=');
    *end = '\0';
    temp[num].key = start;
    start = end+1;
    end = strchr(start, '&');  // user=liuchang&passwd=12345
    if(end == 0){
      temp[num].value = start;
      break;
    }
    *end = '\0';
    temp[num].value = start;
    start = end+1;
    num++;
  }
  return;
}

static void kv_print ()
{
  int i=0;

  while (i < 16 && temp[i].key )
  {
    fprintf (stderr, "kv[%d]=%s, %s\n"
	     , i
	     , temp[i].key
	     , temp[i].value);
    i++;
  }
  return;
}

/*
 * 根据用户点击地搜索结果，相对应的菜的url，更新数据库中每条记录的权重
 */
void updateWeight( char * url )
{
   fprintf( stdout, "updateWeight called\n" );
}

/*
 * 得到用户的输入，放到buf中
 */
void getSearchText( int sockfd, char * buf, char * uri_str )
{
  Http_t tree;

  ReqLine_t reqline = ReqLine_new(REQ_KIND_GET
				      , uri_str
				      , HTTP_ONE_ONE);
  tree = Parse_parse(sockfd, 0);
  tree->reqLine = reqline;

  getValues(tree);
  kv_print();
  strcpy( buf, kv_lookup("search_text") );

  free( reqline );
  free( tree );

  fprintf(stdout, "getSearchText called \n");
}

/*
 * req即用户的输入，以'\0'结尾。基于语义的查询结果放在Result结构体当中。
 */
void semanticSearch( char * req, Result * result )
{
   fprintf( stdout, "semanticSearch called\n" );
}

/*
 * 将数据库返回的结果，发送给浏览器
 */
void responseRequest( int sockfd, Result * result )
{
   fprintf( stdout, "responseRequest called\n" );
   write( sockfd, fileOk, strlen(fileOk) );
}

void handle( int sockfd )
{
   Result result;
   char buf[BUF_REQ];

   memset( &result, '\0', sizeof(Result) );
   memset( buf, '\0', BUF_REQ );

   ReqLine_t reqline = Parse_parse(sockfd, 1);   // 解析请求部分

   if( reqline == NULL )
   {
      fprintf( stdout, "%s : parse reqline error!\n", __FUNCTION__ );
      close( sockfd );
      free( reqline );
      return ;
   }

   if (DEBUG)      // uri 代表用户请求的数据
      fprintf( stdout, "%s : parse reqline:[%s]\n",
               __FUNCTION__, reqline->uri );

   switch( reqline->kind )             // 区分用户点击查询搜索，还是点击搜索结果
   {
     case REQ_KIND_GET:                // 用户点击搜索结果，使用GET方法
     {
        updateWeight( reqline->uri );  // 根据被点击页面的url，相应的对数据库数据进行加权。
        write( sockfd, fileOk, strlen(fileOk) );
        break;
     }
     case REQ_KIND_POST:              // 用户点击搜索框，则基于语义进行查询
     {
        getSearchText( sockfd, buf, reqline->uri );
        semanticSearch( buf,  &result );       // 基于语义查询数据库
        responseRequest( sockfd, &result );    // 返回给浏览器
        break;
     }
     case REQ_KIND_HEAD:
     {
        fprintf(stdout, "%s : we not use HEAD menthod \n", __FUNCTION__ );
        write( sockfd, badRequest, strlen(badRequest) );
        break;
      }
     default:
     {
        fprintf(stdout, "%s an error was encountered \n", __FUNCTION__ );
        write( sockfd, badRequest, strlen(badRequest) );
        break;
      }
   }
  free(reqline);
  return ;
}

int createConnect()
{
  struct sockaddr_in host_addr;
  int sockfd;
  int host_port;
  int yes;

  host_port = HOST_PORT;

  if ((sockfd = socket (PF_INET, SOCK_STREAM, 0))==-1)
  {
    DIE("creating a socket");
    return -1;
  }

  if (setsockopt(sockfd, SOL_SOCKET, SO_REUSEADDR, &yes, sizeof(int)) == -1)
  {
    DIE("setting socket option SO_REUSEADDR");
    return -1;
  }

  memset(&(host_addr), '\0', sizeof(host_addr)); // zero off the structure
  host_addr.sin_family = AF_INET;
  host_addr.sin_port = htons(host_port);
  host_addr.sin_addr.s_addr = htonl(INADDR_ANY);

  if (bind(sockfd, (struct sockaddr *)&host_addr
	   , sizeof(struct sockaddr)) == -1)
  {
    DIE("binding to socket");
    return -1;
  }

  if (listen(sockfd, LISTENQ) == -1)
  {
    DIE("listening on socket");
    return -1;
  }

  return sockfd;
}

#endif
