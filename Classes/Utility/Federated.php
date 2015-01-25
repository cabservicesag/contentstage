<?php

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2014 Lavinia Negru <ln@cabag.ch>, cab services ag
 *  
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

/**
 * Convert Tables to Federated Tables
 *
 * @package contentstage
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 *
 */
class Tx_Contentstage_Utility_Federated implements t3lib_singleton {
	/**
	 * Converts tables to federated tables
	 *
	 * @param Tx_Contentstage_Domain_Repository_ContentRepository $slaveRepository The repository to initialize the federated tables.
	 * @param Tx_Contentstage_Domain_Repository_ContentRepository $masterRepository The repository which holds the master tables.
	 * @param string $table The table to make federated.
	 * @param array $remoteLoginData The db information for the remote database to make the tables federated with.
	 * @return boolean True if the table was changed to federated, false otherwise.
	 * @throws Exception
	 */
	public function convertTables(Tx_Contentstage_Domain_Repository_ContentRepository $slaveRepository, Tx_Contentstage_Domain_Repository_ContentRepository $masterRepository, $table, $remoteLoginData) {
		//check if table is already federated
		$statusTableRow = $slaveRepository->_sql('SHOW TABLE STATUS LIKE \'' . $table . '\'');
		
		if (count($statusTableRow) === 0) {
			throw new Exception('Table does not exist in the slave database [' . $table . ']', 1399985904);
		}
		$statusTableRow = current($statusTableRow);
		
		//if not already federated then federate	
		if ($statusTableRow['Engine'] !== 'FEDERATED') {
			$createTableRow = $masterRepository->_sql('SHOW CREATE TABLE ' . $table);
			
			if (count($createTableRow) === 0) {
				throw new Exception('Table does not exist in the master database [' . $table . ']', 1399985905);
			}
			$createTableRow = current($createTableRow);
			
			$createTable = trim(preg_replace('/ENGINE=[^ ]+/i', 'ENGINE=FEDERATED', $createTableRow['Create Table']));
			
			if (substr($createTable, -1) === ';') {
				$createTable = substr($createTable, 0, -1);
			}
			
			$createTable .= ' CONNECTION=\'mysql://' . $remoteLoginData['user'] . ':' . $remoteLoginData['password'] . '@' . $remoteLoginData['host'] . '/' . $remoteLoginData['database'] . '/' . $table . '\'';
			
			//$slaveRepository->_sql('DROP TABLE '.$table.';');
			$slaveRepository->_sql('RENAME TABLE ' . $table . ' TO ' . $table . '_' . date('Ymd'), false);
			$slaveRepository->_sql($createTable);		
			return true;
		}
		return false;
	}
}
?>