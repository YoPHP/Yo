<?php

namespace YoPHP;

use YoPHP\Routing\Route;
use YoPHP\Hook;
use YoPHP\View;
use YoPHP\Container;
use YoPHP\Output;

/**
 * 应用调度
 * @author YoPHP <admin@YoPHP.org>
 */
class Dispatcher {

    /**
     * @param Route $route
     * @return $this
     */
    public static function dispatch(Route $route) {

        Hook::trigger('dispatch_start', $route);
        $content = null;

        $parameters = [$route];
        foreach ($route->getValues() as $value) {
            $parameters[] = $value;
        }

        if ($route->controller && $route->action) {//是控制器
            $content = Invoke::method($route->controller, $route->action, $parameters);
        } elseif ($route->controller) {//是函数
            $content = Invoke::call($route->controller, $parameters);
        }

        $templateFile = null;
        if (!$route->template && $route->controller && $route->action) {
            $name = substr(ltrim($route->controller, '\\'), 11);
            $templateFile = Path::getDir(VIEW_NAME . DS . $name . DS . $route->action);
        } elseif ($route->template) {
            $templateFile = is_readable($route->template) ? $route->template : Path::getDir(VIEW_NAME . DS . $route->template);
        }

        if ($templateFile) {
            Output::startOutputBuffer();
            $View = Container::get(View::class);
            if (is_array($content) || is_object($content)) {
                $View->assign((array) $content);
            }
            $View->render($templateFile);
            $ViewContent = Output::returnOutputBuffer();
        }
        !empty($ViewContent) ? Output::appendContent($ViewContent) : Output::appendContent($content);

        Hook::trigger('dispatch_end', $route);
    }

}
