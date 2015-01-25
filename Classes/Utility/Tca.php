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
 * TCA helper utility.
 *
 * @package contentstage
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 *
 */
class Tx_Contentstage_Utility_Tca implements t3lib_singleton {
	/**
	 * @const string File field.
	 */
	const FILE = 'file';
	
	/**
	 * @const string Folder field.
	 */
	const FOLDER = 'folder';
	
	/**
	 * @const string 1:n relation, field is uid of the related record.
	 */
	const RELATION_DIRECT = 'direct';
	
	/**
	 * @const string n:1 relation, field x in related record points to the current uid.
	 */
	const RELATION_FOREIGN = 'foreign';
	
	/**
	 * @const string n:m relation with a _mm table.
	 */
	const RELATION_MM = 'mm';
	
	/**
	 * @const string The locallang prefix for the current extension.
	 */
	const LLL_PREFIX = 'LLL:EXT:contentstage/Resources/Private/Language/locallang.xml:';
	
	/**
	 * @var array The processed values for the TCA.
	 */
	protected $tca = false;
	
	/**
	 * @var array The array with table/uids that were already updated this run.
	 */
	protected $refindexUpdated = array();
	
	/**
	 * @var array The default fields that no TCA exists for (tstamp, crdate etc.).
	 */
	protected $defaultFields = array(
		'pid' => 'field.pid',
		't3ver_oid' => 'field.t3ver_oid',
		't3ver_id' => 'field.t3ver_id',
		't3ver_wsid' => 'field.t3ver_wsid',
		't3ver_label' => 'field.t3ver_label',
		't3ver_state' => 'field.t3ver_state',
		't3ver_stage' => 'field.t3ver_stage',
		't3ver_count' => 'field.t3ver_count',
		't3ver_tstamp' => 'field.t3ver_tstamp',
		't3ver_move_id' => 'field.t3ver_move_id',
		't3_origuid' => 'field.t3_origuid',
		'tstamp' => 'field.tstamp',
		'crdate' => 'field.crdate',
		'cruser_id' => 'field.cruser_id',
		'hidden' => 'field.hidden',
		'sorting' => 'field.sorting',
		'sys_language_uid' => 'field.sys_language_uid',
		'l18n_parent' => 'field.l18n_parent',
		'l18n_diffsource' => 'field.l18n_diffsource',
		'_targetMissing' => 'field._targetMissing',
		'_sourceMissing' => 'field._sourceMissing',
	);
	
	/**
	 * @var array The fields that should not be displayed. Array of table. => array of field. => 0/1.
	 */
	protected $ignoreFields = array();
	
	/**
	 * The cache.
	 *
	 * @var t3lib_cache_frontend_AbstractFrontend The cache.
	 */
	protected $cache = null;

	/**
	 * Initialize the cache.
	 *
	 * @return void
	 */
	protected function initializeCache() {
		t3lib_cache::initializeCachingFramework();
		t3lib_cache::initContentHashCache();
		$this->cache = $GLOBALS['typo3CacheManager']->getCache('cache_hash');
	}
	
	/**
	 * Initialize the ignore fields.
	 *
	 * @param array $ignoreFields Array of table. => array of field. => 0/1.
	 * @return void
	 */
	public function initializeIgnoreFields($ignoreFields) {
		$this->ignoreFields = &$ignoreFields;
	}
	
	/**
	 * Returns the relevant information from the TCA.
	 *
	 * @param string $table If set, only the tables part of the processed TCA is returned.
	 * @param string $field If set, only the fields part of the processed TCA is returned. (Table must be set).
	 * @return array The relevant part of the processed array.
	 */
	public function &getProcessedTca($table = null, $field = null) {
		$this->processTca();
		
		$tca = &$this->tca;
		
		if (is_string($table) && strlen($table) > 0) {
			$tca = &$tca[$table];
			
			if (is_string($field) && strlen($field) > 0) {
				$tca = &$tca[$field];
			}
		}
		
		return $tca;
	}
	
