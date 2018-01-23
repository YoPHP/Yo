<?php

namespace YoPHP;

use YoPHP\Http\RequestMethod;

/**
 * Http Request
 * @author YoPHP <admin@YoPHP.org>
 */
class Request implements RequestMethod {

    /** @var array */
    protected static $headers = [];

    /** @var string */
    protected static $body;

    /**
     * @return string
     */
    public static function getCurrentUrl() {
        return self::getScheme() . '://' . self::getHttpHost() . '/' . ltrim(self::getRequestUrl(), '/');
    }

    /**
     * @return string
     */
    public static function getProtocol() {
        return $_SERVER['SERVER_PROTOCOL'] ?? 'HTTP/1.1';
    }

    /**
     * @return string
     */
    public static function getMethod() {
        return strtoupper($_SERVER['REQUEST_METHOD'] ?? self::METHOD_GET);
    }

    /**
     * @param string $method
     *
     * @return bool
     */
    public static function isMethod($method) {
        return self::getMethod() === $method;
    }

    /**
     * 是否为HEAD请求
     * @return bool
     */
    public static function isHead() {
        return self::isMethod(self::METHOD_HEAD);
    }

    /**
     * 是否为GET请求
     * @return bool
     */
    public static function isGet() {
        return self::isMethod(self::METHOD_GET);
    }

    /**
     * 是否为POST请求
     * @return bool
     */
    public static function isPost() {
        return self::isMethod(self::METHOD_POST);
    }

    /**
     * 是否为PUT请求
     * @return bool
     */
    public static function isPut() {
        return self::isMethod(self::METHOD_PUT);
    }

    /**
     * 是否为PATCH请求
     * @return bool
     */
    public static function isPatch() {
        return self::isMethod(self::METHOD_PATCH);
    }

    /**
     * 是否为DELETE请求
     * @return bool
     */
    public static function isDelete() {
        return self::isMethod(self::METHOD_DELETE);
    }

    /**
     * 是否为PURGE请求
     * @return bool
     */
    public static function isPurge() {
        return self::isMethod(self::METHOD_PURGE);
    }

    /**
     * 是否为OPTIONS请求
     * @return bool
     */
    public static function isOptions() {
        return self::isMethod(self::METHOD_OPTIONS);
    }

    /**
     * 是否为TRACE请求
     * @return bool
     */
    public static function isTrace() {
        return self::isMethod(self::METHOD_TRACE);
    }

    /**
     * 是否为CONNECT请求
     * @return bool
     */
    public static function isConnect() {
        return self::isMethod(self::METHOD_CONNECT);
    }

    /**
     * 是否为cli
     * @return bool
     */
    public static function isCli() {
        return PHP_SAPI == 'cli' ? true : false;
    }

    /**
     * 是否为cgi
     * @return bool
     */
    public static function isCgi() {
        return strpos(PHP_SAPI, 'cgi') === 0;
    }

    /**
     * 当前是否Ajax请求
     * @return bool
     */
    public static function isAjax() {
        return 'xmlhttprequest' == strtolower($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') ? true : false;
    }

    /**
     * 当前是否Pjax请求
     * @return bool
     */
    public static function isPjax() {
        return isset($_SERVER['HTTP_X_PJAX']) ? true : false;
    }

    /**
     * 检测当前请求类型
     * @param string $type
     * @return bool
     */
    public static function isContentType($type) {
        return strpos(self::getContentType(), $type) !== false;
    }

    /**
     * 当前请求 HTTP_CONTENT_TYPE
     * @return string
     */
    public static function getContentType() {
        return $_SERVER['CONTENT_TYPE'] ?? '';
    }

    /**
     * @param string $key
     *
     * @return null|string
     */
    public static function getHeader($key) {
        !self::$headers && self::getHeaders();
        foreach (self::$headers as $header => $content) {
            if (strtolower($key) == strtolower($header)) {
                return $content;
            }
        }
        return null;
    }

    /**
     * @return array
     */
    public static function getHeaders() {
        if (!self::$headers) {
            foreach ($_SERVER as $key => $value) {
                if (substr($key, 0, 4) == 'HTTP') {
                    self::$headers[substr($key, 5)] = $value;
                }
            }
        }
        return self::$headers;
    }

    /**
     * This is surprisingly annoying due to unreliable
     * availability of $_SERVER values.
     *
     * @return string
     */
    public static function getScheme() {
        if (isset($_SERVER['REQUEST_SCHEME'])) {
            // Apache 2.4+
            return $_SERVER['REQUEST_SCHEME'];
        }
        if (isset($_SERVER['REDIRECT_REQUEST_SCHEME'])) {
            return $_SERVER['REDIRECT_REQUEST_SCHEME'];
        }
        if (isset($_SERVER['HTTP_X_FORWARDED_PROTO'])) {
            // Sometimes available in proxied requests
            return $_SERVER['HTTP_X_FORWARDED_PROTO'];
        }
        if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') {
            // Old-style but compatible with IIS
            return 'https';
        }
        if (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443) {
            // Hacky but this is our last attempt, so why not
            return 'https';
        }
        return 'http';
    }

    /**
     * @return null|string
     */
    public static function getHttpHost() {
        if (isset($_SERVER['HTTP_HOST']) && isset($_SERVER['SERVER_NAME']) && $_SERVER['HTTP_HOST'] === $_SERVER['SERVER_NAME']
        ) {
            return $_SERVER['HTTP_HOST'];
        }

        if (isset($_SERVER['HTTP_HOST'])) {
            return $_SERVER['HTTP_HOST'];
        }

        // This is the least reliable, due to the ability to spoof it
        if (isset($_SERVER['SERVER_NAME'])) {
            return $_SERVER['SERVER_NAME'];
        }

        return null;
    }

    /**
     * @return null|string
     */
    public static function getRequestUrl() {
        return $_SERVER['REQUEST_URI'] ?? '';
    }

    /**
     * @return null|string
     */
    public static function getScriptName() {
        return $_SERVER['SCRIPT_NAME'] ?? '';
    }

    /**
     * @return string
     */
    public static function getBody() {
        if (self::$body === null) {
            self::$body = file_get_contents('php://input');
        }
        return self::$body;
    }

}
