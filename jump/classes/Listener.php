<?php

/**
 * Document: Listener
 * Created on: 2012-4-13, 17:43:03
 * @author: jxxu
 * GTalk: sailxjx@gmail.com
 */
class Listener extends JobBase {

    protected function main() {
        $this->readJList();
        $i = 0;
        while (true) {
            Util::logInfo('listen times:' . $i++);
            sleep(5);
        };
        return true;
    }

    protected $sPidDir = '';
    protected $aPids;
    protected $aJobList = array();

    protected function listen() {
        $aPids = $this->readPids();
        //TODO 监控作业
    }

    protected function readPids() {
        
    }

    protected function readJList() {
        $aCmds = Util::getConfig('cmd', 'List');
        $aJClass = array();
        foreach ($aCmds as $sCmd) {
            $aClass = array();
            preg_match('/^([a-zA-Z]+)|--daemon-num=(\d+)?/i', $sCmd, $aClass);
            print_r($aClass);exit;
            if (!empty($aClass)) {
                $aJClass[] = $aClass;
            }
        }
    }

}