	/**
	 * Process the TCA.
	 *
	 * @return void
	 */
	protected function processTca() {
		if ($this->tca !== false) {
			return;
		}
		$this->initializeCache();
		
		$language = $this->translate(self::LLL_PREFIX . 'language');
		$identifier = 'tx_contentstage_Tca_' . $language;
		if (TX_CONTENTSTAGE_USECACHE && $this->cache->has($identifier)) {
			$this->tca = $this->cache->get($identifier);
			return;
		}
		
		$this->tca = array();
		$defaultFields = array();
		foreach ($this->defaultFields as $field => &$label) {
			$defaultFields[$field] = array(
				'__name' => $this->translate(self::LLL_PREFIX . $label)
			);
		}
		
		foreach ($GLOBALS['TCA'] as $table => &$tableData) {
			$this->tca[$table] = $defaultFields;
			$this->tca[$table]['__name'] = $this->translate($tableData['ctrl']['title']);
			$this->tca[$table]['__labelField'] = $tableData['ctrl']['label'];
			$this->tca[$table]['__tstampField'] = $tableData['ctrl']['tstamp'];
			$this->tca[$table]['__files'] = $this->tca[$table]['__folders'] = array();
			
			t3lib_div::loadTCA($table);
			foreach ($tableData['columns'] as $field => &$fieldData) {
				$this->processTcaField($table, $field, $fieldData['config'], $fieldData['label']);
			}
		}
		
		if (TX_CONTENTSTAGE_USECACHE) {
			$this->cache->set($identifier, $this->tca, array(), TX_CONTENTSTAGE_CACHETIME);
		}
	}
	
	/**
	 * Process a TCA field.
	 * TODO: 
	 * - select:neg_foreign_table
	 * - irre:foreign_selector
	 * IGNORED:
	 * - irre:foreign_table_field: irrelevant
	 * - irre: symmetric stuff: irrelevant
	 *
	 * @param string $table The table of the field.
	 * @param string $field The field.
	 * @param array $config The field config.
	 * @param string $label The The label of the field (may be LLL:-reference).
	 * @return void
	 */
	protected function processTcaField($table, $field, &$config, $label) {
		$processed = array();
		switch (strtolower($config['type'])) {
			case 'group':
				switch (strtolower($config['internal_type'])) {
					case 'file':
					case 'file_reference':
						$processed = array(
							'type' => self::FILE,
							'folder' => preg_replace('#[/\\\\]?$#', '/', $config['uploadfolder'])
						);
						$this->tca[$table]['__files'][$field] = true;
						break;
					
					case 'folder':
						$processed = array(
							'type' => self::FOLDER
						);
						$this->tca[$table]['__folders'][$field] = true;
						break;
						
					case 'db':
						if ($config['MM']) {
							$processed = array(
								'type' => self::RELATION_MM,
								'table' => $config['allowed'],
								'mmTable' => $config['MM']
							);
						}
						break;
						
					default:
						break;
				}
				break;
				
			case 'radio':
			case 'select':
				if (empty($config['foreign_table'])) {
					if (!empty($config['fileFolder'])) {
						$processed = array(
							'type' => self::FILE,
							'folder' => preg_replace('#[/\\\\]?$#', '/', $config['fileFolder'])
						);
						$this->tca[$table]['__files'][$field] = true;
						break;
					}
				}
				$processed = array(
					'type' => self::RELATION_DIRECT,
					'table' => $config['foreign_table']
				);
				
				if (is_array($config['items']) && count($config['items']) > 0) {
					$itemIndex = array();
					foreach ($config['items'] as $item) {
						$itemIndex[$item[1]] = $this->translate($item[0]);
					}
					$processed['items'] = $itemIndex;
				}
				
				if (!empty($config['MM'])) {
					$processed['type'] = self::RELATION_MM;
					$processed['mmTable'] = $config['MM'];
				}
				
				break;
			
			case 'inline':
				$processed = array(
					'type' => self::RELATION_DIRECT,
					'table' => $config['foreign_table']
				);
				
				if (!empty($config['foreign_field'])) {
					$processed['type'] = self::RELATION_FOREIGN;
					$processed['foreignField'] = $config['foreign_field'];
				} else if (!empty($config['MM'])) {
					$processed['type'] = self::RELATION_MM;
					$processed['mmTable'] = $config['MM'];
				}
				break;
				
			default:
				break;
		}
		
		$processed['__name'] = $this->translate($label);
		
		if (!empty($config['softref'])) {
			$processed['softref'] = $config['softref'];
		}
		
		$this->tca[$table][$field] = $processed;
	}
	
