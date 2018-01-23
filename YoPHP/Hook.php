<?php

namespace YoPHP;

/**
 * 事件钩子
 * @author YoPHP <admin@YoPHP.org>
 */
class Hook {

    /** @var array */
    protected static $hooks = [];

    /**
     * 绑定事件
     * @param string   $event 事件名称
     * @param callable|string $callable 一个函数或函数名称
     */
    public static function on($event, $callable) {
        self::$hooks[$event][] = $callable;
    }

    /**
     * 清除事件
     * @param string $event 事件名称
     */
    public static function clear($event) {
        unset(self::$hooks[$event]);
    }

    /**
     * 触发事件
     * @param string     $event 事件名称
     * @param null|mixed $payload 额外参数
     */
    public static function trigger($event, &$payload = null) {
        if (isset(self::$hooks[$event])) {
            foreach (self::$hooks[$event] as $closure) {
                Invoke::call($closure, $payload);
            }
        }
    }

}
