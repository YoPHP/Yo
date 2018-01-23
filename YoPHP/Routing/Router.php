<?php

namespace YoPHP\Routing;

use YoPHP\Http\RequestMethod;
use YoPHP\Container;

/**
 * 路由器
 * @author YoPHP <admin@YoPHP.org>
 */
class Router implements RequestMethod {

    /** @var array */
    protected static $routes = [];

    /**
     * @var string 
     */
    protected static $baseRoute = '';

    /**
     * 注册HEAD路由
     * @param string $url URL或正则表达
     * @param string|callable $controller 控制器或回调函数
     * @param string $action 方法
     * @param string $template 模板
     * @return $this
     */
    public function head(string $url, $controller, string $action = '', string $template = null) {
        return self::map([self::METHOD_HEAD], $url, $controller, $action, $template);
    }

    /**
     * 注册GET路由
     * @param string $url URL或正则表达
     * @param string|callable $controller 控制器或回调函数
     * @param string $action 方法
     * @param string $template 模板
     * @return $this
     */
    public function get(string $url, $controller, string $action = '', string $template = null) {
        return self::map([self::METHOD_GET], $url, $controller, $action, $template);
    }

    /**
     * 注册POST路由
     * @param string $url URL或正则表达
     * @param string|callable $controller 控制器或回调函数
     * @param string $action 方法
     * @param string $template 模板
     * @return $this
     */
    public function post(string $url, $controller, string $action = '', string $template = null) {
        return self::map([self::METHOD_POST], $url, $controller, $action, $template);
    }

    /**
     * 注册GET|POST路由
     * @param string $url URL或正则表达
     * @param string|callable $controller 控制器或回调函数
     * @param string $action 方法
     * @param string $template 模板
     * @return $this
     */
    public function get_post(string $url, $controller, string $action = '', string $template = null) {
        return self::map([self::METHOD_GET, self::METHOD_POST], $url, $controller, $action, $template);
    }

    /**
     * 注册PUT路由
     * @param string $url URL或正则表达
     * @param string|callable $controller 控制器或回调函数
     * @param string $action 方法
     * @param string $template 模板
     * @return $this
     */
    public function put(string $url, $controller, string $action = '', string $template = null) {
        return self::map([self::METHOD_PUT], $url, $controller, $action, $template);
    }

    /**
     * 注册PATCH路由
     * @param string $url URL或正则表达
     * @param string|callable $controller 控制器或回调函数
     * @param string $action 方法
     * @param string $template 模板
     * @return $this
     */
    public function patch(string $url, $controller, string $action = '', string $template = null) {
        return self::map([self::METHOD_PATCH], $url, $controller, $action, $template);
    }

    /**
     * 注册DELETE路由
     * @param string $url URL或正则表达
     * @param string|callable $controller 控制器或回调函数
     * @param string $action 方法
     * @param string $template 模板
     * @return $this
     */
    public function delete(string $url, $controller, string $action = '', string $template = null) {
        return self::map([self::METHOD_DELETE], $url, $controller, $action, $template);
    }

    /**
     * 注册PURGE路由
     * @param string $url URL或正则表达
     * @param string|callable $controller 控制器或回调函数
     * @param string $action 方法
     * @param string $template 模板
     * @return $this
     */
    public function purge(string $url, $controller, string $action = '', string $template = null) {
        return self::map([self::METHOD_PURGE], $url, $controller, $action, $template);
    }

    /**
     * 注册OPTIONS路由
     * @param string $url URL或正则表达
     * @param string|callable $controller 控制器或回调函数
     * @param string $action 方法
     * @param string $template 模板
     * @return $this
     */
    public function options(string $url, $controller, string $action = '', string $template = null) {
        return self::map([self::METHOD_OPTIONS], $url, $controller, $action, $template);
    }

    /**
     * 注册TRACE路由
     * @param string $url URL或正则表达
     * @param string|callable $controller 控制器或回调函数
     * @param string $action 方法
     * @param string $template 模板
     * @return $this
     */
    public function trace(string $url, $controller, string $action = '', string $template = null) {
        return self::map([self::METHOD_TRACE], $url, $controller, $action, $template);
    }

    /**
     * 注册CONNECT路由
     * @param string $url URL或正则表达
     * @param string|callable $controller 控制器或回调函数
     * @param string $action 方法
     * @param string $template 模板
     * @return $this
     */
    public function connect(string $url, $controller, string $action = '', string $template = null) {
        return self::map([self::METHOD_CONNECT], $url, $controller, $action, $template);
    }

    /**
     * 注册到所有路由
     * @param string $url URL或正则表达
     * @param string|callable $controller 控制器或回调函数
     * @param string $action 方法
     * @param string $template 模板
     * @return $this
     */
    public function any(string $url, $controller, string $action = '', string $template = null) {
        return self::map([self::METHOD_HEAD, self::METHOD_GET, self::METHOD_POST, self::METHOD_PUT, self::METHOD_PATCH, self::METHOD_DELETE, self::METHOD_PURGE, self::METHOD_OPTIONS, self::METHOD_TRACE, self::METHOD_CONNECT], $url, $controller, $action, $template);
    }

    /**
     * 注册路由组
     * @param string $baseRoute
     * @param callable $callback
     * @param array|string $methods
     * @return void
     */
    public function group($baseRoute, $callback) {
        self::$baseRoute = '';
        if (is_callable($callback)) {
            $curBaseRoute = self::$baseRoute;
            self::$baseRoute .= $baseRoute;
            call_user_func($callback);
            self::$baseRoute = $curBaseRoute;
        }
    }

    /**
     * 注册到路由
     * @param array $methods HTTP方法名称
     * @param string $url URL或正则表达
     * @param string|callable $controller 控制器或回调函数
     * @param string $action 方法
     * @param string $template 模板
     * @return $this
     */
    public function map(array $methods, string $url, $controller, string $action = '', string $template = null) {
        $routeArray = [
            'methods' => $methods,
            'url' => self::$baseRoute ? rtrim(self::$baseRoute . '/' . trim($url, '/'), '/') : $url,
            'controller' => $controller,
            'action' => $action,
            'template' => $template
        ];
        return self::addRoute($routeArray);
    }

    /**
     * 注册路由
     * @param array $routeArray
     * @return $this
     */
    public function addRoute(array $routeArray) {
        $route = Container::create(Route::class);
        $route->setData($routeArray);
        self::$routes[] = $route;
        return $this;
    }

    /**
     * 匹配路由
     * @param string $url
     * @return Route|null
     */
    public function matchUrl($url) {
        $url = '/' . ltrim($url, '/');
        if (($route = $this->matchUrlDirectly($url))) {
            return $route;
        }
        if (($route = $this->matchUrlWithParameters($url))) {
            return $route;
        }
        return null;
    }

    /**
     * 尝试直接匹配路由线路
     * @param string $url
     * @return Route|null
     */
    protected function matchUrlDirectly($url) {
        foreach (self::$routes as $route) {
            if ($route->matchDirectly($url)) {
                return $route;
            }
        }
        return null;
    }

    /**
     * 尝试匹配路由线路参数
     * @param string $url
     * @return Route|null
     */
    protected function matchUrlWithParameters($url) {
        foreach (self::$routes as $route) {
            if ($route->matchWithParameters($url)) {
                return $route;
            }
        }
        return null;
    }

}
