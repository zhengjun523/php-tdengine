######################
## taos的测试数据库 ##
######################
## 删除数据库
drop database if exists api_dev

## 新建数据库
create database if not exists api_dev

use api_dev

## 查询记录
create table if not exists kc_s_history (ts timestamp, wd binary(300))
# import into kc_s_history file search_history.csv
insert into kc_s_history values('2010-07-23 11:01:02.000', 'https://www.google.com')
insert into kc_s_history values(now, 'php');
insert into kc_s_history values(now, 'Taos的RESTful');
insert into kc_s_history values(now+1a, 'tdengine是否支持中文');
insert into kc_s_history values(now+1s, 'taos数据类型');
insert into kc_s_history values(now+1m, 'flutter');
insert into kc_s_history values(now+1h, 'dart');

## 访问记录
create table if not exists kc_s_action (ts timestamp, action tinyint(1), action_url binary(300))
insert into kc_s_action values('2010-07-23 11:01:02.000', 1, 'https://www.google.com')
insert into kc_s_action values(now, 2, 'http://www.kisschou.com');
insert into kc_s_action values(now+1a, 1, 'http://www.api.nodev');
insert into kc_s_action values(now+1s, 2, 'https://www.baidu.com');
insert into kc_s_action values(now+1m, 1, 'https://flutterchina.club/get-started/codelab/');
insert into kc_s_action values(now+1h, 2, 'https://www.taosdata.com/cn/documentation/');
