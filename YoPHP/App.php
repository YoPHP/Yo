<?php

/**
 * Yo Framework for PHP
 * @name Yo Framework
 * @author YoPHP <admin@YoPHP.org>
 * @version 0.0.1
 * @link http://www.YoPHP.org
 * @copyright YoPHP.org
 * @license Apache-2.0
 */

namespace YoPHP;

use YoPHP\Hook;
use YoPHP\Url;
use YoPHP\Routing;
use YoPHP\Loader;
use YoPHP\Path;
use YoPHP\Container;
use YoPHP\Output;
use Throwable;

/**
 * APP入口
 * @author YoPHP <admin@YoPHP.org>
 */
class App {

    private static $route = null;

    /**
     * APP运行
     */
    public static function run() {
        Hook::trigger('app_run_start');
        if (self::$route) {
            try {
                Dispatcher::dispatch(self::$route);
                Output::setHttpCode(200);
            } catch (Throwable $e) {
                $errorStr = $e->getMessage() . ' ' . $e->getFile() . ' 第 ' . $e->getLine() . ' 行';
                if (DEBUG) {
                    echo '出错:' . PHP_EOL;
                    echo $e->getMessage() . PHP_EOL;
                    echo $errorStr;
                }
                echo 500;
                Response::setHttpCode(500);
            }
        } else {
            echo 404;
            Response::setHttpCode(404);
        }
        Output::send();
    }

    /**
     * APP初始
     */
    public static function init() {
        Hook::trigger('app_init_start');
        $pathInfo = Url::getPathInfo();
        Hook::trigger('route_match_start', $pathInfo);
        $Routing = Container::get(Routing::class);
        Loader::load(Path::getDir('routing.php'), $Routing);
        self::$route = $Routing->matchUrl($pathInfo);
        Hook::trigger('route_match_end', self::$route);
        Hook::trigger('app_init_end');
    }

}
