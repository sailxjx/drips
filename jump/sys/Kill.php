<?php

/**
 * Document: Kill
 * Kill a process by id
 * Created on: 2012-4-17, 17:02:00
 * @author: jxxu
 * GTalk: sailxjx@gmail.com
 */
class Kill extends Base {

	protected function main() {
		$this->doKill();
		return true;
	}

	protected function doKill() {
		$sJClass = $this->oCore->getJobClass();
		if (empty($sJClass) || !reqClass($sJClass)) {
			Util::output('Class not exsit!');
			$this->oCore->showHelp();
			return false;
		}
		$aParams = $this->oCore->getParams();
		if (!empty($aParams['proc_id'])) {
			$aPids = explode(',', $aParams['proc_id']);
		}
		if (empty($aPids)) {
			Util::output('No process ids!');
			$this->oCore->showHelp();
			return false;
		}
		$aOriPids = Util_Sys::getProcIdsByClass($sJClass);
		$sPidFile = Util_Sys::getPidFileByClass($sJClass);
		$aPids = array_intersect($aOriPids, $aPids);
		if (empty($aPids)) {
			Util::output('Process id error');
			return false;
		}
		foreach ($aPids as $iPid) {
			if (Util_Sys::stopProcById($iPid)) {
				$aOriPids = array_diff($aOriPids, array($iPid));
			}
			else {
				Util::report();
			}
		}
		if (empty($aOriPids)) {
			if (file_exists($sPidFile)) {
				unlink($sPidFile);
			}
		}
		else {
			Util::setFileCon($sPidFile, implode(',', $aOriPids));
		}
		return true;
	}

}
