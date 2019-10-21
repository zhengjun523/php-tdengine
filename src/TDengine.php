<?php
/**
 * 一款TDengine的操作脚本库，基于php7开发
 *
 * @author Kisschou <kiss_chou@126.com>
 **/
namespace TDengine;

class TDengine
{
    private $config = [
        'td_host'   => '127.0.0.1', // 地址
        'td_port'   => '6020', // 端口
        'td_user'   => 'root', // 用户
        'td_pass'   => 'root', // 密码
        'td_name'   => 'api_dev', // 库名
        'td_prefix' => 'kc_', // 表前缀
    ];

    private $_host   = '';
    private $_port   = '';
    private $_user   = '';
    private $_pass   = '';
    private $_name   = '';
    private $_prefix = '';

    private $_table        = ''; // 操作的数据表
    private $_field        = '*'; // 查询字段
    private $_where        = ''; // 查询条件
    private $_data         = ''; // 插入数据
    private $_order        = ''; // 排序
    private $_limit        = ''; // 查询行数
    private $_sql          = ''; // 最后生成的SQL语句
    private $_rowsCount    = 0; // 受影响行数
    private $_rows         = []; // 查询结果集合
    private $_error        = true; // true/false 成功/失败
    private $_errorMessage = '';

    public function __construct($config = [])
    {
        if (is_array($config) && !empty($config)) {
            $this->config = $config;
        }

        $this->_host   = $this->config['td_host'];
        $this->_port   = $this->config['td_port'];
        $this->_user   = $this->config['td_user'];
        $this->_pass   = $this->config['td_pass'];
        $this->_name   = $this->config['td_name'];
        $this->_prefix = $this->_name . '.' . $this->config['td_prefix']; // 数据请求都得加上表名
    }

    public function __get($key)
    {
        return isset($this->config[$key]) ? $this->config[$key] : null;
    }

    public function __set($key, $value)
    {
        $this->config[$key] = $value;
    }

    /**
     * 构建请求地址
     *
     * @return
     */
    private function _buildUrl()
    {
        return sprintf('%s:%s/rest/sql', $this->_host, $this->_port);
    }

    /**
     * 构建请求头部
     *
     * @return
     */
    private function _buildHeader()
    {
        $header   = [];
        $header[] = 'Authorization: Basic ' . base64_encode(sprintf('%s:%s', $this->_user, $this->_pass));
        return $header;
    }

    /**
     * 发送请求
     *
     * @param $sql 请求的SQL语句
     *
     * @return Array
     */
    private function _curl($sql = '')
    {
        // 初始化
        $curl = curl_init();
        // 设置抓取的url
        curl_setopt($curl, CURLOPT_URL, $this->_buildUrl());
        // 设置头文件的信息作为数据流输出
        curl_setopt($curl, CURLOPT_HEADER, 0);
        // 设置获取的信息以文件流的形式返回，而不是直接输出。
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        // 设置post方式提交
        curl_setopt($curl, CURLOPT_POST, 1);
        // 设置发送的头部信息
        curl_setopt($curl, CURLOPT_HTTPHEADER, $this->_buildHeader());
        // post提交的数据
        curl_setopt($curl, CURLOPT_POSTFIELDS, empty($sql) ? $this->_sql : $sql);
        // 执行命令
        $result = curl_exec($curl);
        // 关闭URL请求
        curl_close($curl);
        // 显示获得的数据
        return $this->_buildResult($result);
    }

    /**
     * 处理结果
     *
     * @param $result 返回结果(json)
     *
     * @return
     */
    private function _buildResult($result)
    {
        $result = json_decode($result, true);
        // 执行成功
        if ($result['status'] === 'succ') {
            $head             = $result['head']; // 表的定义
            $this->_rowsCount = $result['rows']; // 受影响行数
            // 只返回受影响行数，插入、创建】
            if ($head === 'affected_rows') {
                return true;
            }

            // 返回的数据
            $data = $result['data'];
            // 解析成 key:head => value:data
            foreach ($data as $k => $v) {
                foreach ($v as $kk => $vv) {
                    unset($v[$kk]);
                    $v[$head[$kk]] = $vv;
                }
                $data[$k] = $v;
            }
            $this->_rows = $data;
            unset($head); // 销毁
            unset($data); // 销毁
            return true;
        } else {
            // 错误代码
            $this->_error = $result['code'];
            // 错误信息
            $this->_errorMessage = $result['desc'];
            return false;
        }
    }

    /**
     * 获取最后生成的SQL语句
     *
     * @return $this
     */
    public function getLastSql()
    {
        return $this->_sql;
    }

    /**
     * 设置需要执行的数据表
     *
     * @param $table 表名
     *
     * @return $this
     */
    public function table($table)
    {
        $this->_table = $this->_prefix . $table;
        return $this;
    }

