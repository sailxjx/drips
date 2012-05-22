<?php

/**
 * Document: Restart
 * Created on: 2012-4-16, 16:53:34
 * @author: jxxu
 * GTalk: sailxjx@gmail.com
 */
class Restart extends Base {

	protected function main() {
		Stop::getIns()->run();
		Start::getIns()->run();
		return true;
	}

}
