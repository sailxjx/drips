<?php

/**
 * Document: Start
 * Created on: 2012-4-16, 16:52:54
 * @author: jxxu
 * GTalk: sailxjx@gmail.com
 */
class Start extends Base {

	protected function main() {
		$sJClass = $this->oCore->getJobClass();
		if (empty($sJClass)) {
			$this->startAll();
		}
		else {
			$this->startOne($sJClass);
		}
		return true;
	}

	protected function startOne($sJClass) {
		if (!reqClass($sJClass)) {
			Util::output('Class not exsit!');
			$this->oCore->showHelp();
			return false;
		}
		$sJClass::getIns()->run();
	}

	protected function startAll() {
		$aJList = Util::getConfig('cmd', 'List');
		$sCmd = '';
		foreach ($aJList as $sOriCmd) {
			$sCmd = APP_PATH . 'launcher.php start ' . $sOriCmd;
			Util_SysUtil::runCmd($sCmd);
		}
		return true;
	}

}