#!/usr/bin/env php
<?php
define('APP_PATH', realpath(dirname(__FILE__)) . '/'); //工作目录
require APP_PATH . 'gfuncs.php'; //全局方法
require APP_PATH . 'classes/Core.php';
list($sClassName, $aPacdrams, $aOptions) = Options::hashOptions($argv);
if (!reqClass($sClassName)) {
    echo 'Class is not exsit!' . PHP_EOL;
    Options::showHelp();
}
$sClassName::instance()->run($aParams);