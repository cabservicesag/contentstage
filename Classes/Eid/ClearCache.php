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
	const CACHE_TABLE = 'cachingframework_cache_hash';
	
	/**
	 * @var string The md5 hash for the call.
	 */
	protected $hash = false;
	
	/**
	 * @var array The errors if any occured.
	 */
	protected $errors = array();
	
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
		
		$rows = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows(
			'*',
			self::CACHE_TABLE,
			'crdate > ' . (time() - 10) . ' AND identifier = ' . $GLOBALS['TYPO3_DB']->fullQuoteStr($this->hash, self::CACHE_TABLE),
			'',
			'crdate DESC',
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
		
		$tsfe = t3lib_div::makeInstance('tslib_fe', $GLOBALS['TYPO3_CONF_VARS'], 0, 0);
		
		$tsfe->clearPageCacheContent_pidList(unserialize($row['content']));
		
		$GLOBALS['TYPO3_DB']->exec_DELETEquery(
			self::CACHE_TABLE,
			'identifier = ' . $GLOBALS['TYPO3_DB']->fullQuoteStr($this->hash, self::CACHE_TABLE)
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
}

$clearCache = new Tx_Contentstage_Eid_ClearCache();
$clearCache->initialize();
$clearCache->clear();
$clearCache->display();
die();
?>
