<?php

use YoPHP\Loader;
use YoPHP\App;

//简化文件分隔符
defined('DS') or define('DS', DIRECTORY_SEPARATOR);
//框架根目录
defined('__YO__') or define('__YO__', __DIR__);
//APP根目录
defined('__APP__') or define('__APP__', dirname(__DIR__) . DS . 'App');
//APP运行目录
defined('__RUNTIME__') or define('__RUNTIME__', __APP__ . DS . 'Runtime');
//Controller目录名称
defined('CONTROLLER_NAME') or define('CONTROLLER_NAME', 'Controller');
//Model目录名称
defined('MODEL_NAME') or define('MODEL_NAME', 'Model');
//Library目录名称
defined('LIBRARY_NAME') or define('LIBRARY_NAME', 'Library');
//Config目录名称
defined('CONFIG_NAME') or define('CONFIG_NAME', 'Config');
//View目录名称
defined('VIEW_NAME') or define('VIEW_NAME', 'View');
//框架调试
defined('DEBUG') or define('DEBUG', true);
//加载处理
require __YO__ . '/Loader.php';
//加载初始
Loader::init();
//APP初始化
App::init();
