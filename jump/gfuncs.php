<?php

spl_autoload_register('jumpAutoLoad');

function jumpAutoLoad($sName) {
    if (class_exists($sName)) {
        return true;
    }
    return reqClass($sName);
}

function reqClass($sClass) {
    if (class_exists($sClass)) {
        return true;
    }
    $aPath = explode('_', $sClass);
    $iCnt = count($aPath) - 1;
    $sDir = 'classes/';
    for ($i = 0; $i < $iCnt; $i++) {
        $sDir.=strtolower($aPath[$i]) . '/';
    }
    $sFile = APP_PATH . $sDir . $aPath[$iCnt] . '.php';
    if (file_exists($sFile)) {
        require_once $sFile;
        return true;
    }
    return false;
}
