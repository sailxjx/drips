<?php

/**
 * Document: ZWorker
 * Created on: 2012-5-21, 10:03:17
 * @author: jxxu
 * GTalk: sailxjx@gmail.com
 */
class Kit_ZWorker extends Base {

	protected $oZPullSock;
	protected $oZPushSock;
	protected $sZPullConn = 'ipc:///tmp/jump_zvent.ipc';
	protected $sZPushConn = 'ipc:///tmp/jump_zsink.ipc';

	protected function main() {
		$this->oZPullSock = Fac_Mq::getIns()
				->getZMQ()
				->init(ZMQ::SOCKET_PULL)
				->connect($this->sZPullConn);
		$this->oZPushSock = Fac_Mq::getIns()
				->getZMQ()
				->init(ZMQ::SOCKET_PUSH)
				->connect($this->sZPushConn);
		while (1) {
			$sMsg = $this->oZPullSock->recv();
			$this->oZPushSock->send(posix_getpid());
		}
		return true;
	}

}