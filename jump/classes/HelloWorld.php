<?php

/**
 * Document: HelloWorld
 * Created on: 2012-4-6, 14:14:53
 * @author: jxxu
 * GTalk: sailxjx@gmail.com
 */
class HelloWorld extends Base {

	protected function main() {
		Util::logInfo('HelloWorld');
		for ($i = 0; $i < 17; $i++) {
			Util::output($this->aParams);
			sleep(3);
		}
	}

}
