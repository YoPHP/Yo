<?php

namespace YoPHP\ORM;

use YoPHP\Base\Container;

/**
 * Model
 * @author YoPHP <admin@YoPHP.org>
 */
class Model extends Query {

    /**
     * 解析表
     * @param string  $form 表名
     * @return string
     */
    protected function parsefrom($form = null) {
        if (empty($form)) {
            $form = ($pos = strrpos(($name = get_class($this)), '\\')) !== false ? substr($name, $pos + 1) : $name;
        }
        return parent::parsefrom($form);
    }

    /**
     * 获取Model
     * @param string $modelName
     * @return $this
     */
    protected function getModel($modelName) {
        return Container::get($modelName);
    }

}
