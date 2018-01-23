<?php

namespace YoPHP\Routing;

use Exception;
use YoPHP\Http\RequestMethod;

/**
 * 路由线路处理器
 * @author YoPHP <admin@YoPHP.org>
 */
class Route implements RequestMethod {

    /** @var array */
    public $methods = [];

    /** @var null|string */
    public $name;

    /** @var null|string */
    public $url;

    /** @var null|string */
    public $controller;

    /** @var null|string */
    public $action;

    /** @var null|string */
    public $template;

    /** @var array */
    public $parameters = [];

    /** @var array */
    public $values = [];

    /** @var array */
    public $cleanValues = [];

    /**
     * 设置线路信息
     * @param array $data
     * @throws Exception
     */
    public function setData(array $data) {
        $this->methods = $data['methods'] ?? [];
        $this->url = $data['url'] ?? null;
        $this->controller = $data['controller'] ?? null;
        $this->action = $data['action'] ?? null;
        $this->template = $data['template'] ?? null;
        if (!$this->controller) {
            throw new Exception('需要控制器和方法组合或可调用函数');
        }
        if (empty($this->methods) || !is_array($data['methods'])) {
            throw new Exception('必须指定一个请求模式');
        }

        $this->parseUrlParameters();
    }

    /**
     * 解析url参数
     * @return $this
     */
    protected function parseUrlParameters() {
        $urlParts = explode('/', $this->url);
        $this->parameters = [];
        foreach ($urlParts as $index => $part) {
            if (!empty($part) && substr($part, 0, 1) === '{' && substr($part, -1) === '}') {
                $this->parameters[$index] = substr($part, 1, -1);
            }
        }
        return $this;
    }

    /**
     * 
     * @param string $url
     * @return array
     */
    protected function extractParameterValues($url) {
        $urlParts = explode('/', $url);
        $this->values = [];
        foreach ($this->parameters as $index => $name) {
            $value = $urlParts[$index];
            $validValue = $this->checkAndApplyParameterValueType($name, $value);
            if ($validValue === false) {
                $this->values = [];
                break;
            }
            $this->values[$name] = $validValue;
        }
        $this->cleanValues();
        return $this->values;
    }

    /**
     * @param string $name
     * @param mixed  $value
     * @return mixed|bool
     */
    protected function checkAndApplyParameterValueType($name, $value) {
        if (strpos($name, ':') === false) {
            return $value;
        }
        list(, $type) = explode(":", $name);
        if ($type === 'int') {
            if (is_numeric($value) && (int) $value == $value) {
                return (int) $value;
            }
        } elseif ($type === 'float') {
            if (is_numeric($value) && (float) $value == $value) {
                return (float) $value;
            }
        } elseif ($type === 'id') {
            if (is_numeric($value) && preg_match('/^[1-9][0-9]*$/i', $value)) {
                return (int) $value;
            }
        }

        return false;
    }

    protected function removeParameterValueTypeFromName($name) {
        if (strpos($name, ':') === false) {
            return $name;
        }
        list($key) = explode(":", $name);
        return $key;
    }

    /**
     * @param string $url
     * @return string
     */
    protected function injectParameters($url) {
        $urlParts = explode('/', $url);
        foreach ($this->values as $key => $value) {
            $foundKey = array_search($value, $urlParts);
            if ($foundKey !== false) {
                $urlParts[$foundKey] = '{' . ltrim($key, '{}') . '}';
            }
        }
        return implode('/', $urlParts);
    }

    /**
     * 直接匹配路由线路
     * @param string $url
     * @return bool
     */
    public function matchDirectly($url) {
        if (!$this->isAcceptedRequestMethod()) {
            return false;
        }
        if (rtrim($this->url, "/") === rtrim($url, "/")) {
            return true;
        }
        return false;
    }

    /**
     * 匹配路由线路
     * @param string $url
     * @return bool
     */
    public function matchWithParameters($url) {
        if (!$this->parameters || !$this->isAcceptedRequestMethod() || !$this->isPartCountSame($url) || !$this->hasParameters()) {
            return false;
        }
        $this->extractParameterValues($url);
        if (!$this->values) {
            return false;
        }
        $correctedUrl = $this->injectParameters($url);

        if ($this->matchDirectly($correctedUrl)) {
            return true;
        }
        return false;
    }

    /**
     * 检测请求方法是否正确
     * @return bool
     */
    public function isAcceptedRequestMethod() {
        return in_array(strtoupper($_SERVER['REQUEST_METHOD'] ?? self::METHOD_GET), $this->methods);
    }

    /**
     * 验证URL与请求格式是否一样
     * @param string $url
     * @return bool
     */
    public function isPartCountSame($url) {
        return count(explode('/', rtrim($url, '/'))) === count(explode('/', rtrim($this->url, '/')));
    }

    /**
     * 检查url是否有参数
     * @return bool
     */
    public function hasParameters() {
        return mb_strpos($this->url, '{') && mb_strpos($this->url, '}');
    }

    /**
     * @return $this
     */
    protected function cleanValues() {
        foreach ($this->values as $key => $value) {
            $key = $this->removeParameterValueTypeFromName($key);
            $this->cleanValues[$key] = $value;
        }
        return $this;
    }

    /**
     * 获取值
     * @param string $key
     * @return mixed
     */
    public function getValue($key) {
        return $this->cleanValues[$key] ?? null;
    }

    /**
     * 获取所有值
     * @return array
     */
    public function getValues() {
        return $this->cleanValues;
    }

    /**
     * @param array $parameters
     * @return string
     */
    public function buildUrlWithParameters(array $parameters = []) {
        $url = $this->url;

        if (!$this->hasParameters() && !$parameters) {
            return $url;
        }

        foreach ($parameters as $key => $value) {
            $url = str_replace('{' . $key . '}', $value, $url);
        }
        return $url;
    }

}