	/**
	 * Returns the name of the given table.
	 *
	 * @param string $table The table to get the name for.
	 * @return string The table name.
	 */
	public function getTableName($table) {
		$this->processTca();
		return $this->tca[$table]['__name'] ?: $table;
	}
	
	/**
	 * Returns the name of the given field.
	 *
	 * @param string $table The table.
	 * @param string $field The field to get the name for.
	 * @return string The field name.
	 */
	public function getFieldName($table, $field) {
		$this->processTca();
		return $this->tca[$table][$field]['__name'] ?: $field;
	}
	
	/**
	 * Returns the label field of the given table.
	 *
	 * @param string $table The table.
	 * @return string The label field.
	 */
	public function getLabelField($table) {
		$this->processTca();
		return $this->tca[$table]['__labelField'];
	}
	
	/**
	 * Returns the if the given field should be displayed or not.
	 *
	 * @param string $table The table.
	 * @param string $field The field.
	 * @return boolean true if it should be visible, false otherwise.
	 */
	public function isVisibleField($table, $field) {
		$_table = $table . '.';
		if (isset($this->ignoreFields['__all.'][$field]) && !empty($this->ignoreFields['__all.'][$field])) {
			return false;
		}
		
		return !isset($this->ignoreFields[$_table][$field]) || empty($this->ignoreFields[$_table][$field]);
	}
	
	/**
	 * Resolves the relations for a given table/field/value pair.
	 *
	 * @param Tx_Contentstage_Domain_Repository_ContentRepository $repository The repository to get the data from.
	 * @param string $table The table.
	 * @param string $field The field.
	 * @param string $value The value in the db.
	 * @param array $row The full row, used for the uid in it.
	 * @return string The string representation of the resolved field.
	 */
	public function resolve($repository, $table, $field, $value, &$row) {
		$config = &$this->getProcessedTca($table, $field);
		switch ($config['type']) {
			case self::RELATION_DIRECT:
				$value = $this->resolveDirect($repository, $config, $value);
				break;
				
			case self::RELATION_FOREIGN:
				$value = $this->resolveForeign($repository, $config, $row['uid']);
				break;
				
			case self::RELATION_MM:
				$value = $this->resolveMM($repository, $config, $row['uid']);
				break;
				
			default:
				break;
		}
		
		if (is_array($value)) {
			$value = implode(', ', $value);
		}
		
		return $value;
	}
	
	/**
	 * Resolves the relations for a given table/field/value pair. This does not necessarily go and check the record if not necessary and can only return the uids.
	 *
	 * @param Tx_Contentstage_Domain_Repository_ContentRepository $repository The repository to get the data from.
	 * @param string $table The table.
	 * @param string $field The field.
	 * @param string $value The value in the db.
	 * @param array $row The full row, used for the uid in it.
	 * @return array The table/uid or folder relations.
	 */
	public function &resolveUids($repository, $table, $field, $value, &$row) {
		$config = &$this->getProcessedTca($table, $field);
		$result = array();
		switch ($config['type']) {
			case self::RELATION_DIRECT:
				$result = &$this->resolveDirectUids($config, $value);
				break;
				
			case self::RELATION_FOREIGN:
				$result = &$this->resolveForeignUids($repository, $config, $row['uid'], 'uid');
				break;
				
			case self::RELATION_MM:
				$result = &$this->resolveMMUids($repository, $config, $row['uid']);
				$result[$config['mmTable']][$row['uid']] = true;
				break;
				
			case self::FILE:
				$key = '__FILE';
				
			case self::FOLDER:
				$key = $key ?: '__FOLDER';
				$files = t3lib_div::trimExplode(',', $value, true);
				$fileIndex = array();
				foreach ($files as $file) {
					$fileIndex[$config['folder'] . $file] = true;
				}
				$result[$key] = $fileIndex;
				break;
				
			default:
				break;
		}
		
		$this->resolveSoftRefUids($repository, $table, $field, $row, $result);
		
		return $result;
	}
	
