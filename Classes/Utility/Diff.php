<?php

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
 *
 *
 * @package contentstage
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 *
 */
class Tx_Contentstage_Utility_Diff {
	
	/**
	 * The TCA utility object.
	 *
	 * @var Tx_Contentstage_Utility_Tca The TCA utility object.
	 */
	protected $tca = null;
	
	/**
	 * The maximum tstamp at the source.
	 *
	 * @var int
	 */
	protected $maximumSourceTstamp = 0;
	
	/**
	 * The maximum tstamp at the target.
	 *
	 * @var int
	 */
	protected $maximumTargetTstamp = 0;
	
	/**
	 * Injects the TCA utility object.
	 *
	 * @param Tx_Contentstage_Utility_Tca $diff The TCA utility object.
	 */
	public function injectTca(Tx_Contentstage_Utility_Tca $tca = null) {
		$this->tca = $tca;
	}
	
	/**
	 * @var t3lib_diff Diff helper object.
	 */
	protected $diff = null;
	
	/**
	 * Ensures that the local diff helper object is initialized.
	 */
	protected function init() {
		if ($this->diff !== null) {
			return;
		}
		$this->diff = t3lib_div::makeInstance('t3lib_diff');
	}
	
	/**
	 * Diff's two rows (must be an array of key => value pairs and _children => array of children items).
	 *
	 * @param array $tree1 The original (target) tree.
	 * @param array $tree2 The changed (source) tree.
	 * @param array $differences The difference array, passed this way for recursions etc.
	 * @param string $keyField The key field of each row (usually uid).
	 * @param string $table The table to perform the diff for. Is only used to hide certain fields. Defaults to pages.
	 * @return array An array with keys 'tree1', 'tree2', 'differences', whereas the two trees contain '_differences' keys and the 'differences' key contains an index by uid.
	 */
	public function &rows(array &$tree1 = array(), array &$tree2 = array(), array &$differences = array(), $keyField = 'uid', $table = 'pages') {
		$this->init();
		$tree1Keys = $this->getKeyIndex($tree1, $table);
		$tree2Keys = $this->getKeyIndex($tree2, $table);
		
		$uid = intval($tree1[$keyField]);
		
		$tableTCA = $this->tca->getProcessedTca($table);
		$this->maximumTargetTstamp = max($this->maximumTargetTstamp, intval($tree1[$tableTCA['__tstampField']]));
		$this->maximumSourceTstamp = max($this->maximumSourceTstamp, intval($tree2[$tableTCA['__tstampField']]));
		
		if (!isset($differences[$uid])) {
			$differences[$uid] = array();
		}
		$d = &$differences[$uid];
		
		if ($uid !== intval($tree2[$keyField])) {
			$d[$keyField] = 'Internal Error! Keyfield mismatch [' . $keyField . '].';
		}
		unset($tree1Keys[$keyField]);
		unset($tree2Keys[$keyField]);
		
		foreach ($tree1Keys as $key => &$true) {
			if (isset($tree2Keys[$key])) {
				if ((string)$tree1[$key] !== (string)$tree2[$key]) {
					$d[$key] = $this->diff->makeDiffDisplay($tree1[$key], $tree2[$key]);
				}
				unset($tree2Keys[$key]);
			} else {
				$d[$key] = $this->wrap($this->translate('diff.source.fieldMissing', array($key)), true);
			}
		}
		
		foreach ($tree2Keys as $key => &$true) {
			$d[$key] = $this->wrap($this->translate('diff.target.fieldMissing', array($key)));
		}
		
		$tree1['_differences'] = &$d;
		$tree2['_differences'] = &$d;
		return array(
			'tree1' => &$tree1,
			'tree2' => &$tree2,
			'differences' => &$differences
		);
	}
	
