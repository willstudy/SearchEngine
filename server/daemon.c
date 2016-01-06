#include <stdio.h>
#include <stdlib.h>
#include <string.h>
#include <signal.h>
#include <sys/socket.h>
#include <netinet/in.h>
#include <unistd.h>
#include <sys/types.h>
#include "daemon.h"

int main (int argc, char **argv)
{
  int sockfd, client_sockfd;
  struct sockaddr_in host_addr, client_addr;

  //init_daemon();
#if 0
  FILE *fp;
  if( (fp = fopen("log.file", "a")) == NULL )
  {
    fprintf( stdout, "%s : file %s open failed !\n", __FUNCTION__, "log.file" );
    exit(1);
  }
#endif
  if( ( sockfd = createConnect() ) == -1 )
  {
     fprintf( stdout, "%s create connect failed\n", __FUNCTION__ );
     return 0;
  }

  fprintf( stdout, "create connect successfully, sockfd = %d\n", sockfd );

  signal(SIGCHLD, SIG_IGN);

  while(1)
  {
    pid_t pid;
    int size;

    if((client_sockfd = accept(sockfd, (struct sockaddr *)&client_addr, &size))==-1)
      DIE("accepting client connection");

    fprintf(stdout, "server: accepting a client from %s port %d\n"
	      , inet_ntoa (client_addr.sin_addr)
	      , ntohs (client_addr.sin_port));

    if( ( pid = fork() ) == 0 )
    {
       close( sockfd );
       handle( client_sockfd );
       close( client_sockfd );
    }
  }
  return 0;
}
