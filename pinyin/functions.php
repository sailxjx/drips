<?php

function getConfigs($strName) {
    include 'Config.php';
    if (isset($config[$strName])) {
        return $config[$strName];
    }
    return null;
}
