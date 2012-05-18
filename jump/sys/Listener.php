<?php

/**
 * Document: Listener
 * Created on: 2012-4-13, 17:43:03
 * @author: jxxu
 * GTalk: sailxjx@gmail.com
 */
class Listener extends Base {

	protected static $aInitData; //初始数据
	protected static $sInitFile = 'var/listen.dat'; //初始数据文件

	protected function main() {
		$this->readJList();
		$i = 0;
		while (true) {
			Util::logInfo('listen times:' . $i++);
			sleep(5);
		};
		return true;
	}

	protected function __construct() {
		$this->getInitData();
		parent::__construct();
	}

	protected function getInitData() {
		if (!isset(self::$aInitData)) {
			$aInitData = Util::getFileCon(APP_PATH . self::$sInitFile);
			if (!empty($aInitData)) {
				self::$aInitData = json_decode(base64_decode($aInitData), true);
			}
			else {
				self::$aInitData = array();
			}
		}
		return self::$aInitData;
	}

	protected function setInitData() {
		return Util::setFileCon(APP_PATH . self::$sInitFile, json_encode(self::$aInitData));
	}

	public function __destruct() {
		$this->setInitData();
		parent::__destruct();
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
				'options' => $aOptions,
				'hehe'=>'呵呵:-)'
			);
		}
		self::$aInitData = $aJClass;
		exit;
	}

}
