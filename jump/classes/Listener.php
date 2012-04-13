<?php

/**
 * Document: Listener
 * Created on: 2012-4-13, 17:43:03
 * @author: jxxu
 * GTalk: sailxjx@gmail.com
 */
class Listener extends JobBase {

	public function main() {
		while (true) {
			$this->listen();
		};
		return true;
	}

	protected $sPidDir = '';
	protected $aPids;

	protected function listen() {
		$aPids = $this->readPids();
		//TODO 监控作业
	}

	protected function readPids() {
		
	}

}
