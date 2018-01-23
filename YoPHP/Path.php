<?php

namespace YoPHP;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

/**
 * 应用路径
 * @author YoPHP <admin@YoPHP.org>
 */
class Path {

    /** @var string */
    protected static $appdir;

    /**
     * 设置应用根目录
     * @param string $appdir
     */
    public static function setAppDir($appdir) {
        self::$appdir = str_replace(['/', '\\'], DS, rtrim($appdir, '/\\'));
    }

    /**
     * 返回应用根目录
     * @return string
     */
    public static function getAppDir() {
        return self::$appdir;
    }

    /**
     * 获取基于应用目录
     * @param string $dir
     * @return string
     */
    public static function getDir($dir) {
        return self::$appdir . DS . ltrim(str_replace(['/', '\\'], DS, $dir), '/\\');
    }

    /**
     * 递归的创建目录
     * @param string $path 目录路径
     * @param int $permissions 权限
     * @return bool
     */
    public static function mkDir($path, $permissions = 0777) {
        if (is_dir($path)) {
            return true;
        }
        $_path = dirname($path);
        if ($_path !== $path) {
            self::mkDir($_path, $permissions);
        }
        return mkdir($path, $permissions);
    }

    /**
     * 清除目录
     * @access public
     * @return bool
     */
    public static function clearDir($dir) {
        $path = rtrim($dir, '/\\');
        if (is_dir($path)) {
            $directory = new RecursiveDirectoryIterator($path);
            $iterator = new RecursiveIteratorIterator($directory);
            $files = [];
            foreach ($iterator as $info) {
                if ($info->isFile() && $info->isWritable()) {
                    unlink($info->getPathname());
                } elseif ($info->isDir() && $info->isWritable()) {
                    $files[] = $info->getPath();
                }
            }
            if (!empty($files)) {
                $files = array_unique($files);
                foreach ($files as $value) {
                    $value != $path && $value != self::$appdir && rmdir($value);
                }
            }
        }
        return true;
    }

}
