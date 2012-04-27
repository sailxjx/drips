<?php

/**
 * Document: Daemonize
 * Created on: 2012-4-27, 10:34:50
 * @author: jxxu
 * GTalk: sailxjx@gmail.com
 */
abstract class Daemonize {

	public static function daemon($sPidFile) {
		$oCore = Core::getIns();
		if (empty($sPidFile)) {
			Util::logInfo('could not find pid file!');
			exit();
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
				register_shutdown_function('Daemonize::shutdown', array(
					'pid' => posix_getpid(),
					'pidfile' => $sPidFile
				));
				chdir('/tmp');
				umask(022);
				// detatch from the controlling terminal
				if (posix_setsid() == -1) {
					Util::logInfo("could not detach from terminal");
					exit;
				}
//                pcntl_signal(SIGTERM, "Daemonize::sigHandler");
				break; //break the parent loop
			}
		}
		return true;
	}

	public static function sigHandler($iSigno) {
		switch ($iSigno) {
			case SIGTERM:
				exit;
				break;
			default:
				break;
		}
	}

	/**
	 * 作业结束时删除正常结束的PID文件
	 * @param array $aPidConf
	 * @return boolean
	 */
	public static function shutdown($aPidConf) {
		$iPid = $aPidConf['pid'];
		$sPidFile = $aPidConf['pidfile'];
		if (!is_file($sPidFile)) {
			return false;
		}
		$sPids = file_get_contents($sPidFile);
		$aPids = explode(',', $sPids);
		$aPids = array_diff($aPids, array($iPid));
		if (empty($aPids)) {
			unlink($sPidFile);
		}
		else {
			file_put_contents($sPidFile, implode(',', $aPids));
		}
		return true;
	}

}
