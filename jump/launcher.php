#!/usr/bin/env php
<?php
define('APP_PATH', realpath(dirname(__FILE__)) . '/'); //工作目录
define('ENV', 'dev'); //设置工作环境:dev/ga

$G_LOAD_PATH = array(
	APP_PATH . 'classes/',
	APP_PATH . 'sys/'
);

require APP_PATH . 'sys/gfuncs.php'; //全局方法
Core::getIns()->init($argv)->run();
