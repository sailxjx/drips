<?php

/**
 * Document: Stop
 * Created on: 2012-4-16, 16:53:25
 * @author: jxxu
 * GTalk: sailxjx@gmail.com
 */
class Stop extends JobBase {

    protected function main() {
        $sJClass = $this->oCore->getJobClass();
        if (empty($sJClass)) {
            $this->stopAll();
        } else {
            $this->stopOne($sJClass);
        }
    }

    protected function stopAll() {
        
    }

    protected function stopOne() {
        
    }

}
