#!/usr/bin/php
<?php
include_once 'functions.php';
$arrParams = array();
foreach ($argv as $valString) {
    if (strpos($valString, '--') !== 0) {
        continue;
    }
    $valString = str_replace('--', '', $valString);
    $valString = str_replace('-', '_', $valString);
    parse_str($valString, $arrTmp);
    $arrParams = array_merge($arrParams, $arrTmp);
}

$strWords = isset($arrParams['word']) ? $arrParams['word'] : null;
$strMode = isset($arrParams['mode']) ? $arrParams['mode'] : 't';
$bolMulti = isset($arrParams['multi']) ? $arrParams['multi'] : false;
if (empty($strWords)) {
    exit('please give me a word');
}
$objPyC = new PyClient();
$objPyC->send($strWords, $strMode, $bolMulti);

class PyClient {

    protected $arrSockedConfig;

    public function send($strWords, $strMode = 't', $bolMulti = false) {
        $this->arrSockedConfig = getConfigs('socket');
        $socket = socket_create($this->arrSockedConfig['domain'], $this->arrSockedConfig['type'], $this->arrSockedConfig['protocol']);
        $connection = socket_connect($socket, $this->arrSockedConfig['address'], $this->arrSockedConfig['port']);
        $arrSendMsg = array(
            'word' => $strWords,
            'mode' => $strMode,
            'multi' => $bolMulti ? $bolMulti : false
        );
        if (!socket_write($socket, serialize($arrSendMsg) . "\n")) {
            echo "Write failed";
        }
        if ($strPinyin = socket_read($socket, 1024, PHP_NORMAL_READ)) {
            echo $strPinyin;
        }
        socket_close($socket);
    }

}