	/**
	 * Diff's two rows recursively (must be an array of key => value pairs and _children => array of children items).
	 *
	 * @param array $tree1 The original (target) tree.
	 * @param array $tree2 The changed (source) tree.
	 * @param array $differences The difference array, passed this way for recursions etc.
	 * @param string $keyField The key field of each row (usually uid).
	 * @param string $table The table to perform the diff for. Is only used to hide certain fields. Defaults to pages.
	 * @return array An array with keys 'tree1', 'tree2', 'differences', whereas the two trees contain '_differences' keys and the 'differences' key contains an index by uid.
	 */
	public function &rowsRecursive(array &$tree1 = array(), array &$tree2 = array(), array &$differences = array(), $keyField = 'uid', $table = 'pages') {
		$this->rows($tree1, $tree2, $differences, $keyField, $table);
		
		$this->children($tree1['_children'], $tree2['_children'], $differences, $keyField, $table);
		
		return array(
			'tree1' => &$tree1,
			'tree2' => &$tree2,
			'differences' => &$differences
		);
	}
	
	/**
	 * Diff's two arrays of rows (must be an array of array of key => value pairs and _children => array of children items).
	 *
	 * @param array $children1 The original (target) children.
	 * @param array $children2 The changed (source) children.
	 * @param array $differences The difference array, passed this way for recursions etc.
	 * @param string $keyField The key field of each row (usually uid).
	 * @param string $table The table to perform the diff for. Is only used to hide certain fields. Defaults to pages.
	 * @return array An array with keys 'children1', 'children2', 'differences', whereas the two children arrays contain '_differences' keys and the 'differences' key contains an index by uid.
	 */
	public function &children(array &$children1 = array(), array &$children2 = array(), array &$differences = array(), $keyField = 'uid', $table = 'pages') {
		$this->init();
		$children1Keys = $this->getKeyIndex($children1, $table);
		$children2Keys = $this->getKeyIndex($children2, $table);
		if (!isset($differences[$uid])) {
			$differences[$uid] = array();
		}
		$d = &$differences[$uid];
		
		foreach ($children1Keys as $uid => &$true) {
			if (isset($children2Keys[$uid])) {
				unset($children2Keys[$uid]);
			} else {
				$d[$uid] = $this->wrap($this->translate('diff.source.recordMissing', array($keyField, $uid)), true);
			}
		}
		
		foreach ($children2Keys as $uid => &$true) {
			$d[$uid] = $this->wrap($this->translate('diff.target.recordMissing', array($keyField, $uid)));
		}
		
		$children1['_differences'] = &$d;
		$children2['_differences'] = &$d;
		return array(
			'children1' => &$children1,
			'children2' => &$children2,
			'differences' => &$differences
		);
	}
	
