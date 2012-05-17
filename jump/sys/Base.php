<?php

/**
 * Document: Base
 * Created on: 2012-4-27, 10:35:44
 * @author: jxxu
 * GTalk: sailxjx@gmail.com
 */
abstract class Base {

	/**
	 * record timestamp
	 * @var array
	 */
	protected $aTS;

	/**
	 * params
	 * @var array
	 */
	protected $aParams;

	/**
	 * instance
	 * @var array
	 */
	private static $aIns;

	/**
	 *
	 * @var Core
	 */
	protected $oCore;

	protected function __construct() {
		$this->trBegin(get_called_class());
	}

	public function __destruct() {
		$this->trEnd(get_called_class());
	}

	/**
	 * get a new instance
	 * @return Base
	 */
	public static function &getIns() {
		$sClass = get_called_class();
		if (!isset(self::$aIns[$sClass])) {
			self::$aIns[$sClass] = new $sClass();
		}
		return self::$aIns[$sClass];
	}

	public function run() {
		$this->oCore = Core::getIns();
		$this->aParams = $this->oCore->getParams();
		return $this->main();
	}

	abstract protected function main();

	protected function trBegin($sAction = 'action') {
		$this->aTS[$sAction] = microtime(true);
		Util::logInfo("{$sAction} --begin");
	}

	protected function trEnd($sAction = 'action') {
		Util::logInfo("{$sAction} --end");
		if (!isset($this->aTS[$sAction])) {
			Util::logInfo("{$sAction}: duration-> No time record");
			return false;
		}
		Util::logInfo("{$sAction}: duration-> " . (microtime(true) - $this->aTS[$sAction]));
		unset($this->aTS[$sAction]);
		return true;
	}

}