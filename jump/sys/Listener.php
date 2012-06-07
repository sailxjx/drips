<?php

/**
 * Document: Listener
 * Created on: 2012-4-13, 17:43:03
 * @author: jxxu
 * GTalk: sailxjx@gmail.com
 */
class Listener extends Base {

	protected $aJobList; //初始数据
	protected static $sInitFile = 'var/listen.dat'; //初始数据文件
	protected static $iMaxRetry = 3; //重试次数
	protected static $iSleep = 5; //睡眠间隔
	protected static $iDMinDaemon = 1; //默认最小进程数量，低于此进程时会重启
	protected $iPPid;
	protected $iPid;
	protected $iCPid;

	const K_PARAMS = 'kp';
	const K_CONF_CMD = 'kcc';
	const K_CMD = 'kc';
	const K_OPTIONS = 'ko';
	const K_START_TIME = 'kst';
	const K_RETRY_NUM = 'krn';

	protected function main() {
		$this->readJList();
		$this->daemon();
		$this->listen();
		return true;
	}

	protected function __construct() {
		$this->getProcStatus();
		parent::__construct();
	}

	protected function getProcStatus() {
		$this->iPid = posix_getpid();
		$this->iPPid = posix_getppid();
	}

	/**
	 * read status date of last launcher
	 * @todo how to handle these data?
	 * @return array
	 */
	protected function getInitData() {
		if (!isset($this->aJobList)) {
			$aInitData = Util::getFileCon(APP_PATH . self::$sInitFile);
			if (!empty($aInitData)) {
				$this->aJobList = json_decode(base64_decode($aInitData), true);
			}
			else {
				$this->aJobList = array();
			}
		}
		return $this->aJobList;
	}

	/**
	 * set status data on job exit
	 * @return array
	 */
	protected function setInitData() {
		return Util::setFileCon(APP_PATH . self::$sInitFile, base64_encode(json_encode($this->aJobList)));
	}

	public function __destruct() {
		$this->setInitData();
		parent::__destruct();
	}

	protected function listen() {
		while (1) {
			if (!$this->wParent()) {
				exit;
			}
			$this->wList();
			sleep(self::$iSleep);
		}
	}

	/**
	 * check whether the parent process is exited
	 * @return boolean
	 */
	protected function wParent() {
		return (posix_getppid() != $this->iPPid) ? false : true;
	}

	/**
	 * watch the job list
	 * @return boolean
	 */
	protected function wList() {
		$aJobList = $this->aJobList;
		$iCMaxDNum = Util::getConfig('MaxDaemonNum');
		foreach ($aJobList as $sClass => $aJob) {
			if (empty($sClass)) {
				continue;
			}
			$iNum = Util_SysUtil::getSysProcNumByClass($sClass);
			$iMaxNum = !isset($aJob[self::K_PARAMS][Const_Common::P_DAEMON_NUM]) ? 1 :
					($aJob[self::K_PARAMS][Const_Common::P_DAEMON_NUM] > $iCMaxDNum ? $iCMaxDNum :
							$aJob[self::K_PARAMS][Const_Common::P_DAEMON_NUM]);
			$iMinNum = !isset($aJob[self::K_PARAMS][Const_Common::P_MIN_DAEMON_NUM]) ? self::$iDMinDaemon :
					($aJob[self::K_PARAMS][Const_Common::P_MIN_DAEMON_NUM] > $iMaxNum ? $iMaxNum :
							$aJob[self::K_PARAMS][Const_Common::P_MIN_DAEMON_NUM]);
			if ($iNum >= $iMinNum) {
				continue;
			}
			$iRNum = intval($iMaxNum - $iNum);
			$sDNKey = Util_SysUtil::convParamKeyToArgsKey(Const_Common::P_DAEMON_NUM);
			$sCmd = preg_replace("/{$sDNKey}\=\d+?/i", $sDNKey . '=' . $iRNum, $aJob[self::K_CONF_CMD]);
			$sCmd = APP_PATH . 'launcher.php start ' . $sCmd;
			Util::logInfo($sCmd, APP_PATH . 'log/listen.log');
			Util_SysUtil::runCmd($sCmd);
		}
	}

	protected function readJList() {
		$aCmds = Util::getConfig('cmd', 'List');
		$aJClass = array();
		foreach ($aCmds as $sConfCmd) {
			$aArgvs = explode(' ', $sConfCmd);
			list($sClassName, $aParams, $aOptions, $sCmd) = Util_SysUtil::hashArgv($aArgvs);
			if (!in_array(Const_Common::OL_LISTEN, $aOptions) && !in_array(Const_Common::OS_LISTEN, $aOptions)) {
				continue;
			}
			$aJClass[$sClassName] = array(
				self::K_CONF_CMD => $sConfCmd,
				self::K_CMD => $sCmd,
				self::K_PARAMS => $aParams,
				self::K_OPTIONS => $aOptions,
				self::K_START_TIME => time(),
				self::K_RETRY_NUM => 0
			);
		}
		$this->aJobList = $aJClass;
		return $this->aJobList;
	}

	protected function daemon() {
		$iPid = pcntl_fork();
		$this->iPPid = posix_getppid();
		$this->iPid = posix_getpid();
		if ($iPid === -1) {
			Util::logInfo('could not fork');
		}
		elseif ($iPid) {//parent
			$this->iCPid = $iPid;
			$this->addPid($iPid);
			if ($iCPid = pcntl_waitpid($iPid, $iSt)) {//child process exit
				$this->removePid($iCPid);
				$this->daemon();
			}
		}
		else {//child
		}
		return true;
	}

	protected function addPid($iPid) {
		$sPidFile = Util_SysUtil::getPidFileByClass($this->oCore->getJobClass());
		$sPids = Util::getFileCon($sPidFile);
		$aPids = !empty($sPids) ? explode(',', $sPids) : array();
		$aPids = array_merge($aPids, array($iPid));
		Util::setFileCon($sPidFile, implode(',', $aPids));
		return true;
	}

	protected function removePid($iPid) {
		$sPidFile = Util_SysUtil::getPidFileByClass($this->oCore->getJobClass());
		$sPids = Util::getFileCon($sPidFile);
		if (empty($sPids)) {
			return false;
		}
		$aPids = explode(',', $sPids);
		$aPids = array_diff($aPids, array($iPid));
		if (empty($aPids) && is_file($sPidFile)) {
			@unlink($sPidFile);
			return true;
		}
		Util::setFileCon($sPidFile, implode(',', $aPids));
		return true;
	}

}