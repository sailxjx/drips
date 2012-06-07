<?php

/**
 * Document: Stop
 * Stop all jobs or stop by name
 * Created on: 2012-4-16, 16:53:25
 * @author: jxxu
 * GTalk: sailxjx@gmail.com
 */
class Stop extends Base {

	protected function main() {
		$sJClass = $this->oCore->getJobClass();
		if (empty($sJClass)) {
			$this->stopAll();
		}
		else {
			$this->stopOne($sJClass);
		}
		return true;
	}

	protected function stopAll() {
		$aJList = Util::getConfig('cmd', 'List');
		$aCList = array(); //Class list
		foreach ((array) $aJList as $sOCmd) {
			$aOCmd = explode(' ', trim($sOCmd));
			$aCList[] = !empty($aOCmd[0]) ? $aOCmd[0] : null;
		}
		$aCList = array_filter(array_unique($aCList));
		foreach ((array) $aCList as $sJClass) {
			$this->stopOne($sJClass);
		}
		return true;
	}

	protected function stopOne($sJClass) {
		if (!reqClass($sJClass)) {
			Util::output('Class not exsit!');
			$this->oCore->showHelp();
			return false;
		}
		$aPids = Util_SysUtil::getProcIdsByClass($sJClass);
		$sPidFile = Util_SysUtil::getPidFileByClass($sJClass);
		$this->stopProcByIds($aPids, $sPidFile);
		return true;
	}

	protected function stopProcByIds($aPids, $sPidFile) {
		$iMyPid = posix_getpid();
		foreach ($aPids as $iPid) {
			if ($iMyPid == $iPid) {//if this function is called by a restart command, it will not be killed
				continue;
			}
			if (Util_SysUtil::stopProcById($iPid)) {
				$aPids = array_diff($aPids, array($iPid)); //del process id from pid file
			}
			else {
				Util::report();
			}
		}
		if (empty($aPids) && is_file($sPidFile)) {
			@unlink($sPidFile);
			return true;
		}
		Util::setFileCon($sPidFile, implode(',', $aPids));
		return false;
	}

}