    /**
     * 设置需要查询的字段
     *
     * @param $field 需要查询的字段
     *
     * @return
     */
    public function field($field = '*')
    {
        $this->_field = $field;
        return $this;
    }

    /**
     * 设置插入数据
     *
     * @param $data 待插入数据
     *
     * @return
     */
    public function data($data)
    {
        $keys = array_keys($data);
        $vals = array_values($data);
        foreach ($vals as $k => $v) {
            $vals[$k] = "'" . $v . "'";
        }
        unset($data);
        $this->_data = [$keys, $vals];
        return $this;
    }

    /**
     * 设置查询等方法作用的AND条件
     *
     * @param $key 字段名
     * @param $symbol 比较符
     * @param $val 值
     *
     * @return
     */
    public function where($key, $symbol, $val)
    {
        $this->_where .= sprintf("%s %s %s '%s' ", empty($this->_where) ? 'WHERE ' : ' AND ', $key, $symbol, $val);
        return $this;
    }

    /**
     * 设置查询等方法作用的OR条件
     *
     * @param $key 字段名
     * @param $symbol 比较符
     * @param $val 值
     *
     * @return
     */
    public function whereOr($key, $symbol, $val)
    {
        $this->_where .= sprintf("%s %s %s '%s' ", empty($this->_where) ? 'WHERE ' : ' OR ', $key, $symbol, $val);
        return $this;
    }

    /**
     * 设置数据排序方式
     *
     * @param $key 字段
     * @param $sort 排序方式(AES|DESC)
     *
     * @return
     */
    public function order($key, $sort = 'DESC')
    {
        $this->_order = sprintf('ORDER BY %s %s', $key, $sort);
        return $this;
    }

    /**
     * 设置分页和输出量
     *
     * @param $offset 指定从第几条开始输出
     * @param $limit 输出条数
     *
     * @return
     */
    public function limit($offset = 0, $limit = 20)
    {
        $this->_limit = 'LIMIT ' . $limit;
        if ($offset > 0) {
            $this->_limit .= ' OFFSET ' . $offset;
        }
        return $this;
    }

    /**
     * 执行数据表删除
     *
     * @return
     */
    public function drop()
    {
        $this->_sql = sprintf('DROP TABLE IF EXISTS %s;', $this->_table);
        return $this->_curl();
    }

    /**
     * 执行清空表
     *
     * @return
     */
    public function truncate()
    {
        // 因为TDengine没有数据表清空和数据删除
        // 所以清空表执行步骤如下
        // 1. 获取表结构并生成新建表语句
        // 2. 删除数据表
        // 3. 执行新建表语句
        $this->_sql = sprintf('DESCRIBE %s', $this->_table);
        $result     = $this->_curl();
        // 获取表结构成功
        if ($result) {
            $tableDescribe = $this->_rows;
            $this->_sql    = sprintf('DROP TABLE IF EXISTS %s', $this->_table);
            $result        = $this->_curl();
            // 删除表成功
            if ($result) {
                // 构建建表语句
                $this->_sql = 'CREATE TABLE %s(%s)';
                foreach ($tableDescribe as $k => $v) {
                    unset($tableDescribe[$k]);
                    $tableDescribe[$k] = sprintf('%s %s(%s)', $v['Field'], $v['Type'], $v['Length']);
                }
                $this->_sql = sprintf($this->_sql, $this->_table, implode(',', $tableDescribe));
                $result     = $this->_curl();
            }
        }
        return $result;
    }

    /**
     * 执行SQl语句
     *
     * @param $sql SQL语句
     *
     * @return
     */
    public function query($sql)
    {
        $this->_sql = $sql;
        return $this->_curl();
    }

    /**
     * 执行数据插入
     *
     * @return
     */
    public function insert()
    {
        $this->_sql        = "INSERT INTO %s(%s) VALUES(%s)";
        list($keys, $vals) = $this->_data;
        $this->_sql        = sprintf($this->_sql, $this->_table, implode(',', $keys), implode(',', $vals));
        return $this->_curl();
    }

    /**
     * 执行数据查询
     *
     * @return
     */
    public function select()
    {
        $this->_sql = sprintf(
            "SELECT %s FROM %s %s %s %s",
            $this->_field ?: '*',
            $this->_table,
            $this->_where,
            $this->_order,
            $this->_limit
        );
        return $this->_curl();
    }

    /**
     * 获取查询的结果数据集合
     *
     * @return
     */
    public function rows()
    {
        return $this->_rows;
    }

    /**
     * 获取数据数量
     *
     * @return
     */
    public function rowCount()
    {
        return intval($this->_rowsCount);
    }

    /**
     * 获取执行结果
     *
     * @return boolean
     */
    public function error()
    {
        return $this->_error;
    }

    /**
     * 获取错误信息
     *
     * @return string
     */
    public function getError()
    {
        return $this->_errorMessage;
    }
}
