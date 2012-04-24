#!/usr/bin/env php
<?php
define('APP_PATH', realpath(dirname(__FILE__)) . '/'); //工作目录
define('ENV', 'dev'); //设置工作环境:dev/ga
require APP_PATH . 'gfuncs.php'; //全局方法
require APP_PATH . 'classes/Core.php';
JobCore::getIns()->init($argv);