	/**
	 * Diff's two repository resources. IMPORTANT: This function assumes that there is a total order over the key field and that they are sorted by the keyfield ascending! Basically it assumes that the key field is an integer aswell!
	 *
	 * @param Tx_Contentstage_Domain_Repository_Result $resource1 The original (target) resource.
	 * @param Tx_Contentstage_Domain_Repository_Result $resource2 The changed (source) resource.
	 * @param array $differences The difference array, passed this way for recursions etc.
	 * @param string $keyField The key field of each row (usually uid).
	 * @param string $table The table to perform the diff for. Is only used to hide certain fields. Defaults to pages.
	 * @return array An array with keys for each $keyField found in either (or both) of the resources. These arrays contain an array with differences for each field or a lone message if the row was missing entirely.
	 */
	public function &resources(Tx_Contentstage_Domain_Repository_Result &$resource1 = null, Tx_Contentstage_Domain_Repository_Result &$resource2 = null, array &$differences = array(), $keyField = 'uid', $pidField = 'pid') {
		$differences = array('byPid' => array());
		$r1 = $resource1->nextResolved();
		$r2 = $resource2->nextResolved();
		$table = $resource1->getTable();
		$tableTCA = $this->tca->getProcessedTca($table);
		$fromRepository = $resource1->getRepository();
		$toRepository = $resource2->getRepository();
		
		while (true) {
			if ($r1 === false && $r2 === false) {
				break;
			}
			
			$uid1 = $r1 === false ? PHP_INT_MAX : $r1[$keyField];
			$uid2 = $r2 === false ? PHP_INT_MAX : $r2[$keyField];
			$r1Next = $r2Next = false;
			$uid = min($uid1, $uid2);
			
			if ($uid1 < $uid2) {
				$differences[$uid]['_sourceMissing'] = $this->wrap($this->translate('diff.source.recordMissing', array($keyField, $uid)), true);
				$r1Next = true;
				$this->maximumTargetTstamp = max($this->maximumTargetTstamp, intval($r1[$tableTCA['__tstampField']]));
			} else if ($uid2 < $uid1) {
				$differences[$uid]['_targetMissing'] = $this->wrap($this->translate('diff.target.recordMissing', array($keyField, $uid)));
				$r2Next = true;
				$this->maximumSourceTstamp = max($this->maximumSourceTstamp, intval($r2[$tableTCA['__tstampField']]));
			} else {
				$this->rows($r1, $r2, $differences, $keyField, $table);
				
				foreach (array('files', 'folders', 'softrefs') as $type) {
					if (!is_array($tableTCA['__' . $type])) {
						continue;
					}
					foreach ($tableTCA['__' . $type] as $field => $true) {
						if (!isset($differences[$uid][$field])) {
							// no difference, let's check the files
							if ($type === 'softrefs') {
								$folder = '';
								$function = 'compareFiles';
								$values = array();
								$this->tca->resolveSoftRefUids($fromRepository, $table, $field, $r1, $values);
								$singleValues = is_array($values['__FILE']) ? array_keys($values['__FILE']) : array();
							} else {
								$folder = $tableTCA[$field]['folder'];
								$function = 'compare' . ucfirst($type);
								$singleValues = t3lib_div::trimExplode(',', $r1[$field], true);
							}
							
							foreach ($singleValues as $value) {
								$message = $toRepository->$function(
									$fromRepository->getFileHandle($folder . $value),
									$toRepository->getFileHandle($folder . $value)
								);
								if ($message !== false) {
									$differences[$uid][$field] .= $this->wrap($message);
								}
							}
						}
					}
				}
				
				$r1Next = $r2Next = true;
			}
			
			if (!empty($differences[$uid])) {
				$differences['byPid'][($r1 === false ? $r2[$pidField] : $r1[$pidField])][$uid] = &$differences[$uid];
			}
			
			if ($r1Next) {
				$r1 = $resource1->nextResolved();
			}
			if ($r2Next) {
				$r2 = $resource2->nextResolved();
			}
		}
		
		return $differences;
	}
	
	/**
	 * Returns an index of the keys of the given array. The idea is, that keys can be set/unset without touching the original array.
	 *
	 * @param array $array The source associative array.
	 * @param string $table The table to perform the diff for. Is only used to hide certain fields. Defaults to pages
	 * @return array An array with the same keys, but no content.
	 */
	protected function &getKeyIndex(array &$array = array(), $table = 'pages') {
		$keys = array();
		foreach ($array as $key => &$value) {
			if ($this->tca->isVisibleField($table, $key)) {
				$keys[$key] = true;
			}
		}
		
		unset($keys['_children']);
		unset($keys['_differences']);
		
		return $keys;
	}
	
	/**
	 * Wrap a message to be displayed.
	 *
	 * @param string $message The message.
	 * @param boolean $sourceMissing Whether or not the content is missing at the source (= red).
	 * @return string The HTML string.
	 */
	protected function wrap($message = '', $sourceMissing = false) {
		return '<span class="diff-' . ($sourceMissing ? 'r' : 'g') . '">' . $message . '</span>';
	}
	
	/**
	 * Translate a given key.
	 *
	 * @param string $key The locallang key.
	 * @param array $arguments If given, it will be passed to vsprintf.
	 * @return string The locallized string.
	 */
	public function translate($key, $arguments = null) {
		return Tx_Extbase_Utility_Localization::translate($key, 'Contentstage', $arguments);
	}
	
	/**
	 * Reset the maximum tstamps.
	 *
	 * @return void
	 */
	public function resetMaximumTstamps() {
		$this->maximumSourceTstamp = 0;
		$this->maximumTargetTstamp = 0;
	}
	
	/**
	 * Returns the maximum source tstamp.
	 *
	 * @return int The maximum source tstamp.
	 */
	public function getMaximumSourceTstamp() {
		return $this->maximumSourceTstamp;
	}
	
	/**
	 * Returns the maximum target tstamp.
	 *
	 * @return int The maximum target tstamp.
	 */
	public function getMaximumTargetTstamp() {
		return $this->maximumTargetTstamp;
	}
}
?>
