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
class Tx_Contentstage_Controller_BaseController extends Tx_CabagExtbase_Controller_BaseController {
	/**
	 * @var array An array of table => true pairs to be ignored in sync/diff.
	 */
	protected $ignoreSyncTables = array(
		'be_sessions' => true,
		'cache_extensions' => true,
		'cache_hash' => true,
		'cache_imagesizes' => true,
		'cache_md5params' => true,
		'cache_pages' => true,
		'cache_pagesection' => true,
		'cache_sys_dmail_stat' => true,
		'cache_treelist' => true,
		'cache_typo3temp_log' => true,
		'cachingframework_cache_hash' => true,
		'cachingframework_cache_hash_tags' => true,
		'cachingframework_cache_pages' => true,
		'cachingframework_cache_pages_tags' => true,
		'cachingframework_cache_pagesection' => true,
		'cachingframework_cache_pagesection_tags' => true,
		'cf_cache_hash' => true,
		'cf_cache_hash_tags' => true,
		'cf_cache_pages' => true,
		'cf_cache_pages_tags' => true,
		'cf_cache_pagesection' => true,
		'cf_cache_pagesection_tags' => true,
		'cf_extbase_object' => true,
		'cf_extbase_object_tags' => true,
		'cf_extbase_reflection' => true,
		'cf_extbase_reflection_tags' => true,
		'fe_session_data' => true,
		'fe_sessions' => true,
		'index_debug' => true,
		'index_fulltext' => true,
		'index_grlist' => true,
		'index_phash' => true,
		'index_rel' => true,
		'index_section' => true,
		'index_stat_search' => true,
		'index_stat_word' => true,
		'index_words' => true,
		'sys_be_shortcuts' => true,
		'sys_collection_entries' => true,
		'sys_dmail_maillog' => true,
		'sys_history' => true,
		'sys_lockedrecords' => true,
		'sys_log' => true,
		'sys_preview' => true,
		'sys_refindex' => true,
		'sys_registry' => true,
		'sys_ter' => true,
		'sys_workspace' => true,
		'sys_workspace_cache' => true,
		'sys_workspace_cache_tags' => true,
		'static_tsconfig_help' => true,
		'tt_news_cache' => true,
		'tt_news_cache_tags' => true,
		'tx_extbase_cache_object' => true,
		'tx_extbase_cache_object_tags' => true,
		'tx_extbase_cache_reflection' => true,
		'tx_extbase_cache_reflection_tags' => true,
		'tx_impexp_presets' => true,
		'tx_linkvalidator_link' => true,
		'tx_scheduler_task' => true
	);
	
	/**
	 * @var array An array of table => true pairs to be ignored in snapshots.
	 */
	protected $ignoreSnapshotTables = array(
		'be_sessions' => true,
		'cache_extensions' => true,
		'cache_hash' => true,
		'cache_imagesizes' => true,
		'cache_md5params' => true,
		'cache_pages' => true,
		'cache_pagesection' => true,
		'cache_treelist' => true,
		'cache_typo3temp_log' => true,
		'cachingframework_cache_hash' => true,
		'cachingframework_cache_hash_tags' => true,
		'cachingframework_cache_pages' => true,
		'cachingframework_cache_pages_tags' => true,
		'cachingframework_cache_pagesection' => true,
		'cachingframework_cache_pagesection_tags' => true,
		'cf_cache_hash' => true,
		'cf_cache_hash_tags' => true,
		'cf_cache_pages' => true,
		'cf_cache_pages_tags' => true,
		'cf_cache_pagesection' => true,
		'cf_cache_pagesection_tags' => true,
		'cf_extbase_object' => true,
		'cf_extbase_object_tags' => true,
		'cf_extbase_reflection' => true,
		'cf_extbase_reflection_tags' => true,
		'fe_session_data' => true,
		'fe_sessions' => true,
		'tx_extbase_cache_object' => true,
		'tx_extbase_cache_object_tags' => true,
		'tx_extbase_cache_reflection' => true,
		'tx_extbase_cache_reflection_tags' => true
	);
	
