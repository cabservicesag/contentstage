<?php

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2013 
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
 *
 *
 * @package contentstage
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 *
 */
class Tx_Contentstage_Domain_Model_BaseModel extends Tx_Extbase_DomainObject_AbstractEntity {
	
	/**
	 * The cache (6.2.4 extbase has problems with t3lib_cache_frontend_AbstractFrontend => object).
	 *
	 * @var object
	 * @transient
	 */
	protected static $cache = null;

	/**
	 * Collects the needed data recursively cached. This function detects recursion loops.
	 *
	 * @param string $function The function to call and collect the result of.
	 * @param string $childrenFunction The function to get the children from (must return an iterable object or an array).
	 * @param callable $processData An optional closure to process the data from the function, must return an array.
	 * @return array The resulting collection.
	 */
	public function collectRecursiveDataCached($function, $childrenFunction, $processData = null, $keyFunction = null, &$recursionIndex = array()) {
		if (isset($recursionIndex[$this->getUid()])) {
			return array();
		}
		$recursionIndex[$this->getUid()] = true;
		
		$identifier = 'tx_contentstage_' . get_class($this) . '_' . $function . '_' . $childrenFunction . '_' . $this->getUid();
		
		if (TX_CONTENTSTAGE_USECACHE) {
			if (self::$cache === null) {
				t3lib_cache::initializeCachingFramework();
				if (method_exists('t3lib_cache', 'initContentHashCache')) {
					// not needed in 6.2 anymore
					t3lib_cache::initContentHashCache();
				}
				self::$cache = $GLOBALS['typo3CacheManager']->getCache('cache_hash');
			}
			if (TX_CONTENTSTAGE_USECACHE && self::$cache->has($identifier)) {
				return self::$cache->get($identifier);
			}
		}
		
		$data = array();
		$tData = array();
		if (is_callable(array($this, $function))) {
			$tData = $this->$function();
			if ($processData !== null && is_callable($processData)) {
				$tData = $processData($tData);
			}
		}
		
		$tData = is_array($tData) ? $tData : array();
		$useKeyFunction = false;
		if ($keyFunction !== null && is_callable($keyFunction)) {
			$useKeyFunction = true;
			
			foreach ($tData as $item) {
				$key = $keyFunction($item);
				if ($key !== null) {
					$data[$key] = $item;
				} else {
					$data[] = $item;
				}
			}
		} else {
			$data = $tData;
		}
		
		if (is_callable(array($this, $childrenFunction))) {
			foreach ($this->$childrenFunction() as $child) {
				foreach ($child->collectRecursiveDataCached($function, $childrenFunction, $processData, $keyFunction, $recursionIndex) as $item) {
					if ($useKeyFunction && ($key = $keyFunction($item)) !== null) {
						$data[$key] = $item;
					} else {
						$data[] = $item;
					}
				}
			}
		}
		
		if (TX_CONTENTSTAGE_USECACHE) {
			self::$cache->set($identifier, $data, array(), TX_CONTENTSTAGE_CACHETIME);
		}
		return $data;
	}
}
