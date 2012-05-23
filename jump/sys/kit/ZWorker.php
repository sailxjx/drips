<?php

/**
 * Document: ZWorker
 * Created on: 2012-5-21, 10:03:17
 * @author: jxxu
 * GTalk: sailxjx@gmail.com
 */
class Kit_ZWorker extends Base {

	/**
	 *
	 * @var ZMQSocket
	 */
	protected $oZPullSock;
	protected $oZPushSock;
	protected $sZPullDsn = 'ipc:///tmp/jump_zvent.ipc';
	protected $sZPushDsn = 'ipc:///tmp/jump_zsink.ipc';

	protected function main() {
		$this->oZPullSock = Fac_Mq::getIns()
				->getZMQ(ZMQ::SOCKET_PULL)
				->connect($this->sZPullDsn);
		$this->oZPushSock = Fac_Mq::getIns()
				->getZMQ(ZMQ::SOCKET_PUSH)
				->connect($this->sZPushDsn);
		$iPid = posix_getpid();
		while (1) {
			try {
				$sMsg = $this->oZPullSock->recv();
				Util::logInfo($iPid . '->' . $sMsg);
				usleep(10);
			}
			catch (Exception $exc) {
				echo '出错啦～～～～～～～' . PHP_EOL;
			}
//			try {
//				$this->oZPushSock->send(posix_getpid());
//			}
//			catch (Exception $exc) {
//				echo '又出错啦～～～～～～～' . PHP_EOL;
//				echo $exc->getTraceAsString();
//			}
		}
		return true;
	}

}