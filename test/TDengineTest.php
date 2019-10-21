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

// query
$result = $td->query('SHOW DATABASES');
echo '###############################' . PHP_EOL;
echo '## RunSQL: ' . $td->getLastSql() . PHP_EOL;
echo '###############################' . PHP_EOL;
if ($result) {
    // print_r($td->rows());
    echo json_encode($td->rows()) . PHP_EOL;
    echo '命中行数: ' . $td->rowCount() . PHP_EOL;
} else {
    echo sprintf('Error#%s %s', $td->error(), $td->getError()) . PHP_EOL;
}

// select
$result = $td->table('s_history')->field('ts, wd')->where('ts', '>', '2019-09-04 19:23:00')->where('ts', '<', '2019-09-04 19:24:00')->order('ts', 'DESC')->limit(0, 10)->select();
echo '###############################' . PHP_EOL;
echo '## RunSQL: ' . $td->getLastSql() . PHP_EOL;
echo '###############################' . PHP_EOL;
if ($result) {
    // print_r($td->rows());
    echo json_encode($td->rows()) . PHP_EOL;
    echo '命中行数: ' . $td->rowCount() . PHP_EOL;
} else {
    echo sprintf('Error#%s %s', $td->error(), $td->getError()) . PHP_EOL;
}

// insert
$result = $td->table('s_history')->data([
    // 'ts' => '2019-09-04 19:23:01',
    // 最恶心的东西就是这个时间戳
    // 1. 主键
    // 2. 新增数据时间不能小于表内已经存在的最大时间
    // 3. 0表示的是获取当前时间
    'ts' => '0',
    'wd' => 'Fuck the world!',
])->insert();
echo '###############################' . PHP_EOL;
echo '## RunSQL: ' . $td->getLastSql() . PHP_EOL;
echo '###############################' . PHP_EOL;
if ($result) {
    echo '命中行数: ' . $td->rowCount() . PHP_EOL;
} else {
    echo sprintf('Error#%s %s', $td->error(), $td->getError()) . PHP_EOL;
}
