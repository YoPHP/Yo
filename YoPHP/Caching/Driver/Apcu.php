<?php

namespace YoPHP\Caching\Driver;

use YoPHP\Caching\Driver;

/**
 * Apcu缓存驱动
 * @author YoPHP <admin@YoPHP.org>
 */
class Apcu extends Driver {

    /**
     * 检测是否可用
     * @return bool
     */
    public function enabled(): bool {
        return function_exists('apcu_cache_info');
    }

    /**
     * 判断缓存
     * @param string $name 缓存变量名
     * @return bool
     */
    public function has($name): bool {
        return apcu_exists($this->filename($name));
    }

    /**
     * 读取缓存
     * @param string $name 缓存变量名
     * @return mixed
     */
    public function get($name) {
        return apcu_fetch($this->filename($name));
    }

    /**
     * 写入缓存
     * @param string $name 缓存变量名
     * @param mixed $value  存储数据
     * @param int $expire  有效时间（秒）
     * @return bool
     */
    public function set($name, $value, $expire = null) {
        if (is_null($expire)) {
            $expire = $this->configBase['expire'];
        }
        return apcu_store($this->filename($name), $value, $expire);
    }

    /**
     * 删除缓存
     * @param string $name 缓存变量名
     * @return bool
     */
    public function rm($name) {
        $filename = $this->filename($name);
        return apcu_delete($filename) || !apcu_exists($filename);
    }

    /**
     * 自增缓存（针对数值缓存）
     * @param string    $name 缓存变量名
     * @param int       $step 步长
     * @return false|int
     */
    public function inc($name, $step = 1) {
        return apcu_inc($this->filename($name), intval($step));
    }

    /**
     * 自减缓存（针对数值缓存）
     * @param string    $name 缓存变量名
     * @param int       $step 步长
     * @return false|int
     */
    public function dec($name, $step = 1) {
        return apcu_dec($this->filename($name), intval($step));
    }

    /**
     * 清除缓存
     * @return bool
     */
    public function clear() {
        return apcu_clear_cache() && apcu_clear_cache('user');
    }

}
