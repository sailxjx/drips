#!/usr/bin/env php
<?php
define('APP_PATH', realpath(dirname(__FILE__)) . '/'); //工作目录
require APP_PATH . 'gfuncs.php'; //全局方法
require APP_PATH . 'classes/Core.php';
$oCore = JobCore::instance();
$sJobClass = $oCore->init($argv)->getJobClass();
if (empty($sJobClass) || !reqClass($sJobClass)) {
	Util::output('Class is not exsit!');
	$oCore->showHelp();
}
$sJobClass::instance()->run();