<?php

/**
 * Document: Db
 * Created on: 2012-6-4, 12:45:24
 * @author: jxxu
 * GTalk: sailxjx@gmail.com
 */
class Fac_Db {

	public function __construct() {

	}

	public function __destruct() {
		foreach ($this->aPdos as $sCKey => $oPdo) {
			$this->closePdo($sCKey);
		};
	}

	protected static $oIns;

	/**
	 * instance of factory
	 * @return Fac_Db
	 */
	public static function &getIns() {
		if (!isset(self::$oIns)) {
			self::$oIns = new Fac_Db();
		}
		return self::$oIns;
	}

	protected $aPdos = array();

	/**
	 *
	 * @param string $sCKey
	 * @return PDO
	 * @throws Exception
	 */
	public function loadPdo($sCKey) {
		if (!isset($this->aPdos[$sCKey])) {
			$aConfig = Util::getConfig($sCKey,'Database');
			if (empty($aConfig) || empty($aConfig['dsn']) || empty($aConfig['user']) || empty($aConfig['pwd'])) {
				throw new Exception('error, db config not found');
			}
			$oPdo = new PDO($aConfig['dsn'], $aConfig['user'], $aConfig['pwd'], $aConfig['options']);
			if (!empty($aConfig['statments'])) {
				foreach ($aConfig['statments'] as $sStmt) {
					$oPdo->exec($sStmt);
				}
			}
			$this->aPdos[$sCKey] = $oPdo;
		}
		return $this->aPdos[$sCKey];
	}

	public function closePdo($sCKey) {
		unset($this->aPdos[$sCKey]);
	}

}
