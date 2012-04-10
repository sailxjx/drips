<?php

/**
 * Document: HelloWorld
 * Created on: 2012-4-6, 14:14:53
 * @author: jxxu
 * GTalk: sailxjx@gmail.com
 */
class HelloWorld extends JobBase {

    public function main() {
        Util::logInfo('HelloWorld');
        for ($i = 0; $i < 7; $i++) {
            Util::logInfo(var_export($this->aParams, true));
            sleep(3);
        }
        exit;
    }

}
