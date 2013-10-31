<?php
if (!defined('PATH_typo3conf')) die ('Could not access this script directly!');

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2012 Nils Blattner <nb@cabag.ch>, cab services ag
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
 * Clear cache EId.
 *
 * @package contentstage
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 *
 */
class Tx_Contentstage_Eid_ClearCache {
	/**
	 * @const string The cache table to write to. Might have to be changed for future versions.
	 * @see Tx_Contentstage_Domain_Repository_ContentRepository::CACHE_TABLE
	 */
	const CACHE_TABLES = 'cachingframework_cache_hash,cf_cache_hash';
	
	/**
	 * @var string The md5 hash for the call.
	 */
	protected $hash = false;
	
	/**
	 * @var array The errors if any occured.
	 */
	protected $errors = array();
	
	/**
	 * @var string The pages to be cleared or "ALL".
	 */
	protected $command = '';
	
	/**
	 * Initialize.
	 */
	public function initialize() {
		$hash = t3lib_div::_GP('hash');
		
		if (!preg_match('/^[a-z0-9]{32}$/i', $hash)) {
			$this->errors[] = array(
				'ident' => 'badHash',
				'message' => 'Incorrect hash given [' . $hash . ']!'
			);
			return;
		}
		$this->hash = $hash;

	    if (!tslib_eidtools::connectDB()) {
			$this->errors[] = array(
				'ident' => 'noDb',
				'message' => 'Could not connect to DB!'
			);
			$this->hash = false;
	    }
	}
	
	/**
	 * Clear the cache for the given hash.
	 */
	public function clear() {
		if ($this->hash === false) {
			return;
		}
		
		$table = false;
		foreach (t3lib_div::trimExplode(',', self::CACHE_TABLES, true) as $possibleTable) {
			$res = $GLOBALS['TYPO3_DB']->sql_query('SHOW TABLES LIKE \'' . $possibleTable . '\'');
			if ($GLOBALS['TYPO3_DB']->sql_num_rows($res) > 0) {
				$table = $possibleTable;
				break;
			}
		}
		
		if (!$table) {
			$this->errors[] = array(
				'ident' => 'noTable',
				'message' => 'No caching table found!'
			);
			return;
		}
		
		$expires = $table === 'cf_cache_hash' ? 'expires' : 'crdate';
		$rows = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows(
			'*',
			$table,
			($table === 'cf_cache_hash' ? 'expires > ' : '(crdate + lifetime) > ') . time() . ' AND identifier = ' . $GLOBALS['TYPO3_DB']->fullQuoteStr($this->hash, $table),
			'',
			$expires . ' DESC',
			1
		);

		if (count($rows) === 0) {
			$this->errors[] = array(
				'ident' => 'noHash',
				'message' => 'Hash not found in DB (or expired) [' . $this->hash . ']!'
			);
			return;
		}
		
		$row = current($rows);
		$this->command = $content = unserialize($row['content']);
		
		try {
			if ($content === 'ALL') {
				// fake tcemain
				$tceMain = t3lib_div::makeInstance('t3lib_TCEmain');
				$tceMain->BE_USER = new Tx_Contentstage_Eid_ClearCache_FakeBEUSER();
				$tceMain->BE_USER->user = array('username' => 'tx_contentstage_eId');
				$tceMain->admin = true;
				
				$tceMain->clear_cacheCmd('all');
			} else {
				$tsfe = t3lib_div::makeInstance('tslib_fe', $GLOBALS['TYPO3_CONF_VARS'], 0, 0);
				
				$tsfe->clearPageCacheContent_pidList($content);
			}
		} catch (Exception $e) {
			$this->errors[] = array(
				'ident' => 'exception',
				'message' => $e->getMessage()
			);
		}
		
		$GLOBALS['TYPO3_DB']->exec_DELETEquery(
			$table,
			'identifier = ' . $GLOBALS['TYPO3_DB']->fullQuoteStr($this->hash, $table)
		);
	}
	
	/**
	 * Display success/error.
	 */
	public function display() {
		echo json_encode(array(
			'success' => (count($this->errors) === 0),
			'errors' => $this->errors
		));
	}
	
	/**
	 * Returns the errors (if any).
	 *
	 * @return array The errors.
	 */
	public function getErrors() {
		return $this->errors;
	}
	
	/**
	 * Returns the cache command.
	 *
	 * @return string The page uid or "ALL".
	 */
	public function getCommand() {
		return $this->command;
	}
}

/**
 * Fake BE_USER
 */
class Tx_Contentstage_Eid_ClearCache_FakeBEUSER {
	public function writelog() {}
}

$clearCache = new Tx_Contentstage_Eid_ClearCache();
$clearCache->initialize();
$clearCache->clear();
$clearCache->display();
die();
?>