	/**
	 * The extension configuration.
	 *
	 * @var array The extension config.
	 */
	protected $extensionConfiguration = null;
	
	/**
	 * The local t3lib_db.
	 *
	 * @var t3lib_db The local DB object.
	 */
	protected $localDB = null;
	
	/**
	 * The remote t3lib_db.
	 *
	 * @var t3lib_db The remote DB object.
	 */
	protected $remoteDB = null;
	
	/**
	 * The local repository.
	 *
	 * @var Tx_Contentstage_Domain_Repository_ContentRepository The local repository object.
	 */
	protected $localRepository = null;
	
	/**
	 * The remote repository.
	 *
	 * @var Tx_Contentstage_Domain_Repository_ContentRepository The remote repository object.
	 */
	protected $remoteRepository = null;

	/**
	 * The snapshot repository.
	 *
	 * @var Tx_Contentstage_Domain_Repository_SnapshotRepository
	 */
	protected $snapshotRepository;
	
	/**
	 * The cache.
	 *
	 * @var t3lib_cache_frontend_AbstractFrontend The cache.
	 */
	protected $cache = null;
	
	/**
	 * The logging object.
	 *
	 * @var Tx_CabagExtbase_Utility_Logging The logging object.
	 */
	protected $log = null;
	
	/**
	 * The diff object.
	 *
	 * @var Tx_Contentstage_Utility_Diff The diff object.
	 */
	protected $diff = null;
	
	/**
	 * The TCA utility object.
	 *
	 * @var Tx_Contentstage_Utility_Tca The TCA utility object.
	 */
	protected $tca = null;
	
	/**
	 * The page ts for the current page/user.
	 *
	 * @var array
	 */
	protected $pageTS = null;
	
	/**
	 * The minimum depth of recursion.
	 *
	 * @var int
	 */
	protected $minimumDepth = null;
	
	/**
	 * The maximum depth of recursion.
	 *
	 * @var int
	 */
	protected $maximumDepth = null;

	/**
	 * Injects the snapshot repository.
	 *
	 * @param Tx_Contentstage_Domain_Repository_SnapshotRepository $snapshotRepository
	 * @return void
	 */
	public function injectSnapshotRepository(Tx_Contentstage_Domain_Repository_SnapshotRepository $snapshotRepository) {
		$this->snapshotRepository = $snapshotRepository;
	}
	
	/**
	 * Injects the diff utility.
	 *
	 * @param Tx_Contentstage_Utility_Diff $diff The diff utility.
	 */
	public function injectDiff(Tx_Contentstage_Utility_Diff $diff = null) {
		$this->diff = $diff;
	}
	
	/**
	 * Injects the TCA utility object.
	 *
	 * @param Tx_Contentstage_Utility_Tca $diff The TCA utility object.
	 */
	public function injectTca(Tx_Contentstage_Utility_Tca $tca = null) {
		$this->tca = $tca;
	}

	/**
	 * Initialize the controller.
	 *
	 * @return void
	 */
	public function initializeAction() {
		t3lib_cache::initializeCachingFramework();
		t3lib_cache::initContentHashCache();
		$this->cache = $GLOBALS['typo3CacheManager']->getCache('cache_hash');
		
		$this->extensionConfiguration = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['contentstage']);
		define('TX_CONTENTSTAGE_USECACHE', !empty($this->extensionConfiguration['useCache']));
		define('TX_CONTENTSTAGE_CACHETIME', intval($this->extensionConfiguration['cacheTime']));
		
		$this->localDB = $GLOBALS['TYPO3_DB'];
		
		$info = $this->extensionConfiguration['remote.']['db.'];
		
		$noPconnect = $GLOBALS['TYPO3_CONF_VARS']['SYS']['no_pconnect'];
		$GLOBALS['TYPO3_CONF_VARS']['SYS']['no_pconnect'] = true;
		$this->remoteDB = t3lib_div::makeInstance('t3lib_db');
		$this->remoteDB->connectDB($info['host'], $info['user'], $info['password'], $info['database']);
		$GLOBALS['TYPO3_CONF_VARS']['SYS']['no_pconnect'] = $noPconnect;
		
