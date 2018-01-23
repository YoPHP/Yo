<?php

namespace YoPHP\ORM;

use YoPHP\Config;
use Exception;
use YoPHP\Invoke;
use YoPHP\Log;
use YoPHP\Container;
use PDOException;
use PDO;

/**
 * 数据库查询
 * @author YoPHP <admin@YoPHP.org>
 */
class Query {

    /** @var PDO */
    protected $instance = null;

    /**
     *
     * @var Database
     */
    protected $database;

    /**
     *
     * @var Config
     */
    protected $config;

    /**
     *
     * @var Invoke
     */
    protected $invoke;

    /** @var Log */
    protected $log;

    /**
     * 标识符
     *  @var string
     */
    protected $identifier = '';

    /**
     *  @var array
     */
    protected $_value = null;

    /**
     * 表前缀
     * @var string
     */
    protected $prefix = '';

    public function __construct(Database $database, Config $config, Invoke $invoke, Log $log) {
        $this->database = $database;
        $this->config = $config;
        $this->invoke = $invoke;
        $this->log = $log;
        !$this->instance && $this->connect();
    }

    /**
     * 连接数据库
     * @param string $dsn 数据源名称
     * @param string $username 账号
     * @param string $password 密码
     * @param array $options  具体驱动的连接选项
     * @param string $identifier 标识符
     * @return PDO
     */
    public function connect(string $dsn = null, string $username = null, string $password = null, array $options = [], $prefix = null, $identifier = null) {
        $this->identifier = $identifier ?: ($this->config->get('db.identifier') ?: '');
        $this->prefix = $prefix ?: ($this->config->get('db.prefix') ?: '');
        return $this->instance || ($this->instance = $this->database->connect($dsn, $username, $password, $options));
    }

    /**
     * 启动事务
     * @return bool
     */
    public function beginTransaction(): bool {
        if ($this->instance->beginTransaction()) {
            $this->config->get('db.log') && $this->log->sql('[DB]启动事务');
            return true;
        }
        return false;
    }

    /**
     * 提交事务
     * @return bool
     */
    public function commit(): bool {
        if ($this->instance->commit()) {
            $this->config->get('db.log') && $this->log->sql('[DB]提交事务');
            return true;
        }
        return false;
    }

    /**
     * 回滚事务
     * @return bool
     */
    public function rollBack(): bool {
        if ($this->instance->rollBack()) {
            $this->config->get('db.log') && $this->log->sql('[DB]回滚事务');
            return true;
        }
        return false;
    }

    /**
     * 检查是否在事务内
     * @return bool
     */
    public function inTransaction(): bool {
        return $this->instance->inTransaction();
    }

    /**
     * 返回最后插入行的ID或序列值
     * @return string
     */
    public function lastInsertId() {
        return $this->instance->lastInsertId();
    }

    /**
     * 查询SQL语句
     * @param string $statement SQL语句
     * @param string $parameters 绑定的参数 
     * @return \PDOStatement|false
     */
    public function query(string $statement, array $parameters = []) {
        $sth = $this->prepareExecute($statement, $parameters);
        return $sth ?: false;
    }

    /**
     * 执行的SQL语句 DELETE、INSERT、UPDATE
     * 返回受影响的行数
     * @param string $statement SQL语句
     * @param string $parameters 绑定的参数 
     * @return int
     */
    public function exec(string $statement, array $parameters = []): int {
        //自动启动事务
        !$this->inTransaction() && $this->beginTransaction();
        $sth = $this->prepareExecute($statement, $parameters);
        return $sth ? $sth->rowCount() : 0;
    }

    /**
     * 执行语句并返回语句对象
     * @param string $statement SQL语句
     * SQL语句可以（:name）或问号（?）做参数标记
     * 有效防止SQL注入攻击
     * @param string $parameters 绑定的参数 
     * @return \PDOStatement|false
     * @throws PDOException
     * @throws Exception
     */
    public function prepareExecute(string $statement, array $parameters = []) {

        try {
            $start = microtime(true);
            $sth = $this->instance->prepare($statement);
        } catch (Exception $e) {
            $this->inTransaction() && $this->rollBack();
            throw $e;
        }
        if ($sth->execute($parameters) !== false) {
            //$sth->debugDumpParams();
            $this->config->get('db.log') && $this->log->sql('[SQL]' . number_format(microtime(true) - $start, 6) . ' ' . $statement);
            return $sth;
        }
        $this->inTransaction() && $this->rollBack();
        if (DEBUG) {
            die('SQL Failed:' . $statement);
        }
        return false;
    }

