<?php

namespace YoPHP;

use ReflectionClass;
use ReflectionFunction;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use YoPHP\Path;

/**
 * 执行函数、类
 * @author YoPHP <admin@YoPHP.org>
 */
class Invoke {

    /**
     * 执行一个方法
     * @param type $className 类名称
     * @param type $name 方法名称
     * @param array $parameters 传递参数
     * @return mixed
     */
    public static function method($className, $name, array $parameters = []) {
        $class = Container::get($className);
        $class = new ReflectionClass($class);
        $method = $class->getmethod($name);
        $className = get_class($class);
        if ($method->isPublic() && !$method->isStatic()) {
            if ($method->getNumberOfParameters() > 0) {
                $args = [];
                foreach ($method->getParameters() as $value) {
                    if (is_object($value->getClass())) {
                        $subClassName = $value->getClass()->getName();
                        $args[] = Container::get($subClassName, $className);
                    } else {
                        $args[] = array_shift($parameters) ?: ($value->isDefaultValueAvailable() ? $value->getDefaultValue() : null);
                    }
                }
                return $method->invokeArgs($class->newInstanceArgs(), $args);
            } else {
                return $method->invoke($class->newInstanceArgs());
            }
        }
    }

    /**
     * 执行一个函数
     * @param mixed $name 匿名函数或函数名称
     * @param mixed $parameters 传递参数
     * @return mixed
     */
    public static function call($name, $parameters = null) {
        $function = new ReflectionFunction($name);
        if ($function->getNumberOfParameters() > 0) {
            $args = [];
            foreach ($function->getParameters() as $value) {
                if (is_object($value->getClass())) {
                    $args[] = Container::get($value->getClass()->getName());
                } else {
                    $args[] = $parameters ?: ($value->isDefaultValueAvailable() ? $value->getDefaultValue() : null);
                }
            }
            return $function->invokeArgs($args);
        } else {
            return $function->invoke();
        }
    }

    /**
     * 迭代实例
     * @param string $name
     * @param array $classes
     */
    public static function iterator($name, &$classes = []) {
        $path = Path::getDir($name);
        if (is_dir($path)) {
            $dirIterator = new RecursiveDirectoryIterator($path, RecursiveDirectoryIterator::SKIP_DOTS);
            $iteratorIterator = new RecursiveIteratorIterator($dirIterator);
            foreach ($iteratorIterator as $file) {
                if ($file->getExtension() !== 'php') {
                    continue;
                }
                $className = "\\{$name}\\" . str_replace('.' . $file->getExtension(), '', $file->getFilename());
                $classes[] = Container::get($className);
            }
        }
    }

}
