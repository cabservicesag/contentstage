<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2013 Nils Blattner <nb@cabag.ch>
*  All rights reserved
*
*  This script is part of the TYPO3 project. The TYPO3 project is
*  free software; you can redistribute it and/or modify
*  it under the terms of the GNU General Public License as published by
*  the Free Software Foundation; either version 2 of the License, or
*  (at your option) any later version.
*
*  The GNU General Public License can be found at
*  http://www.gnu.org/copyleft/gpl.html.
*  A copy is found in the textfile GPL.txt and important notices to the license
*  from the author is found in LICENSE.txt distributed with these scripts.
*
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/
/**
 * XCLASS for the record history class
 *
 * $Id$
 * XHTML Compliant
 *
 * @author	Nils Blattner <nb@cabag.ch>
 */

require_once(PATH_site . 'typo3/class.show_rechis.inc');

/**
 * XCLASS for the record history class
 *
 * @author	Nils Blattner <nb@cabag.ch>
 * @package TYPO3
 * @subpackage core
 */
class ux_recordHistory extends recordHistory {
	/**
	 * @var array The review history entries.
	 */
	protected $reviewHistory = array();

	/**
	 * Creates change log including sub-elements, filling $this->changeLog
	 *
	 * @return	[type]		...
	 */
	function createChangeLog()	{
		$this->reviewHistory = array();
		$result = parent::createChangeLog();
		
		// after the ksort the $this->changeLog should be sorted ascending by sys_log uid
		// because review records do not really have a sys_log uid, we have to fake it
		
		return $result;
	}

	/**
	 * Gets history and delete/insert data from sys_log and sys_history
	 *
	 * @param	string		DB table name
	 * @param	integer		UID of record
	 * @return	array		history data of the record
	 */
	function getHistoryData($table, $uid)	{
		$changeLog = parent::getHistoryData($table, $uid);
		
		if ($table === 'pages') {
			if (!is_array($changeLog)) {
				$changeLog = array();
			}
			$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
				'r.*, s.user AS push_user',
				'tx_contentstage_domain_model_review AS r LEFT JOIN tx_contentstage_domain_model_state AS s ON (r.state = s.uid)',
				'r.deleted <> 0 AND s.deleted <> 0 AND s.state = ' . $GLOBALS['TYPO3_DB']->fullQuoteStr($table, 'pushed'),
				'',
				'r.tstamp ASC'
			);
			while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
				$hisDat = array(
					'action' => 'pushed',
					'tstamp' => $row['tstamp'],
					'user' => $row['push_user'],
					'tablename' => $table,
					'recuid' => $uid
				);
				
				$this->reviewHistory[$row['tstamp']] = $hisDat;
			}
		}
		
		return count($changeLog) > 0 ? $changeLog : 0;
	}
}
?>