    /**
     * 创建查询实例
     * @param string $from 表
     * @return Statement\Select
     */
    public function select($from = null) {
        return Container::create(Statement\Select::class)->query($this)->from($this->parsefrom($from));
    }

    /**
     * 创建新增实例
     * @param array $data 数据
     * @param string $from 表
     * @return Statement\Insert
     */
    public function insert(array $data = [], $from = null) {
        if (empty($data)) {
            $data = $this->toArray();
            $this->_data = [];
        }
        return Container::create(Statement\Insert::class)->query($this)->from($this->parsefrom($from))->data($data);
    }

    /**
     * 批量创建新增实例
     * @param array $datas 数据
     * @param string $from 表
     * @return Statement\Insert
     */
    public function inserts(array $datas, $from = null) {
        return Container::create(Statement\Insert::class)->query($this)->from($this->parsefrom($from))->datas($datas);
    }

    /**
     * 创建更新实例
     * @param array $data 数据
     * @param string $from 表
     * @return Statement\Update
     */
    public function update(array $data = [], $from = null) {
        if (empty($data)) {
            $data = $this->toArray();
            $this->_data = [];
        }
        return Container::create(Statement\Update::class)->query($this)->from($this->parsefrom($from))->set($data);
    }

    /**
     * 创建删除实例
     * @param string $from 表
     * @return Statement\Delete
     */
    public function delete($from = null) {
        return Container::create(Statement\Delete::class)->query($this)->from($this->parsefrom($from));
    }

    /**
     * 引用用于查询的字符串
     * 返回在理论上安全传入SQL语句的引用字符串
     * @param string $string
     * @return string
     */
    public function quote(string $string): string {
        return $this->instance->quote($string);
    }

    /**
     * 处理字段和表名转义标识符
     * PDO不提供此功能
     * @param string|array $string
     * @return string
     */
    public function identifier($string) {
        if (!is_array($string)) {
            $string = preg_split('/\s*,\s*/', trim($string), -1, PREG_SPLIT_NO_EMPTY);
        }
        $strings = [];
        foreach ($string as $value) {
            if (preg_match('/(.*) AS (.*)/i', $value, $matches)) {
                $strings[] = $this->name($matches[1]) . ' AS ' . $this->name($matches[2]);
            } else {
                $strings[] = $this->name($value);
            }
        }
        return implode(',', $strings);
    }

    /**
     * 转义字段
     * @param string $string
     * @return string
     */
    public function name($string): string {
        if (preg_match('/^[A-Za-z\-\_]+$/', $string) && stristr($string, '*') === false) {
            $string = $this->identifier && strlen($this->identifier) === 2 ? $this->identifier[0] . trim($string) . $this->identifier[1] : trim($string);
        } else {
            $string = trim($string);
        }
        return $string;
    }

    /**
     * 解析表
     * @param string  $form 表名
     * @return string
     */
    protected function parsefrom($form) {
        if (empty($form)) {
            return null;
        }
        return $this->prefix . strtolower(trim(preg_replace("/[A-Z]/", "_\\0", $form), '_'));
    }

    public function toArray() {
        return $this->_value;
    }

    public function __set($name, $value) {
        $this->_value[$name] = $value;
    }

    public function __get($name) {
        return $this->_value[$name] ?? null;
    }

    public function __destruct() {
//        if ($e = error_get_last()) {
//            print_r($e);exit;
//            switch ($e['type']) {
//                case E_ERROR:
//                case E_PARSE:
//                case E_CORE_ERROR:
//                case E_COMPILE_ERROR:
//                case E_USER_ERROR:
//                    $this->inTransaction() && $this->rollBack();
//                    break;
//            }
//        }
        //自动提交事务
        $this->inTransaction() && $this->commit();
    }

}
