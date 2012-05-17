<?php

/**
 * Document: Listener
 * Created on: 2012-4-13, 17:43:03
 * @author: jxxu
 * GTalk: sailxjx@gmail.com
 */
class Listener extends Base {

	protected function main() {
		$this->readJList();
		$i = 0;
		while (true) {
			Util::logInfo('listen times:' . $i++);
			sleep(5);
		};
		return true;
	}

	protected $sPidDir = '';
	protected $aPids;
	protected $aJobList = array();

	protected function listen() {
		$aPids = $this->readPids();
		//TODO 监控作业
	}

	protected function readPids() {

	}

	protected function readJList() {
		$aCmds = Util::getConfig('cmd', 'List');
		$aJClass = array();
		foreach ($aCmds as $sCmd) {
			$aArgvs = explode(' ', $sCmd);
			list($sClassName, $aParams, $aOptions, $sCmd) = Util_Sys::hashArgv($aArgvs);
			$aJClass[$sClassName] = array(
				'params' => $aParams,
				'options' => $aOptions
			);
		}
		print_r($aJClass);
		exit;
	}

}
