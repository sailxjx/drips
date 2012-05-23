<?php

/**
 * Document: ZReq
 * Created on: 2012-5-23, 18:09:57
 * @author: jxxu
 * GTalk: sailxjx@gmail.com
 */
class Kit_ZReq extends Base {

	protected $oZSock;
	protected $sZReqDsn = 'ipc:///tmp/jump_req.ipc';
	protected $sMsg = '';

	protected function main() {
		if (!isset($this->oZSock)) {
			$this->oZSock = Fac_Mq::getIns()
					->getZMQ(ZMQ::SOCKET_REQ)
					->connect($this->sZReqDsn);
		}
		$this->oZSock->send($this->sMsg)->recv();
		return true;
	}

	public function setMsg($sMsg = '') {
		$this->sMsg = $sMsg;
		return $this;
	}

}
