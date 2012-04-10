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
		'-l' => 'showChangeLog',
		'--changelog' => 'showChangeLog',
		'--daemon' => 'daemon'
	);
	protected $aMan;
	protected $sJobClass;
	protected $aParams;
	protected $aOptions;
	protected static $oIns;
	protected $sLogFile;
	protected $iDNum;

	/**
	 * instance of JobCore
	 * @return JobCore 
	 */
	public static function getIns() {
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
		unset($argv[0]);
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
			$this->aMan = Util::xmlToArray(Util::getConfig('ManPage'));
		}
		return $this->aMan;
	}

	public function showVersion() {
		$aMan = $this->getMan();
		$sVersion = isset($aMan['version']) ? $aMan['version'] : '';
		print_r(trim($sVersion) . PHP_EOL);
		exit;
	}

	public function showChangeLog() {
		$oXml = simplexml_load_file(Util::getConfig('ManPage'));
		$aChangeLog = json_decode(json_encode($oXml->changelog), true);
		foreach ($oXml->changelog as $oChangeLog) {
			$aAttrs = json_decode(json_encode($oChangeLog), true);
			echo $aAttrs['@attributes']['date'], PHP_EOL;
			echo $aAttrs[0], PHP_EOL;
			echo '========================================================', PHP_EOL;
		}
		exit;
	}

	public function daemon() {
		if (empty($this->sJobClass)) {
			Util::output('Class is not exsit!');
			$this->showHelp();
		}
		$sPidFile = Util::getConfig('PidPath') . $this->sJobClass . '.pid';
		Daemonize::daemon($sPidFile);
	}

	public function showHelp() {
		$aMan = $this->getMan();
		$sHelp = isset($aMan['help']) ? $aMan['help'] : '';
		print_r(trim($sHelp) . PHP_EOL);
		exit;
	}

	public function getDaemonNum() {
		if (!isset($this->iDNum)) {
			$iDNum = 1;
			if (isset($this->aParams['daemon_num'])) {
				$iDNum = intval($this->aParams['daemon_num']);
				if ($iDNum <= 0 || $iDNum > Util::getConfig('MaxDaemonNum')) {
					$iDNum = 1;
				}
			}
			$this->iDNum = $iDNum;
		}
		return $this->iDNum;
	}

	public function getLogFile() {
		if (!isset($this->sLogFile)) {
			if (!isset($this->aParams['log_file'])) {
				$this->sLogFile = Util::getConfig('LogFile');
			} else {
				$this->sLogFile = $this->aParams['log_file'];
			}
		}
		return $this->sLogFile;
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
		
	}

	public function __destruct() {
		$this->trEnd(get_called_class());
	}

	/**
	 * get a new instance
	 * @return JobBase
	 */
	public static function getIns() {
		$sClass = get_called_class();
		if (!isset(self::$oIns)) {
			static::$oIns = new $sClass();
		}
		return static::$oIns;
	}

	public function run() {
		$this->trBegin(get_called_class());
		$this->oCore = JobCore::getIns();
		$this->aParams = $this->oCore->getParams();
		return $this->main();
	}

	abstract public function main();

	protected function trBegin($sAction = 'action') {
		$this->aTS[$sAction] = microtime(true);
		Util::logInfo("{$sAction} --begin");
	}

	protected function trEnd($sAction = 'action') {
		Util::logInfo("{$sAction} --end");
		if (!isset($this->aTS[$sAction])) {
			Util::logInfo("{$sAction}: duration-> No time record");
			return false;
		}
		Util::logInfo("{$sAction}: duration-> " . (microtime(true) - $this->aTS[$sAction]));
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
			include APP_PATH . "config/{$sFile}.inc.php";
			self::$aConfigs = array_merge(self::$aConfigs, $config);
		}
		return isset(self::$aConfigs[$sKey]) ? self::$aConfigs[$sKey] : null;
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

	public static function output($sContent) {
		print_r(date('Y-m-d H:i:s') . ':[' . JobCore::getIns()->getJobClass() . '] ' . $sContent . PHP_EOL);
		return true;
	}

	public static function logInfo($sContent, $sLogFile = null) {
		$sContent = date('Y-m-d H:i:s') . ':[' . JobCore::getIns()->getJobClass() . '] ' . $sContent . PHP_EOL;
		$sLogFile = empty($sLogFile) ? JobCore::getIns()->getLogFile() : $sLogFile;
		self::setFileContent($sLogFile, $sContent, FILE_APPEND);
		return true;
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
		$oCore = JobCore::getIns();
		if (empty($sPidFile)) {
			Util::logInfo('could not find pid file!');
			exit();
		}
		$iDNum = $oCore->getDaemonNum();
		$aPid = array();
		for ($i = 0; $i < $iDNum; $i++) {
			$iPid = pcntl_fork();
			if ($iPid === -1) {
				Util::logInfo('could not fork');
			} elseif ($iPid) {//parent
				$aPid[] = $iPid;
				if ($i < ($iDNum - 1)) {
					continue;
				} else {
					$fp = fopen($sPidFile, 'w');
					fwrite($fp, implode(',', $aPid));
					fclose($fp);
				}
				exit;
			} else {//child
				chdir('/tmp');
				umask(022);
				// detatch from the controlling terminal
				if (posix_setsid() == -1) {
					Util::logInfo("could not detach from terminal");
					exit;
				}
				break; //break the parent loop
			}
		}
		return true;
	}

	public static function sigHandler($signo) {
		switch ($signo) {
			default:
				break;
		}
	}

}
