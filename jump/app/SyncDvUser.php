<?php

/**
 * Document: SyncDvUser
 * Created on: 2012-6-4, 12:07:27
 * @author: jxxu
 * GTalk: sailxjx@gmail.com
 */
class SyncDvUser extends Base {

	const P_START_ID = 'psi';
	const P_LIMIT_NUM = 'pln';

	protected $iDLimit = 2000;
	protected $iSUserId = 0;
	protected $sIdFile = 'var/lastuser.id';
	protected $sLogFile = 'log/syncdvuser.log';

	protected function main() {
		$this->getLimits();
		$this->getLastUserId();
		$this->sync();
	}

	protected function getLastUserId() {
		$this->iSUserId = isset($this->aParams[self::P_START_ID]) ? intval($this->aParams[self::P_START_ID]) : 0;
		return $this->iSUserId;
	}

	protected function getLimits() {
		$this->iDLimit = isset($this->aParams[self::P_LIMIT_NUM]) ? intval($this->aParams[self::P_LIMIT_NUM]) : $this->iDLimit;
		return $this->iDLimit;
	}

	protected function setLastUserId($iUserId) {
		Util::logInfo('last update id: ' . $iUserId, APP_PATH . $this->sLogFile);
		return Util::setFileCon(APP_PATH . $this->sIdFile, $iUserId, FILE_BINARY);
	}

	protected function sync() {
		$omUser = Model_User::getIns();
		$aUsers = $omUser->getDvUserJoinDateByRange($this->iSUserId, $this->iDLimit);
		if (empty($aUsers)) {
			$this->setLastUserId('finish');
			return false;
		}
		foreach ($aUsers as $aUser) {
			$iUserId = $aUser['UserID'];
			$iJoinDate = $this->convJoinDate($aUser['JoinDate']);
			$omUser->updUserJoinDateInMysql($iUserId, $iJoinDate);
		}
		$this->setLastUserId($iUserId);
		return true;
	}

	protected function convJoinDate($sJoinDate) {
		list($sDate, $sMSec) = explode('.', $sJoinDate);
		$iRound = round(intval($sMSec) / 1000); //毫秒数四舍五入
		return strtotime($sDate) + $iRound;
	}

}
