<?php

/**
 * Document: Start
 * Created on: 2012-4-16, 16:52:54
 * @author: jxxu
 * GTalk: sailxjx@gmail.com
 */
class Start extends JobBase {

    protected function main() {
        $sJClass = $this->oCore->getJobClass();
        if (empty($sJClass)) {
            $this->startAll();
        } else {
            $this->startOne($sJClass);
        }
        return true;
    }

    protected function startOne($sJClass) {
        if (!reqClass($sJClass)) {
            Util::output('Class not exsit!');
            $this->oCore->showHelp();
            return false;
        }
        $sJClass::getIns()->run();
    }

    protected function startAll() {
        $aJList = Util::getConfig('cmd', 'List');
        $sCmd = '';
        foreach ($aJList as $sOriCmd) {
            $sCmd = APP_PATH . 'launcher.php start ' . $sOriCmd;
            $this->startJob($sCmd);
        }
        return true;
    }

    /**
     * @todo 使用Daemon
     * @param type $sCmd
     * @param type $sMode
     * @return boolean 
     */
    protected function startJob($sCmd, $sMode = 'w') {
        if (empty($sCmd)) {
            return false;
        }
        if ($rProc = popen($sCmd, $sMode)) {
            pclose($rProc);
            Util::logInfo('Start -> ' . $sCmd);
            return true;
        } else {
            Util::logInfo('StartError -> ' . $sCmd);
            return false;
        }
    }

}