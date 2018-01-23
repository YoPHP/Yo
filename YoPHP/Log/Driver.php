<?php

namespace YoPHP\Log;

/**
 * 日志接口
 * @author YoPHP <admin@YoPHP.org>
 */
abstract class Driver {

    /**
     * 写入日志
     * @param 级别 $level
     * @param 消息 $message
     * @return $this
     */
    abstract public function write($level, $message);
}
