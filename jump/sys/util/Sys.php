<?php

/**
 * Document: Sys
 * Created on: 2012-5-17, 17:12:10
 * @author: jxxu
 * GTalk: sailxjx@gmail.com
 */
abstract class Util_Sys {

	public static function getPidFileByClass($sCName) {
		if (empty($sCName)) {
			return null;
		}
		return Util::getConfig('PidPath') . $sCName . '.pid';
	}

	public static function stopProcById($iPid) {
		if (posix_kill($iPid, SIGTERM)) {
			Util::logInfo('Stop Process Succ:' . $iPid);
			return true;
		}
		else {
			Util::logInfo('Stop Process Error:' . $iPid);
			return false;
		}
	}

	public static function getProcIdsByClass($sJClass) {
		$sPidFile = self::getPidFileByClass($sJClass);
		$aPids = array();
		if (is_file($sPidFile)) {
			$sPids = Util::getFileCon($sPidFile);
			$aPids = explode(',', $sPids);
		}
		return $aPids;
	}

	public static function hashArgv($argv, $aDCmds = array()) {
		$sClassName = null;
		$aParams = array();
		$aOptions = array();
		$sCmd = null;
		foreach ($argv as $sArgv) {
			if (preg_match('/^--.*?=/i', $sArgv)) {//参数
				$sArgv = str_replace('--', '', $sArgv);
				$sArgv = str_replace('-', '_', $sArgv);
				parse_str($sArgv, $aTmp);
				$aParams = array_merge($aParams, $aTmp);
			}
			elseif (preg_match('/^--?.*/i', $sArgv)) {//选项
				$aOptions[] = $sArgv;
			}
			else {
				if (in_array($sArgv, $aDCmds)) {//默认命令
					$sCmd = $sArgv;
					continue;
				}
				$sClassName = $sArgv;
			}
		}
		return array(
			$sClassName,
			$aParams,
			$aOptions,
			$sCmd
		);
	}

}
