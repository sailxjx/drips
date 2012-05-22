<?php

/**
 * Document: ZVent
 * Created on: 2012-5-22, 16:17:34
 * @author: jxxu
 * GTalk: sailxjx@gmail.com
 */
class Kit_ZVent extends Base {

	protected $oZSock;
	protected $sZPushConn = 'ipc:///tmp/jump_zvent.ipc';

	protected function main() {
		$this->oZSock = Fac_Mq::getIns()
				->getZMQ()
				->init(ZMQ::SOCKET_PUSH)
				->bind($this->sZPushConn);
		for ($i = 0; $i < 100000; $i++) {
			$this->oZSock->send($i);
		}
		return true;
	}

}
