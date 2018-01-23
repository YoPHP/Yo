<?php

namespace YoPHP;

use YoPHP\Http\StatusCode;
use YoPHP\Output\Html;
use YoPHP\Output\Json;
use YoPHP\Container;

/**
 * 输出类
 * @author YoPHP <admin@YoPHP.org>
 */
class Output implements StatusCode {

    /** @var int */
    protected static $httpCode = self::STATUS_OK;

    /** @var string|array */
    protected static $content = null;

    /** @var string */
    protected static $contentType;

    /** @var array */
    protected static $headers = [];

    /**
     * 设置HTTP状态码
     * @param int $httpCode
     */
    public static function setHttpCode($httpCode) {
        self::$httpCode = $httpCode;
    }

    /**
     * 返回HTTP状态码
     * @return int
     */
    public static function getHttpCode() {
        return self::$httpCode;
    }

    /**
     * 返回HTTP消息
     * @return string
     */
    public static function getHttpCodeText() {
        return self::HTTP_CODES[self::$httpCode] ?? '';
    }

    /**
     * 设置HTTP CONTENT_TYPE
     */
    public static function setContentType($contentType) {
        self::$contentType = $contentType;
    }

    /**
     * 返回HTTP CONTENT_TYPE
     * @param string
     */
    public static function getContentType() {
        return self::$contentType;
    }

    /**
     * 发送到客户端
     */
    public static function send() {

        Container::get(is_string(self::$content) ? Html::class : Json::class)->prepare();

        if (!headers_sent()) {
            header(Request::getProtocol() . ' ' . self::getHttpCode() . ' ' . self::getHttpCodeText());
            header('Content-type: ' . self::getContentType() . ';charset=utf-8');
            foreach (self::getHeaders() as $key => $value) {
                header("{$key}: {$value}");
            }
        }

        echo self::getContent();
    }

    /**
     * 设置内容
     * @param string|array $content
     */
    public static function setContent($content) {
        self::$content = $content;
    }

    /**
     * 返回内容
     * @return string|array
     */
    public static function getContent() {
        return self::$content;
    }

    /**
     * 追加内容
     * @param string $content
     */
    public static function appendContent($content) {
        if (is_array(self::$content)) {
            self::$content[] = $content;
        } else {
            self::$content .= $content;
        }
    }

    /**
     * 追加内容在最前
     * @param string $content
     */
    public static function prependContent($content) {
        if (is_array(self::$content)) {
            array_unshift(self::$content, $content);
        } else {
            self::$content = $content . self::$content;
        }
    }

    /**
     * 设置Header
     * @param string $key
     * @param string $value
     */
    public static function setHeader($key, $value) {
        self::$headers[$key] = $value;
    }

    /**
     * 返回Header
     * @param string $key
     * @return null|string
     */
    public static function getHeader($key) {
        if (!isset(self::$headers[$key])) {
            return null;
        }
        return self::$headers[$key];
    }

    /**
     * 返回所有Header
     * @return array
     */
    public static function getHeaders() {
        return self::$headers;
    }

    /**
     * 跳转
     * @param string $url
     */
    public static function redirect($url) {
        if (!headers_sent()) {
            header("location: {$url}");
        }
        exit;
    }

    /**
     * 开始一个新的输出缓冲区
     */
    public static function startOutputBuffer() {
        ob_start();
    }

    /**
     * 开始输出缓冲
     * @return string
     */
    public static function returnOutputBuffer() {
        return ob_get_clean();
    }

}