	/**
	 * Update refindex if necessary and apply the sys_refindex items for the given field.
	 */
	protected function resolveSoftRefUids($repository, $table, $field, &$row, &$result) {
		$this->updateRefindex($repository, $table, $row['uid']);
		
		// escape alias
		$e = function($value) use ($repository) {
			return $repository->_getDb()->fullQuoteStr($value);
		};
		
		$query = 'SELECT * FROM sys_refindex WHERE table = ' . $e($table) . ' AND field = ' . $e($field) . ' AND recuid = ' . intval($row['uid']) . ' AND deleted = 0';
		
		foreach ($repository->_sql() as $row) {
			if (substr($row['ref_table'], 0, 1) === '_') {
				if ($row['ref_table'] === '_FILE') {
					$result['__FILE'][$row['ref_string']] = true;
				}
			} else {
				$result[$row['ref_table']][$row['ref_uid']] = true;
			}
		}
	}
	
	/**
	 * Update refindex if necessary.
	 */
	protected function updateRefindex($repository, $table, $uid) {
		if (isset($this->refindexUpdated[$table]) && isset($this->refindexUpdated[$table][$uid])) {
			return;
		}
		$identifier = 'tx_contentstage_updateRefindex_' . $repository->getTag() . '_' . $table . '_' . $uid;
		if (TX_CONTENTSTAGE_USECACHE && $this->cache->has($identifier)) {
			return;
		}
		
		$db = $GLOBALS['TYPO3_DB'];
		$GLOBALS['TYPO3_DB'] = $repository->_getDb();
		$refIndexObj = t3lib_div::makeInstance('t3lib_refindex');
		$result = $refIndexObj->updateRefIndexTable($table, $uid);
		$GLOBALS['TYPO3_DB'] = $db;
		
		$this->refindexUpdated[$table][$uid] = true;
		if (TX_CONTENTSTAGE_USECACHE) {
			$this->cache->set($identifier, true, array(), TX_CONTENTSTAGE_CACHETIME);
		}
	}
	
	/**
	 * Resolves the direct relation for a given table/field/value pair.
	 *
	 * @param Tx_Contentstage_Domain_Repository_ContentRepository $repository The repository to get the data from.
	 * @param array $config The config.
	 * @param string $value The value in the db.
	 * @param string $returnField The field to return into the value.
	 * @return string The string representation of the resolved field.
	 */
	protected function resolveDirect(Tx_Contentstage_Domain_Repository_ContentRepository $repository, $config, &$value, $returnField = null) {
		
		$uidsByTable = &$this->resolveDirectUids($config, $value);
		
		$this->resolveTables($repository, $uidsByTable, $returnField);
		
		return implode(', ', array_map(function (&$tableValues) {
			return implode(', ', $tableValues);
		}, $uidsByTable));
	}
	
	/**
	 * Resolves the direct relation for a given table/field/value pair and returns the uids.
	 *
	 * @param array $config The config.
	 * @param string $value The value in the db.
	 * @return array The table/uids.
	 */
	protected function &resolveDirectUids($config, &$value) {
		$uids = t3lib_div::trimExplode(',', $value, true);
		
		$uidsByTable = array();
		
		foreach ($uids as $itemValue) {
			if (is_array($config['items']) && isset($config['items'][$itemValue])) {
				$uidsByTable['__item'][] = $config['items'][$itemValue];
			} else {
				$parts = t3lib_div::trimExplode('_', $itemValue);
				$uid = array_pop($parts);
				$table = (count($parts) > 0 ? implode('_', $parts) : $config['table']);
				if (is_numeric($uid) && !empty($table)) {
					$uidsByTable[$table][intval($uid)] = intval($uid);
				} else {
					$uidsByTable['__passthrough'][] = $itemValue;
				}
			}
		}
		
		return $uidsByTable;
	}
	
	/**
	 * Resolves the mm relation for a given table/field/uid pair.
	 *
	 * @param Tx_Contentstage_Domain_Repository_ContentRepository $repository The repository to get the data from.
	 * @param array $config The config.
	 * @param string $localUid The uid of the record to resolve.
	 * @param string $returnField The field to return into the value.
	 * @return string The string representation of the resolved field.
	 */
	protected function resolveMM(Tx_Contentstage_Domain_Repository_ContentRepository $repository, $config, $localUid, $returnField = null) {
		$uidsByTable = $this->resolveMMUids($repository, $config, $localUid);
		
		$this->resolveTables($repository, $uidsByTable, $returnField);
		
		return implode(', ', array_map(function (&$value) {
			return implode(', ', $value);
		}, $uidsByTable));
	}
	
