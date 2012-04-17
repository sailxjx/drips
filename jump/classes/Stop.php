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
        return true;
    }

    protected function stopAll() {
        $aJList = Util::getConfig('cmd', 'List');
        $aCList = array(); //Class list
        foreach ((array) $aJList as $sOCmd) {
            $aOCmd = explode(' ', trim($sOCmd));
            $aCList[] = !empty($aOCmd[0]) ? $aOCmd[0] : null;
        }
        $aCList = array_filter(array_unique($aCList));
        foreach ((array) $aCList as $sJClass) {
            $this->stopOne($sJClass);
        }
        return true;
    }

    protected function stopOne($sJClass) {
        if (!reqClass($sJClass)) {
            Util::output('Class not exsit!');
            $this->oCore->showHelp();
            return false;
        }
        $aPids = Util::getProcIdsByClass($sJClass);
        $sPidFile = Util::getPidFileByClass($sJClass);
        $this->stopProcByIds($aPids, $sPidFile);
        return true;
    }

    protected function stopProcByIds($aPids, $sPidFile) {
        foreach ($aPids as $iPid) {
            if (Util::stopProcById($iPid)) {
                $aPids = array_diff($aPids, array($iPid)); //del process id from pid file
            } else {
                Util::report();
            }
        }
        if (empty($aPids)) {
            if (file_exists($sPidFile)) {
                unlink($sPidFile);
            }
            return true;
        } else {
            Util::setFileCon($sPidFile, implode(',', $aPids));
            return false;
        }
    }

}