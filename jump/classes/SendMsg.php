<?php

/**
 * Document: SendMsg
 * Created on: 2012-5-23, 18:21:01
 * @author: jxxu
 * GTalk: sailxjx@gmail.com
 */
class SendMsg extends Base {

	protected function main() {
		for ($i = 0; $i < 10000; $i++) {
			Kit_ZReq::getIns()->setMsg('从前有座山，山上有座庙……')->run();
		}
		return true;
	}

}
