<?php

/**
 * Document: Restart
 * Created on: 2012-4-16, 16:53:34
 * @author: jxxu
 * GTalk: sailxjx@gmail.com
 */
class Restart extends Base {

	protected function main() {
		$this->stop();
		$this->start();
		return true;
	}

	protected function stop() {
		Stop::getIns()->run();
		return true;
	}

	protected function start() {
		$this->oCore->setCmd(Const_Common::C_START);
		$aOptions = $this->oCore->getOptions();
		$aDaemons = array_intersect($aOptions, array(Const_Common::OL_DAEMON, Const_Common::OS_DAEMON));
		if (!empty($aDaemons)) {
			$this->oCore->daemon();
		}
		Start::getIns()->run();
		return true;
	}

}
