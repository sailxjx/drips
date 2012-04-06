<?php

/**
 * Document: Base
 * Created on: 2012-4-6, 11:20:43
 * @author: jxxu
 * GTalk: sailxjx@gmail.com
 */
abstract class Base {

    /**
     * record timestamp
     * @var array
     */
    protected $aTS;

    /**
     * params
     * @var array 
     */
    protected $aParams;

    /**
     * instance
     * @var object
     */
    protected static $oIns;

    protected function __construct() {
        
    }

    /**
     * get a new instance
     * @return Base
     */
    public static function instance() {
        $sClass = get_called_class();
        if (!isset(self::$oIns)) {
            static::$oIns = new $sClass();
        }
        return static::$oIns;
    }

    protected function hashOptions($argv) {
        print_r($argv);
        exit;
    }

    public function run($aParams) {
        $this->aParams = $aParams;
        return $this->main();
    }

    abstract public function main();

    protected function trBegin($sAction = 'action') {
        $this->aTS[$sAction] = microtime(true);
        Util::output($sAction . ' --begin');
    }

    protected function trEnd($sAction = 'action') {
        Util::output('"' . $sAction . '" --end');
        if (!isset($this->aTS[$sAction])) {
            Util::output('"' . $sAction . '": duration-> No time record');
            return false;
        }
        Util::output('"' . $sAction . '": duration-> ' . (microtime(true) - $this->aTS[$sAction]));
        unset($this->aTS[$sAction]);
        return true;
    }

}

/**
 * Document: Options
 * Created on: 2012-4-6, 14:48:03
 * @author: jxxu
 * GTalk: sailxjx@gmail.com
 */
abstract class Options {

    protected static $aOptions = array(
        '--help' => 'showHelp',
        '-v' => 'showVersion',
        '--version' => 'showVersion',
        '-d' => 'daemon',
        '--daemon' => 'daemon'
    );
    protected static $aMan;

    public static function hashOptions($argv) {
        foreach (self::$aOptions as $sOps => $sFunc) {
            if (in_array($sOps, $argv)) {
                self::$sFunc();
            }
        }
        return self::getParams($argv);
    }

    public static function getParams($argv) {
        $sClassName = null;
        $aParams = array();
        $aOptions = array();
        foreach ($argv as $str) {
            if (preg_match('/^--.*?=/i', $str)) {//参数
                $str = str_replace('--', '', $str);
                $str = str_replace('-', '_', $str);
                parse_str($str, $aTmp);
                $aParams = array_merge($aParams, $aTmp);
            } elseif (preg_match('/^--?.*/i', $str)) {//选项
                $aOptions[] = $str;
            } else {
                $sClassName = $str;
            }
        }
        return array(
            $sClassName,
            $aParams,
            $aOptions
        );
    }

    public static function getMan() {
        if (!isset(self::$aMan)) {
            self::$aMan = Util::xmlToArray(APP_PATH . 'man/man.xml');
        }
        return self::$aMan;
    }

    public static function showVersion() {
        $aMan = self::getMan();
        $sVersion = isset($aMan['version']) ? $aMan['version'] : '';
        print_r($sVersion);
        exit;
    }

    public static function daemon() {
        Daemonize::daemon();
    }

    public static function showHelp() {
        $aMan = self::getMan();
        $sHelp = isset($aMan['help']) ? $aMan['help'] : '';
        print_r($sHelp);
        exit;
    }

}

/**
 * Document: Util
 * Created on: 2012-4-6, 11:41:21
 * @author: jxxu
 * GTalk: sailxjx@gmail.com
 */
abstract class Util {

    protected static $aConfigs = array();

    public static function getConfig($sKey, $sFile = 'Common') {
        if (!isset(self::$aConfigs[$sKey])) {
            include APP_PATH . "config/{$sFile}.php";
            self::$aConfigs = array_merge(self::$aConfigs, $config);
        }
        return isset(self::$aConfigs[$sKey]) ? self::$aConfigs[$sKey] : null;
    }

    public static function logInfo() {
        
    }

    public static function xmlToArray($sXmlFile) {
        $oSXml = simplexml_load_file($sXmlFile);
        return json_decode(json_encode($oSXml), true);
    }

    public static function objToArray($obj) {
        $arr = array();
        foreach ((array) $obj as $sKey => $mVal) {
            if (is_object($mVal)) {
                $arr[$sKey] = self::objToArray($mVal);
            } else {
                $arr[$sKey] = $mVal;
            }
        }
        return $arr;
    }

    public static function getFileContent($sFile, $sSetContent = '') {
        if (file_exists($sFile)) {
            return file_get_contents($sFile);
        } else {
            $sDir = dirname($sFile);
            if (!is_dir($sDir)) {
                mkdir($sDir, 0777, true);
            }
            file_put_contents($sFile, $sSetContent);
            return '';
        }
    }

    public static function setFileContent($sFile, $sContent, $iOption = FILE_BINARY) {
        if (!file_exists($sFile)) {
            $sDir = dirname($sFile);
            if (!is_dir($sDir)) {
                mkdir($sDir, 0777, true);
            }
        }
        return file_put_contents($sFile, $sContent, $iOption);
    }

    public static function output($str) {
        print_r(date('Y-m-d H:i:s') . ': ' . $str . PHP_EOL);
    }

}

/**
 * Document: Daemonize
 * Created on: 2012-4-6, 11:40:09
 * @author: jxxu
 * GTalk: sailxjx@gmail.com
 */
abstract class Daemonize {

    public static function daemon($pidfile) {
        if (empty($pidfile)) {
            exit('could not find pid file');
        }
        $pid = pcntl_fork();
        if ($pid === -1) {
            exit('could not fork');
        } elseif ($pid) {
            //parent process
            $fp = fopen($pidfile, 'w');
            fwrite($fp, $pid);
            fclose($fp);
            usleep(1000);
            exit;
        }
        //child process
        chdir("/tmp");
        umask(022);

        // detatch from the controlling terminal
        if (posix_setsid() == -1) {
            die("could not detach from terminal");
        }

        $pid = pcntl_fork();
        if ($pid === -1) {
            die("folk 2nd false");
        } elseif ($pid) {
            $fp = fopen($pidfile, 'w');
            fwrite($fp, $pid);
            fclose($fp);
            usleep(1000);
            exit;
        }
        // setup signal handlers
        pcntl_signal(SIGTERM, "self::sig_handler");
        pcntl_signal(SIGHUP, "self::sig_handler");


        if (defined('STDIN')) {
            fclose(STDIN);
        }
        if (defined('STDOUT')) {
            fclose(STDOUT);
        }
        if (defined('STDERR')) {
            fclose(STDERR);
        }
    }

    public static function sig_handler($signo) {
        switch ($signo) {
            case SIGTERM:
                // handle shutdown tasks
                Util::log_info("process terminated");
                exit;
                break;
            case SIGHUP:
                // handle restart tasks
                $oRedis = OrderRedis::getRedis();
                $oPdo = OrderPdo::getPdo();
                try {
                    $oRedis->close();
                } catch (Exception $exc) {
                    
                }
                $oRedis->tryConn();
                $oPdo->tryConn();
                break;
            default:
                break;
        }
    }

}
