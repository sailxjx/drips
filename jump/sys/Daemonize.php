<?php

/**
 * Document: Daemonize
 * Created on: 2012-4-27, 10:34:50
 * @author: jxxu
 * GTalk: sailxjx@gmail.com
 */
class Daemonize {

	private static $oIns;
	public static $aNoDaemonCmds = array(
		Const_Common::C_KILL,
		Const_Common::C_RESTART,
		Const_Common::C_STOP
	);

	/**
	 * instance of Daemonize
	 * @return Daemonize
	 */
	public static function &getIns() {
		if (!isset(self::$oIns)) {
			self::$oIns = new Daemonize();
		}
		return self::$oIns;
	}

	public function daemon() {
		$oCore = Core::getIns();
		if (in_array($oCore->getCmd(), self::$aNoDaemonCmds)) {
			return false;
		}
		$sPidFile = Util_SysUtil::getPidFileByClass($oCore->getJobClass());
		if (empty($sPidFile)) {
			Util::logInfo('could not find pid file!');
			exit;
		}
		$iDNum = $oCore->getDaemonNum();
		$sPids = Util::getFileCon($sPidFile);
		$aPid = !empty($sPids) ? explode(',', $sPids) : array();
		for ($i = 0; $i < $iDNum; $i++) {
			$iPid = pcntl_fork();
			if ($iPid === -1) {
				Util::logInfo('could not fork');
			}
			elseif ($iPid) {//parent
				$aPid[] = $iPid;
				if ($i < ($iDNum - 1)) {
					continue;
				}
				else {
					Util::setFileCon($sPidFile, implode(',', $aPid));
				}
				exit;
			}
			else {//child
				register_shutdown_function('Daemonize::shutdown');
				chdir('/tmp');
				umask(022);
				// detatch from the controlling terminal
				if (posix_setsid() == -1) {
					Util::logInfo("could not detach from terminal");
					exit;
				}
//				self::ctrlSignal();
				break; //break the parent loop
			}
		}
		return true;
	}

	public static function sigHandler($iSignal) {
		Util::logInfo("catch system signal![{$iSignal}]");
		switch ($iSignal) {
			case SIGTERM:
				exit;
				break;
			case SIGINT:
				exit;
				break;
			default:
				break;
		}
	}

	protected static function ctrlSignal() {
		declare (ticks = 1); //for signal control
		pcntl_signal(SIGTERM, "Daemonize::sigHandler");
		pcntl_signal(SIGINT, "Daemonize::sigHandler");
	}

	/**
	 * 作业结束时删除正常结束的PID文件
	 * @param array $aPidConf
	 * @return boolean
	 */
	public static function shutdown() {
		$iPid = posix_getpid();
		$sPidFile = Util_SysUtil::getPidFileByClass(Core::getIns()->getJobClass());
		if (!is_file($sPidFile)) {
			return false;
		}
		$sPids = file_get_contents($sPidFile);
		$aPids = explode(',', $sPids);
		$aPids = array_diff($aPids, array($iPid));
		if (empty($aPids)) {
			@unlink($sPidFile);
		}
		else {
			file_put_contents($sPidFile, implode(',', $aPids));
		}
		return true;
	}

}
