/*
 * 表的 中文名 与 英文名 的转换表
 */
create table ch2eng(
  chinese varchar(128) unique,
  english varchar(128) unique
);

insert into ch2eng(chinese, english) values('简单难度','simple_level');
insert into ch2eng(chinese, english) values('热菜','hot_dish');

create table url2id(
  id int(11) unsigned not null PRIMARY KEY,
  url varchar(128) not null unique
);

/*
 * 创建相应的英文表名(为了编码兼容性)
 * id        : 主键、索引	int unsigned not null 
 * url       : 链接      	varchar(128) not null unique
 * title     : 菜名      	varchar(128) not null
 * picture   : 菜的图片   	varchar(128) not null unique
 * introduce : 菜的介绍   	varchar(2048)
 * material  : 食材      	varchar(1024)
 * type      : 类型      	varchar(1024)
 * step      : 步骤      	varchar(2048)
 * tip       : 小窍门    	varchar(1024)
 * weight    : 权重      	int unsigned not null
 */
 create table dish(
   id int(11) unsigned not null PRIMARY KEY,
   url varchar(128) not null unique,
   title varchar(128) not null,
   picture varchar(128) not null,
   introduce varchar(2048),
   material varchar(1024),
   type varchar(1024),
   step varchar(2048),
   tip varchar(1024),
   weight int(10) unsigned not null
 );

create table simple_level(
  url varchar(128) not null primary key,
  title varchar(128) not null unique,
  picture varchar(128) not null unique,
  introduce text,
  material text,
  type text,
  step text,
  tip text,
  weight int unsigned not null
);

create table hot_dish(
  url varchar(128) not null primary key,
  title varchar(128) not null unique,
  picture varchar(128) not null unique,
  introduce text,
  material text,
  type text,
  step text,
  tip text,
  weight int unsigned not null
);

