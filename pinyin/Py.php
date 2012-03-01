#!/usr/bin/php
<?php
echo 'JobBegin:', date('Y-m-d H:i:s');
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
if (!empty($arrParams)) {
    extract($arrParams);
}

$objPy = new Py();
$objPy->listen();

class Py {

    protected $strWords;

    const PARAM_KEY_FOR = 'fo';
    const PARAM_KEY_WORDS = 'w';
    const PARAM_KEY_TYPE = 't';
    const PARAM_KEY_TRANS = 'trans';
    const PARAM_KEY_MODE = 'mode';
    const PARAM_AJAX = 'ajax';
    const PARAM_TO_PINYIN = 'tp';
    const PARAM_TO_WORDS = 'tw';

    protected $arrParams;
    protected $strDictMemKey = 'dictkey';
    protected $arrSocketConfig;
    protected $strLogFile;
    protected $arrDicts;
    protected $intMaxCandidate = 1;
    protected $intMaxPreRead = 15;

    public function listen() {
        $this->arrDicts = $this->getDictFromFile();
        $this->strLogFile = getConfigs('logPath');
        $this->arrSocketConfig = getConfigs('socket');
        $socket = socket_create($this->arrSocketConfig['domain'], $this->arrSocketConfig['type'], $this->arrSocketConfig['protocol']);
        socket_bind($socket, $this->arrSocketConfig['address'], $this->arrSocketConfig['port']);
        socket_listen($socket);
        $buffer = '';
        while (true) {
            $conn = socket_accept($socket);
            if ($strWordS = socket_read($conn, 1024, PHP_NORMAL_READ)) {
                $arrWords = unserialize($strWordS);
                $strMode = isset($arrWords['mode']) ? $arrWords['mode'] : 't';
                $strWords = isset($arrWords['word']) ? $arrWords['word'] : '';
                $bolMulti = isset($arrWords['multi']) ? $arrWords['multi'] : false;
                if (!$this->check_utf8($strWords)) {
                    $strWords = iconv('gbk', 'utf8', $strWords);
                }
                $arrSendMsg = array(
                    'word' => $strWords,
                    'mode' => $strMode,
                    'multi' => $bolMulti
                );
                self::setFileContent($this->strLogFile . 'pinyin.log.' . date('Y-m-d'), serialize($arrSendMsg) . "\n", FILE_APPEND);
                if ($bolMulti == true) {
                    $strTrans = $this->transMulti($strWords, $strMode);
                    socket_write($conn, serialize($strTrans) . "\n");
                } else {
                    $strTrans = $this->translate($strWords, $strMode);
                    socket_write($conn, $strTrans . "\n");
                }
            }
            socket_close($conn);
        }
    }

    public static function setFileContent($strFilePath, $strContent, $intOption = FILE_BINARY) {
        if (!file_exists($strFilePath)) {
            $strDir = dirname($strFilePath);
            if (!is_dir($strDir)) {
                mkdir($strDir, 0777, true);
            }
        }
        file_put_contents($strFilePath, $strContent, $intOption);
    }

    protected function translate($strWords, $strMode = self::MODE_NORMAL) {
        $arrTrans = $this->getPinyinFromDict($strWords, $strMode);
        return isset($arrTrans[0]) ? $arrTrans[0] : '';
    }

    protected function transMulti($strWords, $strMode = self::MODE_NORMAL) {
        $arrTrans = $this->getPinyinFromDict($strWords, $strMode);
        return $arrTrans;
    }

    const MODE_NORMAL = 'n';
    const MODE_TRIM = 't';
    const MODE_HEAD = 'h';

    protected function getPinyinFromDict($strWords, $strMode = 'n') {
        $strWords = preg_replace('/[^\x{4E00}-\x{9FA5}a-z0-9\,\.\s]/isu', '', preg_replace('/\，|\、/u', ',', preg_replace('/\。/u', '.', $strWords)));
        $arrWords = explode(' ', $strWords); //根据空格断句
        $arrDict = $this->getDictFromCache();
        $arrPy = array();
        foreach ($arrWords as $keyAWord => $valAWord) {
            $arrPinyinT = array();
            for ($i = 0; $i < strlen($valAWord); $i++) {
                for ($n = $this->intMaxPreRead; $n > 0; $n--) {
                    $strKey = substr($valAWord, $i, $n);
                    if (preg_match('/^[a-z0-9\,\.]+$/ui', $strKey)) {
                        $arrPinyinT[] = $strKey;
                        $n = strlen($strKey);
                        break;
                    }
                    if (isset($arrDict[$strKey])) {
                        switch ($strMode) {
                            case self::MODE_NORMAL:
                                $arrPinyinT[] = $arrDict[$strKey];
                                break;
                            case self::MODE_TRIM:
                                $arrPinyinT[] = str_replace(' ', '', $arrDict[$strKey]);
                                break;
                            case self::MODE_HEAD:
                                $arrPinyinT[] = preg_replace('/([a-z])[a-z]+\s*/i', '$1', $arrDict[$strKey]);
                                break;
                            default :
                                $arrPinyinT[] = $arrDict[$strKey];
                                break;
                        }
                        break;
                    }
                }
                $n--;
                $n = ($n < 0) ? 0 : $n;
                $i+=$n;
            }
            $strSplite = $strMode == self::MODE_NORMAL ? ' ' : '';
            $arrPy[] = $this->amazingFoo($arrPinyinT, $strSplite);
        }
        $arrPyFinal = $this->amazingFoo($arrPy, ' ');
        return $arrPyFinal;
    }

