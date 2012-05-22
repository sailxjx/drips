<?php

/**
 * Document: Core
 * Created on: 2012-4-6, 14:48:03
 * @author: jxxu
 * GTalk: sailxjx@gmail.com
 */
final class Core {

	protected $aOptionMaps = array(
		Const_Common::OL_HELP => 'showHelp',
		Const_Common::OS_VERSION => 'showVersion',
		Const_Common::OL_VERSION => 'showVersion',
		Const_Common::OS_LOG => 'showChangeLog',
		Const_Common::OL_LOG => 'showChangeLog',
		Const_Common::OS_DAEMON => 'daemon',
		Const_Common::OL_DAEMON => 'daemon'
	);
	protected $aDCmds = array(
		Const_Common::C_START,
		Const_Common::C_STOP,
		Const_Common::C_RESTART,
		Const_Common::C_KILL
	);
	protected $sCmd;
	protected $aMan; //手册内容
	protected $sJobClass;
	protected $aParams;
	protected $aOptions;
	private static $oIns;
	protected $sLogFile;
	protected $iDNum; //Deamon进程个数

	/**
	 * instance of JobCore
	 * @return Core
	 */

	public static function &getIns() {
		if (!self::$oIns) {
			self::$oIns = new Core();
		}
		return self::$oIns;
	}

	/**
	 * get current job class name
	 * @return string
	 */
	public function getJobClass() {
		return $this->sJobClass;
	}

	/**
	 * get current params
	 * @return array
	 */
	public function getParams() {
		return $this->aParams;
	}

	/**
	 * get current options
	 * @return array
	 */
	public function getOptions() {
		return $this->aOptions;
	}

	/**
	 * get current command
	 * @return string
	 */
	public function getCmd() {
		return $this->sCmd;
	}

	/**
	 * init of JobCore
	 * @return Core
	 */
	public function init($argv) {
		unset($argv[0]);
		list($this->sJobClass, $this->aParams, $this->aOptions, $this->sCmd) = Util_Sys::hashArgv($argv, $this->aDCmds);
		return self::$oIns;
	}

	/**
	 * run job
	 * @return Core
	 */
	public function run() {
		Hook::getIns()->pre();
		foreach ($this->aOptionMaps as $sOps => $sFunc) {
			if (in_array($sOps, $this->aOptions) && method_exists(self::$oIns, $sFunc)) {
				call_user_func(array(self::$oIns, $sFunc));
			}
		}
		$this->rCmd();
		Hook::getIns()->post();
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
		Daemonize::getIns()->daemon();
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
			if (isset($this->aParams[Const_Common::P_DAEMON_NUM])) {
				$iDNum = intval($this->aParams[Const_Common::P_DAEMON_NUM]);
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
			if (!isset($this->aParams[Const_Common::P_LOG_FILE])) {
				$this->sLogFile = Util::getConfig('LogFile');
			}
			else {
				$this->sLogFile = $this->aParams[Const_Common::P_LOG_FILE];
			}
		}
		return $this->sLogFile;
	}

}