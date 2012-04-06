<?php

/**
 * Document: JobCore
 * Created on: 2012-4-6, 14:48:03
 * @author: jxxu
 * GTalk: sailxjx@gmail.com
 */
final class JobCore {

	protected $aOptionMaps = array(
		'--help' => 'showHelp',
		'-v' => 'showVersion',
		'--version' => 'showVersion',
		'-d' => 'daemon',
		'--daemon' => 'daemon'
	);
	protected $aMan;
	protected $sJobClass;
	protected $aParams;
	protected $aOptions;
	protected static $oIns;

	/**
	 * instance of JobCore
	 * @return JobCore 
	 */
	public static function instance() {
		if (!self::$oIns) {
			self::$oIns = new JobCore();
		}
		return self::$oIns;
	}

	public function getJobClass() {
		return $this->sJobClass;
	}

	public function getParams() {
		return $this->aParams;
	}

	public function getOptions() {
		return $this->aOptions;
	}

	/**
	 * init of JobCore
	 * @return JobCore
	 */
	public function init($argv) {
		list($this->sJobClass, $this->aParams, $this->aOptions) = $this->hashArgv($argv);
		foreach ($this->aOptionMaps as $sOps => $sFunc) {
			if (in_array($sOps, $this->aOptions)) {
				call_user_func(array(self::$oIns, $sFunc));
			}
		}
		return self::$oIns;
	}

	protected function hashArgv($argv) {
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

	public function getMan() {
		if (!isset($this->aMan)) {
			$this->aMan = Util::xmlToArray(APP_PATH . 'man/man.xml');
		}
		return $this->aMan;
	}

	public function showVersion() {
		$aMan = $this->getMan();
		$sVersion = isset($aMan['version']) ? $aMan['version'] : '';
		print_r($sVersion);
		exit;
	}

	public function daemon() {
		$sPidFile = APP_PATH . 'var/' . $this->sJobClass . '.pid';
		Daemonize::daemon($sPidFile);
	}

	public function showHelp() {
		$aMan = $this->getMan();
		$sHelp = isset($aMan['help']) ? $aMan['help'] : '';
		print_r($sHelp);
		exit;
	}

}

/**
 * Document: JobBase
 * Created on: 2012-4-6, 11:20:43
 * @author: jxxu
 * GTalk: sailxjx@gmail.com
 */
abstract class JobBase {

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

	/**
	 *
	 * @var JobCore
	 */
	protected $oCore;

	protected function __construct() {
		$this->trBegin(get_called_class());
		$this->oCore = JobCore::instance();
		$this->aParams = $this->oCore->getParams();
	}

	public function __destruct() {
		$this->trEnd(get_called_class());
	}

	/**
	 * get a new instance
	 * @return JobBase
	 */
	public static function instance() {
		$sClass = get_called_class();
		if (!isset(self::$oIns)) {
			static::$oIns = new $sClass();
		}
		return static::$oIns;
	}

	abstract public function run();

	protected function trBegin($sAction = 'action') {
		$this->aTS[$sAction] = microtime(true);
		Util::output("{$sAction} --begin");
	}

	protected function trEnd($sAction = 'action') {
		Util::output("{$sAction} --end");
		if (!isset($this->aTS[$sAction])) {
			Util::output("{$sAction}: duration-> No time record");
			return false;
		}
		Util::output("{$sAction}: duration-> " . (microtime(true) - $this->aTS[$sAction]));
		unset($this->aTS[$sAction]);
		return true;
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

	public static function daemon($sPidFile) {
		if (empty($sPidFile)) {
			Util::output('could not find pid file!');
			exit();
		}
		$iPid = pcntl_fork();
		if ($iPid === -1) {
			Util::output('could not fork');
		} elseif ($iPid) {
			$fp = fopen($sPidFile, 'w');
			fwrite($fp, $iPid);
			fclose($fp);
			exit;
		}
		//child process
		// detatch from the controlling terminal
		if (posix_setsid() == -1) {
			Util::output("could not detach from terminal");
			exit;
		}
	}

	public static function sig_handler($signo) {
		switch ($signo) {
			default:
				break;
		}
	}

}
