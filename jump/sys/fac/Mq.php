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

	protected $oZMQ;

	/**
	 * return zmq
	 * @return Lib_ZMQ
	 */
	public function getZMQ() {
		if (!isset($this->oZMQ)) {
			$this->oZMQ = new Lib_ZMQ();
		}
		return $this->oZMQ;
	}

}
