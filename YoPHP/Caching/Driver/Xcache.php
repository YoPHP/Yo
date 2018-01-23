<?php

namespace YoPHP\Caching\Driver;

use YoPHP\Caching\Driver;

/**
 * Xcache缓存驱动
 * @author YoPHP <admin@YoPHP.org>
 * @link http://xcache.lighttpd.net/ xcache
 */
class Xcache extends Driver {

    /**
     * 检测是否可用
     * @return bool
     */
    public function enabled(): bool {
        return function_exists('xcache_info');
    }

    /**
     * 判断缓存
     * @access public
     * @param string $name 缓存变量名
     * @return bool
     */
    public function has($name): bool {
        return xcache_isset($this->filename($name));
    }

    /**
     * 读取缓存
     * @access public
     * @param string $name 缓存变量名
     * @param mixed  $default 默认值
     * @return mixed
     */
    public function get($name, $default = null) {
        $key = $this->filename($name);
        return xcache_isset($key) ? xcache_get($key) : $default;
    }

    /**
     * 写入缓存
     * @access public
     * @param string            $name 缓存变量名
     * @param mixed             $value  存储数据
     * @param int $expire  有效时间（秒）
     * @return bool
     */
    public function set($name, $value, $expire = null) {
        if (is_null($expire)) {
            $expire = $this->configBase['expire'];
        }
        return xcache_set($this->filename($name), $value, $expire) ? true : false;
    }

    /**
     * 自增缓存（针对数值缓存）
     * @access public
     * @param string    $name 缓存变量名
     * @param int       $step 步长
     * @return false|int
     */
    public function inc($name, $step = 1) {
        return xcache_inc($this->filename($name), intval($step));
    }

    /**
     * 自减缓存（针对数值缓存）
     * @access public
     * @param string    $name 缓存变量名
     * @param int       $step 步长
     * @return false|int
     */
    public function dec($name, $step = 1) {
        return xcache_dec($this->filename($name), intval($step));
    }

    /**
     * 删除缓存
     * @access public
     * @param string $name 缓存变量名
     * @return bool
     */
    public function rm($name) {
        $filename = $this->filename($name);
        return !xcache_isset($filename) || xcache_unset($filename);
    }

    /**
     * 清除缓存
     * @access public
     * @return bool
     */
    public function clear() {
        return xcache_clear_cache(XC_TYPE_VAR);
    }

}
