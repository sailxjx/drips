<?php

/**
 * Document: HelloWorld
 * Created on: 2012-4-6, 14:14:53
 * @author: jxxu
 * GTalk: sailxjx@gmail.com
 */
class HelloWorld extends JobBase {

	public function run() {
		Util::output('HelloWorld');
		for ($i = 0; $i < 7; $i++) {
			print_r($this->aParams);
			sleep(3);
		}
		exit;
	}

}
