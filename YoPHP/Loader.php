<?php

namespace YoPHP;

/**
 * 加载处理
 * @author YoPHP <admin@YoPHP.org>
 */
class Loader {

    /**
     * 命名空间
     * @var array
     */
    protected static $prefixes = [];

    /**
     * 初始化
     */
    public static function init() {
        self::autoload();
        self::addNamespace('YoPHP', __YO__);
        Path::setAppDir(__APP__);
        self::addNamespace(CONTROLLER_NAME, Path::getDir(CONTROLLER_NAME));
        self::addNamespace(MODEL_NAME, Path::getDir(MODEL_NAME));
        self::addNamespace(LIBRARY_NAME, Path::getDir(LIBRARY_NAME));
    }

    /**
     * 自动加载
     * @param mixed $autoload
     */
    public static function autoload($autoload = null) {
        spl_autoload_register($autoload ?: 'self::loadClass', true, true);
    }

    /**
     * 注册本地命名空间
     * @param string $prefix 命名空间前缀
     * @param string $baseDir 类文件的基本目录
     * @param bool $prepend true最先搜索到否则最后
     */
    public static function addNamespace($prefix, $baseDir, $prepend = false) {
        $prefix = trim($prefix, '\\') . '\\';
        $baseDir = rtrim($baseDir, '/\\') . DS;
        if ($baseDir !== false) {
            if (isset(self::$prefixes[$prefix])) {
                $prepend ? array_unshift(self::$prefixes[$prefix], $baseDir) : array_push(self::$prefixes[$prefix], $baseDir);
            } else {
                self::$prefixes[$prefix][] = $baseDir;
            }
        }
    }

    /**
     * 获取已注册的名空间
     * @param string $prefix 命名空间前缀 null时返回所有
     * @return mixed
     */
    public static function getLocalNamespace($prefix = null) {
        return $prefix === null ? self::$prefixes : self::$prefixes[$prefix] ?? null;
    }

    /**
     * 加载类
     * @param string $class 类名称
     * @return boot
     */
    public static function loadClass($class) {
        $prefix = $class;
        while (false !== $pos = strrpos($prefix, '\\')) {
            $prefix = substr($class, 0, $pos + 1);
            $relativeClass = substr($class, $pos + 1);
            if (self::loadMappedFile($prefix, $relativeClass)) {
                return true;
            } else {
                $prefix = rtrim($prefix, '/\\');
            }
        }
        return false;
    }

    /**
     * 加载命名空间前缀和相对类的映射文件
     * @param string $prefix 命名空间前缀
     * @param string $filename 相对类
     * @return boot
     */
    public static function loadMappedFile($prefix, $filename) {
        if (isset(self::$prefixes[$prefix])) {
            foreach (self::$prefixes[$prefix] as $baseDir) {
                if (self::requireFile($baseDir . $filename . '.php')) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * 如果存在文件，则加载文件系统
     * @param string $filename 需要加载的文件
     * @param bool $return 是否返回加载文件
     * @return bool
     */
    public static function requireFile($filename, $return = false) {
        $file = str_replace(['/', '\\'], DS, $filename);
        if (is_readable($file)) {
            if ($return) {
                return require $file;
            }
            require $file;
            return true;
        } else {
            return false;
        }
    }

    /**
     * 加载文件
     * @param string $filename  文件名
     * @param array $vars  传入变量
     * @return bool        
     */
    public static function load($filename, $vars = null) {
        $file = str_replace(['/', '\\'], DS, $filename);
        if (is_readable($file)) {
            if (!is_null($vars)) {
                switch (gettype($vars)) {
                    case 'object':
                        $name = get_class($vars);
                        $strrchr = strrchr($name, '\\');
                        extract([$name === false ? $name : substr($strrchr, 1) => $vars], EXTR_OVERWRITE);
                        break;
                    case "array":
                        extract($vars, EXTR_OVERWRITE);
                        break;
                    default:
                        break;
                }
            }
            require $file;
            return true;
        } else {
            return false;
        }
    }

}
