<?php

/**
 * Document: User
 * Created on: 2012-6-4, 12:44:18
 * @author: jxxu
 * GTalk: sailxjx@gmail.com
 */
class Model_User extends Model_Base {

	/**
	 * get joindate from dv_user
	 * @param int $iSId userid
	 * @param int $iLimit limit
	 * @return array
	 */
	public function getDvUserJoinDateByRange($iSId = 0, $iLimit = 200) {
		$iSId = intval($iSId);
		$iLimit = intval($iLimit);
		$oPdo = Fac_Db::getIns()->loadPdo('MSSQL');
		$sSql = "SELECT TOP {$iLimit} UserID,JoinDate FROM Dv_User WHERE UserID>{$iSId} ORDER BY UserID ASC";
		$oStmt = $oPdo->prepare($sSql);
		$oStmt->execute();
		return $oStmt->fetchAll(PDO::FETCH_ASSOC);
	}

	/**
	 *
	 * @param int $iUserId
	 * @param int $iJoinDate
	 * @return boolean
	 */
	public function updUserJoinDateInMysql($iUserId, $iJoinDate) {
		$oPdo = Fac_Db::getIns()->loadPdo('MYSQL');
		$sTable = 'user_' . ($iUserId % 16);
		$sSql = "UPDATE {$sTable} SET join_date=? WHERE userid=?";
		$oStmt = $oPdo->prepare($sSql);
		return $oStmt->execute(array($iJoinDate, $iUserId));
	}

}
