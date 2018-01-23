<?php

namespace YoPHP;

use YoPHP\Path;
use YoPHP\Loader;

/**
 * 配制类
 * @author YoPHP <admin@YoPHP.org>
 */
class Config {

    /** @var array */
    protected static $config = [];

    /**
     * 获取配制
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public static function get($key, $default = null) {
        $name = strstr($key, '.', true) ?: $key;
        !isset(self::$config[$name]) && self::load($name);
        if (strpos($key, '.') !== false) {
            return self::getNested(self::$config, explode('.', $key), $default);
        }
        return self::$config[$key] ?? $default;
    }

    /**
     * 设置配制
     * @param string|array $key
     * @param mixed $value
     */
    public static function set($key, $value = null) {
        if (is_array($key)) {
            foreach ($key as $value) {
                self::$config = $value;
            }
        } else {
            self::$config[$key] = $value;
        }
    }

    /**
     * 获取嵌入配制
     * @param array $data
     * @param array $keys
     * @return mixed
     */
    private static function getNested(array &$data, array $keys, $default = null) {
        foreach ($keys as $key) {
            if (isset($data[$key])) {
                $data = &$data[$key];
            } else {
                return $default;
            }
        }
        return $data ?: $default;
    }

    /**
     * 加载配制

     */
    public static function load($name) {
        $config = Loader::requireFile(Path::getDir(CONFIG_NAME . DS . $name . '.php'), true);
        if (is_array($config)) {
            self::$config[$name] = isset(self::$config[$name]) ? array_merge(self::$config[$name], $config) : $config;
        }
    }

}
