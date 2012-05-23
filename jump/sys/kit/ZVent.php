<?php

/**
 * Document: ZVent
 * Created on: 2012-5-22, 16:17:34
 * @author: jxxu
 * GTalk: sailxjx@gmail.com
 */
class Kit_ZVent extends Base {

	protected $oZSockPush;
	protected $oZSockRep;
	protected $sZPushDsn = 'ipc:///tmp/jump_zvent.ipc';
	protected $sZRepDsn = 'ipc:///tmp/jump_req.ipc';

	protected function main() {
		$this->oZSockPush = Fac_Mq::getIns()
				->getZMQ(ZMQ::SOCKET_PUSH)
				->bind($this->sZPushDsn);
		$this->oZSockRep = Fac_Mq::getIns()
				->getZMQ(ZMQ::SOCKET_REP)
				->bind($this->sZRepDsn);
		while ($sMsg = $this->oZSockRep->recv()) {
			$this->oZSockRep->send('recv!');
			$this->oZSockPush->send($sMsg);
		}
		return true;
	}

}