		$this->initializeLog();
		
		$this->localRepository = $this->objectManager->create(
			'Tx_Contentstage_Domain_Repository_ContentRepository',
			$this->localDB,
			$this->log,
			$this->cache,
			'localRepository'
		);
		$this->localRepository->setFolder(PATH_site);
		
		$this->remoteRepository = $this->objectManager->create(
			'Tx_Contentstage_Domain_Repository_ContentRepository',
			$this->remoteDB,
			$this->log,
			$this->cache,
			'remoteRepository'
		);
		$this->remoteRepository->setFolder($this->extensionConfiguration['remote.']['folder']);
		
		$this->initializeDepth();
		$this->initializeIgnoreTables();
	}
	
	/**
	 * Initializes the ignore tables.
	 *
	 * @return void
	 */
	protected function initializeIgnoreTables() {
		$this->applyTablesToIndex($this->ignoreSyncTables, $this->extensionConfiguration['tables.']['doNotSync']);
		
		$this->applyTablesToIndex($this->ignoreSnapshotTables, $this->extensionConfiguration['tables.']['doNotSnapshot']);
		
		$pageTS = $this->getPageTS();
		
		$this->applyTableIndexToIndex($this->ignoreSyncTables, $pageTS['doNotSync.']);
		
		$this->applyTableIndexToIndex($this->ignoreSnapshotTables, $pageTS['doNotSnapshot.']);
		
		$this->snapshotRepository->setIgnoreSnapshotTables($this->ignoreSnapshotTables);
		
		$this->tca->initializeIgnoreFields($pageTS['doNotDisplay.']);
	}
	
	/**
	 * Initializes the recursion depth.
	 *
	 * @return void
	 */
	protected function initializeDepth() {
		$depth = t3lib_div::_GP('depth');
		$sessionDepth = $GLOBALS['BE_USER']->getSessionData('tx_' . strtolower($this->extensionName) . '_depth');
		$pageTS = $this->getPageTS();
		
		if (is_numeric($depth) && intval($depth) >= -1) {
			$depth = intval($depth);
		} else if (is_numeric($sessionDepth) && intval($sessionDepth) >= -1) {
			$depth = intval($sessionDepth);
		} else {
			$depth = is_numeric($pageTS['defaultDepth']) ? intval($pageTS['defaultDepth']) : -1;
		}
		
		$range = array(
			is_numeric($pageTS['minimumDepth']) ? intval($pageTS['minimumDepth']) : 0,
			is_numeric($pageTS['maximumDepth']) ? intval($pageTS['maximumDepth']) : PHP_INT_MAX
		);
		$this->maximumDepth = max(0, max($range));
		$this->minimumDepth = max($this->maximumDepth === PHP_INT_MAX ? -1 : 0, min($range));
		
		if ($depth !== -1 || $this->maximumDepth !== PHP_INT_MAX) {
			$depth = max(min($this->maximumDepth, $depth), $this->minimumDepth);
		}
		
		if ($depth != $sessionDepth) {
			$GLOBALS['BE_USER']->setAndSaveSessionData('tx_' . strtolower($this->extensionName) . '_depth', $depth);
		}
		$this->localRepository->setDepth($depth);
		$this->remoteRepository->setDepth($depth);
	}
	
	protected function initializeLog() {
		if ($this->log === null) {
			$this->log = $this->objectManager->create('Tx_CabagExtbase_Utility_Logging');
		}
		
		$this->log->setDefaultTag($this->extensionName)
			->setWriteSeverity(400)
			->addOutput($this->extensionName . 'Flash', 'flash', array(
				'flashMessageContainer' => $this->flashMessageContainer,
				'severity' => Tx_CabagExtbase_Utility_Logging::OK
			));
		
		
		if ($this->extensionConfiguration['logging.']['file.']['enable']) {
			$this->log->addOutput($this->extensionName . 'File', 'file', $this->extensionConfiguration['logging.']['file.']);
		}
		if ($this->extensionConfiguration['logging.']['mail.']['enable']) {
			$this->log->addOutput($this->extensionName . 'Mail', 'mail', $this->extensionConfiguration['logging.']['mail.']);
		}
		if ($this->extensionConfiguration['logging.']['devLog.']['enable']) {
			$this->log->addOutput($this->extensionName . 'DevLog', 'devlog', $this->extensionConfiguration['logging.']['devLog.']);
		}
		$this->log->log($this->translate('info.init'), Tx_CabagExtbase_Utility_Logging::INFORMATION);
	}
	
	protected function getPageTS() {
		if ($this->pageTS === null) {
			$id = intval(t3lib_div::_GP('id'));
			if ($id <= 0) {
				return array();
			}
			
			$rootline = array();
			$c = PHP_INT_MAX;
			foreach ($this->localRepository->getRootline($id) as $uid => $page) {
				$rootline[$c] = &$page;
				$c--;
			}
			$pageTS = t3lib_befunc::getPagesTSconfig($id, $rootline);
			$this->pageTS = $pageTS['tx_' . strtolower($this->extensionName) . '.'];
		}
		
		return $this->pageTS;
	}
	
	/**
	 * Apply the given tables to the index.
	 *
	 * @param array $index The index (array of value => true pairs).
	 * @param string $tables The comma separated list of tables.
	 * @return void
	 */
	protected function applyTablesToIndex(array &$index, $tables) {
		foreach (t3lib_div::trimExplode(',', $tables, true) as $table) {
			$index[$table] = true;
		}
	}
	
	/**
	 * Apply the given table index to the index.
	 *
	 * @param array $index The index (array of value => true pairs).
	 * @param array $tables The index (array of table => true/false).
	 * @return void
	 */
	protected function applyTableIndexToIndex(array &$index, &$tableIndex = array()) {
		if (!is_array($tableIndex)) {
			return;
		}
		foreach ($tableIndex as $table => &$ignore) {
			if ($ignore) {
				$index[$table] = true;
			}
		}
	}
	
	/**
	 * Filters a given array of tables with the ignore tables.
	 *
	 * @param array $tables The tables to filter.
	 * @param array $ignoreTables The index of 'tablename' => true pairs to be ignored.
	 * @return array The $tables minus the $ignoreTables
	 */
	protected function filterTables(array $tables, array $ignoreTables) {
		$result = array();
		foreach ($tables as $table) {
			if (!isset($ignoreTables[$table]) || !$ignoreTables[$table]) {
				$result[] = $table;
			}
		}
		return $result;
	}
	
	/**
	 * Returns the extension configuration.
	 *
	 * @return array The extension config.
	 */
	public function getExtensionConfiguration() {
		return $this->extensionConfiguration;
	}
	
	/**
	 * Returns the local t3lib_db.
	 *
	 * @return t3lib_db The local DB object.
	 */
	public function getLocalDB() {
		return $this->localDB;
	}
	
	/**
	 * Returns the remote t3lib_db.
	 *
	 * @return t3lib_db The remote DB object.
	 */
	public function getRemoteDB() {
		return $this->remoteDB;
	}
	
	/**
	 * Returns the local repository.
	 *
	 * @return Tx_Contentstage_Domain_Repository_ContentRepository The local repository object.
	 */
	public function getLocalRepository() {
		return $this->localRepository;
	}
	
	/**
	 * Returns the remote repository.
	 *
	 * @return Tx_Contentstage_Domain_Repository_ContentRepository The remote repository object.
	 */
	public function getRemoteRepository() {
		return $this->remoteRepository;
	}
	
	/**
	 * Returns the info needed to connect to the local database.
	 *
	 * @return array The login info for the local database.
	 */
	public function getLocalDbInfo() {
		$info = array(
			'user' => TYPO3_db_username,
			'password' => TYPO3_db_password,
			'host' => TYPO3_db_host,
			'database' => TYPO3_db
		);
		
		if ($portInfo = strstr($info['host'], ':')) {
			$info['port'] = intval($portInfo);
			$info['host'] = strstr($info['host'], ':', true);
		}
		return $info;
	}
}
?>