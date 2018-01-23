<?php

namespace YoPHP\Log\Driver;

use YoPHP\Log\Driver;
use YoPHP\Path;
use YoPHP\Config;

/**
 * 文件记录
 * @author YoPHP <admin@YoPHP.org>
 */
class File extends Driver {

    /** @var Path */
    protected $path;

    /** @var Config */
    protected $config;

    public function __construct(Path $path, Config $config) {
        $this->path = $path;
        $this->config = $config;
    }

    /**
     * 写入日志
     * @param 级别 $level
     * @param 消息 $message
     */
    public function write($level, $message) {
        $message = rtrim($message) . PHP_EOL;
        $now = new \DateTime();
        $timeString = $now->format('Y-m-d H:i:s');
        $timeString .= ' ' . $now->getTimezone()->getName();
        $filename = $this->getPath() . DS . $level . DS . $now->format('Y') . DS . $now->format('m') . DS . $now->format('d') . '.log';
        $dir = dirname($filename);
        $this->path->mkDir($dir) && is_writable($dir) && error_log("[{$timeString}] {$message}", 3, $filename);
    }

    private function getPath() {
        return rtrim($this->config->get('log.path') ?: __RUNTIME__ . DS . 'Log', '/\\');
    }

}
