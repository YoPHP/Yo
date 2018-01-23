<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace YoPHP;

use YoPHP\Config;
use YoPHP\Caching\Driver;

/**
 * 缓存类
 * @author YoPHP <admin@YoPHP.org>
 */
class Caching {

    protected static $instance = [];

    /**
     * @var void 
     */
    public $handler = null;

    /**
     * 初始连接缓存
     * @param string $driver 驱动
     * @return $this
     */
    public static function init($driver = null) {
        is_null($driver) && ($driver = Config::get('caching.driver', 'File'));
        if (empty(self::$instance[$driver])) {
            $class = false !== strpos($driver, '\\') ? $driver : 'YoPHP\\Caching\\Driver\\' . ucwords($driver);
            $handler = Container::get($class);
            if (!($handler instanceof Driver) || !$handler->enabled()) {
                throw new Exception('高速缓存启动失败:' . $class);
            }
            self::$instance[$driver] = new self();
            self::$instance[$driver]->handler = $handler;
        }
        return self::$instance[$driver];
    }

    /**
     * 判断缓存是否存在
     * @access public
     * @param string $name 缓存变量名
     * @return bool
     */
    public function has($name) {
        return $this->handler->has($name);
    }

    /**
     * 读取缓存
     * @access public
     * @param string $name 缓存变量名
     * @param mixed  $default 默认值
     * @return mixed
     */
    public function get($name, $default = null) {
        return $this->handler->get($name, $default);
    }

    /**
     * 写入缓存
     * @access public
     * @param string    $name 缓存变量名
     * @param mixed     $value  存储数据
     * @param int       $expire  有效时间 0为永久
     * @return bool
     */
    public function set($name, $value, $expire = null) {
        return $this->handler->set($name, $value, $expire);
    }

    /**
     * 自增缓存（针对数值缓存）
     * @access public
     * @param string    $name 缓存变量名
     * @param int       $step 步长
     * @return false|int
     */
    public function inc($name, $step = 1) {
        return $this->handler->inc($name, $step);
    }

    /**
     * 自减缓存（针对数值缓存）
     * @access public
     * @param string    $name 缓存变量名
     * @param int       $step 步长
     * @return false|int
     */
    public function dec($name, $step = 1) {
        return $this->handler->dec($name, $step);
    }

    /**
     * 删除缓存
     * @access public
     * @param string $name 缓存变量名
     * @return bool
     */
    public function rm($name) {
        return $this->handler->rm($name);
    }

    /**
     * 清除缓存
     * @access public
     * @return bool
     */
    public function clear() {
        return $this->handler->clear();
    }

}
