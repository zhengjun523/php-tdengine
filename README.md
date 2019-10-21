# php-tdengine

#### 介绍
`php-tdengine`是一款[`TDengine`](https://www.taosdata.com/cn/documentation/)的操作脚本库，基于`php7`开发。

#### 基于
* [`Composer`](https://pkg.phpcomposer.com)
* [`TDengine`](https://www.taosdata.com)
* [`PHP >= 7.1`](https://www.php.net)

#### 软件架构
```
application             项目目录
├── composer.json       项目的Composer文件
├── README.en.md        README文档(EN)
├── README.md           README文档
├── src                 脚本开发目录
│   └── TDengine.php
├── test                测试目录
│   ├── dbs             测试用数据存放位置
│   │   └── demo.sql
│   └── TDengineTest.php
└── vendor              Composer资源目录
    └── ...
```

#### 安装教程
请查看对应的文档自行安装
* [`Composer 安装`](https://pkg.phpcomposer.com)
    > 安装完成后替换为`阿里源`:
    > `composer config -g repo.packagist composer https://mirrors.aliyun.com/composer/`

* [`TDengine 手册`](https://www.taosdata.com/cn/getting-started/#%E5%BF%AB%E9%80%9F%E4%B8%8A%E6%89%8B)
```
## 导入TDengine库
Shell> cd ./test/dbs
Shell> taos -h <IP> -P <PORT> -u <USER> -p <PASS> -s "source ./demo.sql"

## 运行测试脚本(CLI)
Shell> composer install
Shell> cd ./test
Shell> php TDengineTest.php
```


#### 使用说明

方法表

方法|说明|是否链式|返回值
--|--|--|--
getLastSql|获取最后生成的SQL语句,无论语句是否成功执行|否|`String`
table|设置需要执行的数据表|是|-
field|设置需要查询的字段|是|-
data|设置插入数据|是|-
where|设置查询等方法作用的条件|是|-
whereOr|设置查询等方法作用的OR条件|是|-
order|设置数据排序方式|是|-
limit|设置分页和输出量|是|-
drop|执行数据表删除|否|`Boolean`<br />影响行数`rowCount()`<br />错误代码`error()`<br />错误信息`getError()`
truncate|执行清空表|否|`Boolean`
query|执行SQl语句|否|`Boolean`<br />影响行数`rowCount`<br />查询返回`rows()`<br />错误代码`error()`<br />错误信息`getError()`
insert|执行数据插入|否|`Boolean`<br />影响行数`rowCount()`<br />错误代码`error()`<br />错误信息`getError()`
select|执行数据查询|否|`Boolean`<br />影响行数`rowCount`<br />查询返回`rows()`<br />错误代码`error()`<br />错误信息`getError()`
rows|获取查询的结果数据集合|否|`Array`
rowCount|获取数据数量|否|`Int32`
error|获取执行结果|否|`String`
getError|获取错误信息|否|`String`

示例
```php
<?php
require_once '../vendor/autoload.php';
use TDengine\TDengine;

$config = [
    'td_host'   => '127.0.0.1', // 地址
    'td_port'   => '6020', // 端口
    'td_user'   => 'root', // 用户
    'td_pass'   => 'root', // 密码
    'td_name'   => 'api_dev', // 库名
    'td_prefix' => 'kc_', // 表前缀
];
$td = new TDengine($config);

// 执行query方法
$result = $td->query('SHOW DATABASES');
 // 查看刚刚生成的SQL语句
$td->getLastSql(); // SHOW DATABASES
if ($result) {
    $td->rows(); // 返回数据列表
    $td->rowCount(); // 受影响行数
} else {
    $td->error(); // 错误代码
    $td->getError(); // 错误信息
}

// 执行select方法
$result = $td->table('s_history')
            ->field('ts, wd')
            ->where('ts', '>', '2019-09-04 19:23:00')
            ->where('ts', '<', '2019-09-04 19:24:00')
            ->order('ts', 'DESC')
            ->limit(0, 10)
            ->select();
$td->getlastsql(); 
// SELECT ts, wd FROM api_dev.kc_s_history WHERE  ts > '2019-09-04 19:23:00'  AND  ts < '2019-09-04 19:24:00'  ORDER BY ts DESC LIMIT 10
if ($result) {
    $td->rows(); // 返回数据列表
    $td->rowcount(); // 受影响行数
} else {
    $td->error(); // 错误代码
    $td->geterror(); // 错误信息
}

// 执行insert方法
$result = $td->table('s_history')->data([
    // 最恶心的东西就是这个时间戳
    // 1. 主键
    // 2. 新增数据时间不能小于表内已经存在的最大时间
    // 3. 0表示的是获取当前时间
    'ts' => '0',
    'wd' => 'Fuck the world!',
])->insert();
$td->getlastsql();
// INSERT INTO api_dev.kc_s_history(ts,wd) VALUES('0','Fuck the world!')
if ($result) {
    $td->rowcount(); // 受影响行数
} else {
    $td->error(); // 错误代码
    $td->geterror(); // 错误信息
}
```

#### 版本
* v 1.0
    * 基于TDengine的RESTful方式
    * 实现了对TDengine库的基本增删改查

#### 参与贡献
* [`Kisschou`](http://www.kisschou.com)

#### 版权信息
Kisschou&copy;2019.China
