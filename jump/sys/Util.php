<?php

/**
 * Document: Util
 * Created on: 2012-4-27, 10:34:10
 * @author: jxxu
 * GTalk: sailxjx@gmail.com
 */
abstract class Util {

	protected static $aConfigs = array();
	protected static $bQuiet;

	/**
	 * 读取配置文件
	 * @param string $sKey
	 * @param string $sFile
	 * @return mix
	 */
	public static function getConfig($sKey, $sFile = 'Common') {
		if (!isset(self::$aConfigs[$sFile])) {
			$sRealFile = APP_PATH . "config/{$sFile}.inc.php";
			if (is_file($sRealFile)) {
				include $sRealFile;
				self::$aConfigs[$sFile] = $config;
			}
			else {
				return null;
			}
		}
		return isset(self::$aConfigs[$sFile][$sKey]) ? self::$aConfigs[$sFile][$sKey] : null;
	}

	public static function xmlToArray($sXmlFile) {
		$oSXml = simplexml_load_file($sXmlFile);
		return json_decode(json_encode($oSXml), true);
	}

	public static function objToArray($obj) {
		$arr = array();
		foreach ((array) $obj as $sKey => $mVal) {
			if (is_object($mVal)) {
				$arr[$sKey] = self::objToArray($mVal);
			}
			else {
				$arr[$sKey] = $mVal;
			}
		}
		return $arr;
	}

	public static function getFileCon($sFile, $sSetContent = '') {
		if (file_exists($sFile)) {
			return file_get_contents($sFile);
		}
		else {
			if (!empty($sSetContent)) {
				$sDir = dirname($sFile);
				if (!is_dir($sDir)) {
					mkdir($sDir, 0777, true);
				}
				file_put_contents($sFile, $sSetContent);
			}
			return '';
		}
	}

	public static function setFileCon($sFile, $sContent, $iOption = FILE_BINARY) {
		if (!file_exists($sFile)) {
			$sDir = dirname($sFile);
			if (!is_dir($sDir)) {
				mkdir($sDir, 0777, true);
			}
		}
		return file_put_contents($sFile, $sContent, $iOption);
	}

	public static function output($mCon) {
		if (!isset(static::$bQuiet)) {
			$aOptions = Core::getIns()->getOptions();
			$aQuiet = array_intersect($aOptions, array(Const_Common::OL_QUIET, Const_Common::OS_QUIET));
			if (empty($aQuiet)) {
				static::$bQuiet = false;
			}
			else {
				static::$bQuiet = true;
			}
		}
		if (static::$bQuiet == false) {
			echo date('Y-m-d H:i:s'), ':[', Core::getIns()->getJobClass(), '] ', var_export($mCon, true), PHP_EOL;
		}
		else {
			self::logInfo($mCon);
		}
		return true;
	}

	public static function logInfo($mCon, $sLogFile = null) {
		$sCon = date('Y-m-d H:i:s') . ':[' . Core::getIns()->getJobClass() . '] ' . var_export($mCon, true) . PHP_EOL;
		$sLogFile = empty($sLogFile) ? Core::getIns()->getLogFile() : $sLogFile;
		self::setFileCon($sLogFile, $sCon, FILE_APPEND);
		return true;
	}

	public static function report($iCode = 0, $sMsg = '') {
		//@todo error report
	}

}
