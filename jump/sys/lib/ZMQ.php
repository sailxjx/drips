<?php

/**
 * Document: ZMQ
 * Created on: 2012-5-21, 11:34:25
 * @author: jxxu
 * GTalk: sailxjx@gmail.com
 */
class Lib_ZMQ {

	protected $oZCon;
	protected $oZSock;

	public function __construct() {

	}

	/**
	 *
	 * @param ZMQType $iSockType
	 * @return \Lib_ZMQ
	 */
	public function init($iSockType) {
		$this->oZCon = new ZMQContext();
		$this->oZSock = new ZMQSocket($this->oZCon, $iSockType);
		return $this;
	}

	/**
	 *
	 * @param string $sConn
	 * @return ZMQSocket
	 */
	public function connect($sConn) {
		$this->oZSock->connect($sConn);
		return $this->oZSock;
	}

	/**
	 *
	 * @param type $sConn
	 * @return ZMQSocket
	 */
	public function bind($sConn) {
		$this->oZSock->bind($sConn);
		return $this->oZSock;
	}

}