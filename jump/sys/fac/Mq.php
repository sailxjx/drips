<?php

/**
 * Document: Mq
 * Created on: 2012-5-21, 11:31:01
 * @author: jxxu
 * GTalk: sailxjx@gmail.com
 */
class Fac_Mq {

	private static $oIns;

	/**
	 * instance of Fac_Mq
	 * @return Fac_Mq
	 */
	public static function &getIns() {
		if (!isset(self::$oIns)) {
			self::$oIns = new Fac_Mq();
		}
		return self::$oIns;
	}

	/**
	 * get zmq socket
	 * @param ZType $iZType
	 * @return \ZMQSocket
	 */
	public function getZMQ($iZType) {
		$oZCtxt = new ZMQContext();
		$oZSock = new ZMQSocket($oZCtxt, $iZType);
		return $oZSock;
	}

}