    protected function amazingFoo($arrOri, $strSplite = '') {
        $arrPinyin = array();
        foreach ($arrOri as $valAPT) {
            if (empty($arrPinyin)) {
                if (is_array($valAPT)) {
                    $arrPinyin = $valAPT; //拼音
                } else {
                    $arrPinyin = array($valAPT); //字母，标点
                }
                continue;
            }
            $i = 0;
            foreach ($arrPinyin as $keyAPinyin => $valAPinyin) {
                if (is_array($valAPT)) {
                    foreach ($valAPT as $valVAPT) {
                        $arrPinyin[$i] = $valAPinyin . $strSplite . $valVAPT; //拼音
                        if ($i < $this->intMaxCandidate) {
                            $i++;
                        } else {
                            break;
                        }
                    }
                } else {
                    $arrPinyin[$keyAPinyin] = $valAPinyin . ' ' . $valAPT . ' '; //字母，标点
                }
            }
        }
        foreach ($arrPinyin as $keyAPinyin => $valAPinyin) {
            $arrPinyin[$keyAPinyin] = preg_replace('/\s+/', ' ', $valAPinyin);
        }
        return $arrPinyin;
    }

    protected function getDictFromCache($bolNoCache = false) {
        if (isset($this->arrDicts)) {
            return $this->arrDicts;
        }
        $objMem = $this->getMem();
        $this->arrDicts = $objMem->get($this->strDictMemKey);
        if ($bolNoCache) {
            $this->arrDicts = false;
        }
        if (!$this->arrDicts) {
            $this->arrDicts = $this->getDictFromFile();
            $objMem->set($this->strDictMemKey, $this->arrDicts, false, 86400);
        }
        return $this->arrDicts;
    }

    protected function getDictFromFile() {
        if (isset($this->arrDicts)) {
            return $this->arrDicts;
        }
        $arrConfigDict = getConfigs('dict');
        $strDict = '';
        $arrDictTmp = array();
        foreach ($arrConfigDict as $valDictConfig) {
            $strDict .= file_get_contents($valDictConfig) . "\n";
        }
        $arrDictTmp = explode("\n", $strDict);
        $this->arrDicts = array();
        foreach ($arrDictTmp as $valDict) {
            if (empty($valDict)) {
                continue;
            }
            if (!preg_match('/\`/', $valDict)) {
                $valDict = preg_replace('/^([\x{4E00}-\x{9FA5}]+)\s{1}([a-zA-Z\s]+)$/u', "$1`$2", $valDict);
            }
            $arrTmp = explode('`', $valDict);
            if (!empty($arrTmp[1])) {
                $this->arrDicts[$arrTmp[0]][] = $arrTmp[1];
            }
        }
        return $this->arrDicts;
    }

    public function check_utf8($str) {
        $len = strlen($str);
        for ($i = 0; $i < $len; $i++) {
            $c = ord($str[$i]);
            if ($c > 128) {
                if (($c > 247))
                    return false;
                elseif ($c > 239)
                    $bytes = 4;
                elseif ($c > 223)
                    $bytes = 3;
                elseif ($c > 191)
                    $bytes = 2;
                else
                    return false;
                if (($i + $bytes) > $len)
                    return false;
                while ($bytes > 1) {
                    $i++;
                    $b = ord($str[$i]);
                    if ($b < 128 || $b > 191)
                        return false;
                    $bytes--;
                }
            }
        }
        return true;
    }

    protected $objMem;

    protected function getMem() {
        if (!isset($this->objMem)) {
            $this->objMem = new Memcache();
            $arrConfig = getConfigs('memcache');
            $this->objMem->addserver($arrConfig['host'], $arrConfig['port'], $arrConfig['persistent']);
        }
        return $this->objMem;
    }

}
