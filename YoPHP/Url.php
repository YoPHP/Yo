<?php

namespace YoPHP;

use YoPHP\Request;

/**
 * Http URL
 * @author YoPHP <admin@YoPHP.org>
 */
class Url {

    /** @var string */
    protected static $baseUrl;

    /** @var string */
    protected static $basePath = "/public";

    /** @var string */
    protected static $scriptName = "/index.php";

    /**
     * @param string $basePath
     *
     * @return $this
     */
    public static function setBasePath($basePath) {
        if ($basePath) {
            $basePath = "/" . trim($basePath, "/");
        }
        self::$basePath = $basePath;
    }

    /**
     * @return string
     */
    public static function getBasePath() {
        return self::$basePath;
    }

    /**
     * @return string
     */
    public static function getScriptName() {
        return self::$scriptName;
    }

    /**
     * Initialize the correct baseUrl
     *
     * @return $this
     */
    public static function buildBaseUrl() {
        $domain = Request::getScheme() . '://' . Request::getHttpHost();

        $url = Request::getScriptName();

        // We only want to remove the first occurrence of our base path, and only if base path is valid
        if (self::getBasePath()) {
            $basePathPos = strpos($url, self::getBasePath());
            if ($basePathPos !== false) {
                $url = substr_replace($url, "", $basePathPos, strlen(self::getBasePath()));
            }
        }

        // And we want to remove the script name separately, since it's possible base path is empty
        $url = str_replace(self::getScriptName(), '', $url);

        self::$baseUrl = $domain . '/' . ltrim($url, '/');
    }

    /**
     * @return string
     */
    public static function getBaseUrl() {
        self::buildBaseUrl();
        return self::$baseUrl;
    }

    /**
     * @param string $url
     *
     * @return string
     */
    public static function get($url = '') {
        return rtrim(self::getBaseUrl(), '/') . '/' . ltrim($url, '/');
    }

    /**
     * 返回路由请求路径
     * @return string
     */
    public static function getPathInfo() {
        return trim(Request::isCli() ? ($_SERVER['argv'][1] ?? '') : ($_SERVER['PATH_INFO'] ?? ''), '/');
    }

}