	/**
	 * Resolves the mm relation uids for a given table/field/uid pair.
	 *
	 * @param Tx_Contentstage_Domain_Repository_ContentRepository $repository The repository to get the data from.
	 * @param array $config The config.
	 * @param string $localUid The uid of the record to resolve.
	 * @return string The string representation of the resolved field.
	 */
	protected function &resolveMMUids(Tx_Contentstage_Domain_Repository_ContentRepository $repository, $config, $localUid) {
		$resource = $repository->findInPageTree(
			0,
			$config['mmTable'],
			'*',
			'uid_local = ' . $localUid,
			'',
			''
		);
		
		$uidsByTable = array();
		while (($row = $resource->next()) !== false) {
			$uidsByTable[($row['tablenames'] ?: $config['table'])][$row['uid_foreign']] = $row['uid_foreign'];
		}
		
		return $uidsByTable;
	}
	
	/**
	 * Resolves the foreign relation for a given table/field/uid pair.
	 *
	 * @param Tx_Contentstage_Domain_Repository_ContentRepository $repository The repository to get the data from.
	 * @param array $config The config.
	 * @param string $localUid The uid of the record to resolve.
	 * @param string $returnField The field to return into the value.
	 * @return string The string representation of the resolved field.
	 */
	protected function resolveForeign(Tx_Contentstage_Domain_Repository_ContentRepository $repository, $config, $localUid, $returnField = null) {
		$values = &$this->resolveForeignUids($repository, $config, $localUid, $returnField);
		
		return implode(', ', $values[$config['table']]);
	}
	
	/**
	 * Resolves the foreign relation for a given table/field/uid pair and returns the table/uid pair.
	 *
	 * @param Tx_Contentstage_Domain_Repository_ContentRepository $repository The repository to get the data from.
	 * @param array $config The config.
	 * @param string $localUid The uid of the record to resolve.
	 * @param string $returnField The field to return into the value.
	 * @return string The string representation of the resolved field.
	 */
	protected function &resolveForeignUids(Tx_Contentstage_Domain_Repository_ContentRepository $repository, $config, $localUid, $returnField = null) {
		$labelField = $this->getLabelField($config['table']);
		$resource = $repository->findInPageTree(
			0,
			$config['table'],
			($returnField !== null ? $returnField . ', ' : '') . $labelField,
			$config['foreignField'] . ' = ' . $localUid,
			'',
			$config['foreignField'] . ' ASC'
		);
		
		$values = array($config['table'] => array());
		while (($row = $resource->next()) !== false) {
			$values[$config['table']][$row['uid']] = $row[($returnField ?: $labelField)];
		}
		
		return $values;
	}
	
	/**
	 * Resolves the table => array of uids array.
	 *
	 * @param Tx_Contentstage_Domain_Repository_ContentRepository $repository The repository to get the data from.
	 * @param array $uidsByTable The array with table => uids pairs.
	 * @param string $returnField The field to return into the value.
	 * @return string The string representation of the resolved field.
	 */
	protected function resolveTables(Tx_Contentstage_Domain_Repository_ContentRepository $repository, &$uidsByTable, $returnField = null) {
		foreach ($uidsByTable as $table => &$uidData) {
			if (substr($table, 0, 2) === '__') {
				continue;
			}
			
			$labelField = $this->getLabelField($table);
			$resource = $repository->findInPageTree(
				0,
				$table,
				($returnField !== null ? $returnField . ', ' : '') . $labelField,
				'uid IN (' . implode(',', $uidData) . ')',
				'',
				'uid ASC'
			);
			
			$uidData = $resource->all($returnField ?: $labelField);
		}
	}
	
	/**
	 * Translate a given local lang reference.
	 *
	 * @param string $lll The local lang reference LLL:EXT:ext/locallang_db.xml:some.key. If no LLL: is present, the $lll value is returned.
	 * @return string The translated text.
	 */
	protected function translate($lll) {
		if (substr($lll, 0, 4) !== 'LLL:') {
			return $lll;
		}
		
		return Tx_Extbase_Utility_Localization::translate($lll, '');
	}
}
?>
