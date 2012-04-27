<?php

/**
 * Document: Core
 * Created on: 2012-4-6, 14:48:03
 * @author: jxxu
 * GTalk: sailxjx@gmail.com
 */
final class Core {

	protected $aOptionMaps = array(
		'--help' => 'showHelp',
		'-v' => 'showVersion',
		'--version' => 'showVersion',
		'-d' => 'daemon',
		'-l' => 'showChangeLog',
		'--changelog' => 'showChangeLog',
		'--daemon' => 'daemon'
	);
	protected $aDCmds = array(
		Const_Common::C_START,
		Const_Common::C_STOP,
		Const_Common::C_RESTART,
		Const_Common::C_KILL
	);
	protected $sCmd;
	protected $aMan;
	protected $sJobClass;
	protected $aParams;
	protected $aOptions;
	private static $oIns;
	protected $sLogFile;
	protected $iDNum;

	/**
	 * instance of JobCore
	 * @return Core
	 */
	public static function getIns() {
		if (!self::$oIns) {
			self::$oIns = new Core();
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
	 * @return Core
	 */
	public function init($argv) {
		unset($argv[0]);
		list($this->sJobClass, $this->aParams, $this->aOptions) = $this->hashArgv($argv);
		foreach ($this->aOptionMaps as $sOps => $sFunc) {
			if (in_array($sOps, $this->aOptions)) {
				call_user_func(array(self::$oIns, $sFunc));
			}
		}
		$this->rCmd();
		return self::$oIns;
	}

	/**
	 * 执行不同命令
	 * @todo 执行多条命令？
	 */
	protected function rCmd() {
		if (empty($this->sCmd) || !reqClass($sCmdClass = ucfirst($this->sCmd))) {
			Util::output('Command not found!');
			$this->showHelp();
			return false;
		}
		$sCmdClass::getIns()->run();
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
			}
			elseif (preg_match('/^--?.*/i', $str)) {//选项
				$aOptions[] = $str;
			}
			else {
				if (in_array($str, $this->aDCmds)) {//默认命令
					$this->sCmd = $str;
					continue;
				}
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
			echo @$aAttrs['@attributes']['date'], PHP_EOL;
			echo @$aAttrs[0], PHP_EOL;
			echo '========================================================', PHP_EOL;
		}
		exit;
	}

	public function daemon() {
		if (empty($this->sJobClass)) {
			Util::output('Class is not exsit!');
			$this->showHelp();
		}
		Daemonize::daemon(Util::getPidFileByClass($this->sJobClass));
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
			}
			else {
				$this->sLogFile = $this->aParams['log_file'];
			}
		}
		return $this->sLogFile;
	}

}