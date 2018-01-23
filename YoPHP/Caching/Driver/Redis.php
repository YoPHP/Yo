<?php

namespace YoPHP\Caching\Driver;

use YoPHP\Caching\Driver;

/**
 * Redis缓存驱动
 * @author YoPHP <admin@YoPHP.org>
 */
class Redis extends Driver {

    /**
     * @var void 
     */
    private $handler = null;

    /**
     * @var array 
     */
    protected $configBase = [
        'host' => '127.0.0.1',
        'port' => 6379,
        'password' => '',
        'select' => 0,
        'timeout' => 0,
        'expire' => 0,
        'persistent' => false,
        'prefix' => ''
    ];

    /**
     * 检测是否可用
     * @return bool
     */
    public function enabled(): bool {
        if (!extension_loaded('Redis')) {
            return false;
        }
        $this->configBase += Config::get('redis', []);
        $func = $this->configBase['persistent'] ? 'pconnect' : 'connect';
        $this->handler = new \Redis;
        $this->handler->$func($this->configBase['host'], $this->configBase['port'], $this->configBase['timeout']);
        !empty($this->configBase['password']) && $this->handler->auth($this->configBase['password']);
        if (0 != $this->configBase['select']) {
            $this->handler->select($this->configBase['select']);
        }
        return true;
    }

    /**
     * 判断缓存
     * @access public
     * @param string $name 缓存变量名
     * @return bool
     */
    public function has($name): bool {
        return $this->handler->get($this->filename($name)) ? true : false;
    }

    /**
     * 读取缓存
     * @access public
     * @param string $name 缓存变量名
     * @param mixed  $default 默认值
     * @return mixed
     */
    public function get($name, $default = null) {
        $value = $this->handler->get($this->filename($name));
        if (is_null($value)) {
            return $default;
        }
        return $value;
    }

    /**
     * 写入缓存
     * @access public
     * @param string            $name 缓存变量名
     * @param mixed             $value  存储数据
     * @param int|  有效时间（秒）
     * @return bool
     */
    public function set($name, $value, $expire = null) {
        if (is_null($expire)) {
            $expire = $this->configBase['expire'];
        }
        $key = $this->filename($name);
        return is_int($expire) && $expire ? $this->handler->setex($key, $expire, $value) : $this->handler->set($key, $value);
    }

    /**
     * 自增缓存（针对数值缓存）
     * @access public
     * @param string    $name 缓存变量名
     * @param int       $step 步长
     * @return false|int
     */
    public function inc($name, $step = 1) {
        return $this->handler->incrby($this->filename($name), intval($step));
    }

    /**
     * 自减缓存（针对数值缓存）
     * @access public
     * @param string    $name 缓存变量名
     * @param int       $step 步长
     * @return false|int
     */
    public function dec($name, $step = 1) {
        return $this->handler->decrby($this->filename($name), intval($step));
    }

    /**
     * 删除缓存
     * @access public
     * @param string $name 缓存变量名
     * @return bool
     */
    public function rm($name) {
        $filename = $this->filename($name);
        return boolval(!$this->handler->get($filename) || $this->handler->delete($filename));
    }

    /**
     * 清除缓存
     * @access public
     * @return bool
     */
    public function clear() {
        return $this->handler->flushDB();
    }

}
