<?php

/**
 * Document: Listener
 * Created on: 2012-4-13, 17:43:03
 * @author: jxxu
 * GTalk: sailxjx@gmail.com
 */
class Listener extends JobBase {

    protected function main() {
        $i = 0;
        while (true) {
            Util::logInfo('listen times:' . $i++);
            sleep(5);
        };
        return true;
    }

    protected $sPidDir = '';
    protected $aPids;

    protected function listen() {
        $aPids = $this->readPids();
        //TODO 监控作业
    }

    protected function readPids() {
        
    }

}
