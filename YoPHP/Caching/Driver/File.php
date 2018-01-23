<?php

namespace YoPHP\Caching\Driver;

use YoPHP\Caching\Driver;
use YoPHP\Path;
use YoPHP\Config;

/**
 * 文件类型缓存类
 * @author YoPHP <admin@YoPHP.org>
 */
class File extends Driver {

    /**
     * 检测是否可用
     * @return bool
     */
    public function enabled(): bool {
        return true;
    }

    /**
     * 判断缓存是否存在
     * @access public
     * @param string $name 缓存变量名
     * @return bool
     */
    public function has($name): bool {
        return is_file($this->filename($name));
    }

    /**
     * 读取缓存
     * @access public
     * @param string $name 缓存变量名
     * @param mixed  $default 默认值
     * @return mixed
     */
    public function get($name, $default = null) {
        $filename = $this->filename($name);
        if (is_file($filename)) {
            $expire = file_get_contents($filename, false, null, 8, 12);
            if ($expire !== false) {
                $expire = intval($expire);
                if (0 === $expire || time() <= $expire) {
                    $data = file_get_contents($filename, false, null, 32);
                    return $data ? unserialize($data) : $default;
                }
            }
        }
        return $default;
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
            $expire = intval($this->configBase['expire']) + time();
        }
        $filename = $this->filename($name);
        $dir = dirname($filename);
        return Path::mkDir($dir) && is_writable($dir) && file_put_contents($filename, "<?php\n//" . sprintf('%012d', $expire) . "\n exit();?>\n" . serialize($value)) ? true : false;
    }

    /**
     * 自增缓存（针对数值缓存）
     * @access public
     * @param string    $name 缓存变量名
     * @param int       $step 步长
     * @return false|int
     */
    public function inc($name, $step = 1) {
        $value = $this->get($name);
        $value = $value ? intval($value) + intval($step) : intval($step);
        return $this->set($name, $value, 0) ? $value : false;
    }

    /**
     * 自减缓存（针对数值缓存）
     * @access public
     * @param string    $name 缓存变量名
     * @param int       $step 步长
     * @return false|int
     */
    public function dec($name, $step = 1) {
        $value = $this->get($name);
        $value = $value ? intval($value) - intval($step) : intval($step);
        return $this->set($name, $value, 0) ? $value : false;
    }

    /**
     * 删除缓存
     * @access public
     * @param string $name 缓存变量名
     * @return bool
     */
    public function rm($name) {
        $filename = $this->filename($name);
        return !is_readable($filename) || @unlink($filename);
    }

    /**
     * 清除缓存
     * @access public
     * @return bool
     */
    public function clear() {
        return Path::clearDir($this->getPath());
    }

    private function getPath() {
        return rtrim(Config::get('caching.path') ?: __RUNTIME__ . DS . 'Caching', '/\\');
    }

    /**
     * 取得存储文件名
     * @access private
     * @param string $name 缓存变量名
     * @return string
     */
    protected function filename(string $name): string {
        $name = md5($name);
        $name = $name[0] . $name[1] . DS . $name[2] . $name[3] . DS . $name[4] . $name[5] . DS . $name;
        return $this->getPath() . DS . $name . '.php';
    }

}
