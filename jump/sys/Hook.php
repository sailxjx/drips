<?php

/**
 * Document: Hook
 * Created on: 2012-5-21, 12:34:53
 * @author: jxxu
 * GTalk: sailxjx@gmail.com
 */
class Hook {

	private static $oIns;
	protected $aPreHooks = array();
	protected $aPostHooks = array();

	/**
	 *
	 * @var Core
	 */
	protected $oCore;
	protected $aParams;

	protected function __construct() {
		$this->oCore = Core::getIns();
		$this->aParams = $this->oCore->getParams();
	}

	/**
	 *
	 * @return Hook
	 */
	public static function &getIns() {
		if (!isset(self::$oIns)) {
			self::$oIns = new Hook();
		}
		return self::$oIns;
	}

	public function pre() {
		return $this->runHook(Const_Common::P_PRE_HOOK);
	}

	public function post() {
		return $this->runHook(Const_Common::P_POST_HOOK);
	}

	protected function runHook($sType = Const_Common::P_PRE_HOOK) {
		$sPostHooks = isset($this->aParams[$sType]) ? $this->aParams[$sType] : '';
		$aPostHooks = explode(',', $sPostHooks);
		if (empty($aPostHooks)) {
			return false;
		}
		foreach ((array) $aPostHooks as $sShell) {
			$sFile = APP_PATH . 'hooks/' . $sShell;
			Util_SysUtil::runFile($sFile);
		}
		return true;
	}

}
