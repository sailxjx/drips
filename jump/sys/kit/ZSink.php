<?php

/**
 * Document: ZSink
 * Created on: 2012-5-22, 16:59:53
 * @author: jxxu
 * GTalk: sailxjx@gmail.com
 */
class Kit_ZSink extends Base {

	protected $oZSock;
	protected $sZPullConn = 'ipc:///tmp/jump_zsink.ipc';

	protected function main() {
		$this->oZSock = Fac_Mq::getIns()
				->getZMQ()
				->init(ZMQ::SOCKET_PULL)
				->bind($this->sZPullConn);
		$aPidCount = array();
		for ($i = 0; $i < 100000; $i++) {
			$sMsg = $this->oZSock->recv();
			@$aPidCount[$sMsg]++;
		}
		print_r($aPidCount);
		return true;
	}

}
