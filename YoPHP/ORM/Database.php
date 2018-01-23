<?php

namespace YoPHP\ORM;

use YoPHP\Base\Config;
use PDO;

class Database {

    /** @var array */
    private static $pdo = [];

    /**
     *
     * @var Config
     */
    protected $config;

    public function __construct(Config $config) {
        $this->config = $config;
    }

    /**
     * 连接
     * @param string $dsn 数据源名称
     * @param string $username 账号
     * @param string $password 密码
     * @param array $options  具体驱动的连接选项
     * @param string $identifier 标识符
     * @return PDO
     * @throws \PDOException
     * @throws Exception
     */
    public function connect(string $dsn = null, string $username = null, string $password = null, array $options = []) {
        if (empty($dsn)) {
            $config = $this->config->get('db');
            $dsn = $config['dsn'] ?? null;
            $username = $config['username'] ?? null;
            $password = $config['password'] ?? null;
            $options = $config['options'] ?? null;
        }
        $dsnsha1 = sha1($dsn);
        if (!isset(self::$pdo[$dsnsha1])) {
            try {
                self::$pdo[$dsnsha1] = new PDO($dsn, $username, $password, $options + [PDO::ATTR_PERSISTENT => true, PDO::ATTR_EMULATE_PREPARES => false, PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]);
            } catch (Exception $e) {
                throw $e;
            }
        }
        return self::$pdo[$dsnsha1];
    }

}
