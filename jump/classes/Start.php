<?php

/**
 * Document: Start
 * Created on: 2012-4-16, 16:52:54
 * @author: jxxu
 * GTalk: sailxjx@gmail.com
 */
class Start extends JobBase {

    protected function main() {
        $sJobClass = $this->oCore->getJobClass();
        if (empty($sJobClass)) {
            $this->startAll();
        } else {
            $this->startOne($sJobClass);
        }
    }

    protected function startOne($sJobClass) {
        if (!reqClass($sJobClass)) {
            Util::output('Class not exsit!');
            $this->oCore->showHelp();
        }
        $sJobClass::getIns()->run();
    }

    protected function startAll() {
        $aJobList = Util::getConfig('cmd', 'List');
        $sCmd = '';
        foreach ($aJobList as $sOriCmd) {
            $sCmd = APP_PATH . 'launcher.php start ' . $sOriCmd;
            $this->startJob($sCmd);
        }
        return true;
    }

    protected function startJob($sCmd, $sMode = 'r') {
        if (empty($sCmd)) {
            return false;
        }
        if ($rProc = popen($sCmd, $sMode)) {
            pclose($rProc);
            Util::logInfo('Start -> ', $sCmd);
            return true;
        } else {
            Util::logInfo('StartError -> ', $sCmd);
            return false;
        }
    }

}