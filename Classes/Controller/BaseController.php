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
		'tx_contentstage_domain_model_review' => true,
		'tx_contentstage_domain_model_reviewed' => true,
		'tx_contentstage_domain_model_state' => true,
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
		'tx_contentstage_domain_model_review' => true,
		'tx_contentstage_domain_model_reviewed' => true,
		'tx_contentstage_domain_model_state' => true,
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
	 * reviewRepository
	 *
	 * @var Tx_Contentstage_Domain_Repository_ReviewRepository
	 */
	protected $reviewRepository;

	/**
	 * BackendUserGroupRepository
	 *
	 * @var Tx_Contentstage_Domain_Repository_BackendUserGroupRepository
	 */
	protected $backendUserGroupRepository;

	/**
	 * BackendUserRepository
	 *
	 * @var Tx_Contentstage_Domain_Repository_BackendUserRepository
	 */
	protected $backendUserRepository;
	
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
	public $log = null;
	
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
	 * The current page id.
	 *
	 * @var int
	 */
	protected $page = 0;
	
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
	 * Review configuration.
	 *
	 * @var array
	 */
	protected $reviewConfiguration = null;
	
	/**
	 * Review record, if present.
	 *
	 * @var Tx_Contentstage_Domain_Model_Review
	 */
	protected $review = null;
	
	/**
	 * The active backend user.
	 *
	 * @var Tx_Contentstage_Domain_Model_BackendUser
	 */
	protected $activeBackendUser = null;

	/**
	 * Holds all hooks for this file
	 * 
	 * @var array
	 */
	protected $hookObjectsArray = array();

	/**
	 * injectReviewRepository
	 *
	 * @param Tx_Contentstage_Domain_Repository_ReviewRepository $reviewRepository
	 * @return void
	 */
	public function injectReviewRepository(Tx_Contentstage_Domain_Repository_ReviewRepository $reviewRepository) {
		$this->reviewRepository = $reviewRepository;
		$querySettings = t3lib_div::makeInstance('Tx_Extbase_Persistence_Typo3QuerySettings');
		$querySettings->setRespectStoragePage(false);
		$this->reviewRepository->setDefaultQuerySettings($querySettings);
	}

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
	 * Injects the BackendUserGroup repository.
	 *
	 * @param Tx_Contentstage_Domain_Repository_BackendUserGroupRepository $backendUserGroupRepository
	 * @return void
	 */
	public function injectBackendUserGroupRepository(Tx_Contentstage_Domain_Repository_BackendUserGroupRepository $backendUserGroupRepository) {
		$this->backendUserGroupRepository = $backendUserGroupRepository;
		$querySettings = t3lib_div::makeInstance('Tx_Extbase_Persistence_Typo3QuerySettings');
		$querySettings->setRespectStoragePage(false);
		$this->backendUserGroupRepository->setDefaultQuerySettings($querySettings);
	}

	/**
	 * Injects the BackendUser repository.
	 *
	 * @param Tx_Contentstage_Domain_Repository_BackendUserRepository $backendUserRepository
	 * @return void
	 */
	public function injectBackendUserRepository(Tx_Contentstage_Domain_Repository_BackendUserRepository $backendUserRepository) {
		$this->backendUserRepository = $backendUserRepository;
		$querySettings = t3lib_div::makeInstance('Tx_Extbase_Persistence_Typo3QuerySettings');
		$querySettings->setRespectStoragePage(false);
		$this->backendUserRepository->setDefaultQuerySettings($querySettings);
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
		$this->extensionName = 'Contentstage';
		$this->activeBackendUser = $this->backendUserRepository->findByUid($GLOBALS['BE_USER']->user['uid']);
		if ($this->activeBackendUser === null) {
			// should never happen
			die($this->translate('error.noBackendUser'));
		}
		
		t3lib_cache::initializeCachingFramework();
		t3lib_cache::initContentHashCache();
		$this->cache = $GLOBALS['typo3CacheManager']->getCache('cache_hash');
		
		$this->extensionConfiguration = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['contentstage']);
		define('TX_CONTENTSTAGE_USECACHE', !empty($this->extensionConfiguration['useCache']));
		define('TX_CONTENTSTAGE_CACHETIME', intval($this->extensionConfiguration['cacheTime']));
		
		$this->localDB = $GLOBALS['TYPO3_DB'];
		
		$this->page = intval(t3lib_div::_GP('id'));
		//t3lib_BEfunc::openPageTree($this->page, false);
		
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
		$this->localRepository->setCurrentPage($this->page);
		
		$this->remoteRepository = $this->objectManager->create(
			'Tx_Contentstage_Domain_Repository_ContentRepository',
			$this->remoteDB,
			$this->log,
			$this->cache,
			'remoteRepository'
		);
		$this->remoteRepository->setFolder($this->extensionConfiguration['remote.']['folder']);
		$this->remoteRepository->setCurrentPage($this->page);
		
		$pageTS = $this->getPageTS();
		$this->localRepository->setUseHttps($pageTS['useHttpsLocal']);
		$this->localRepository->setOverrideDomain($pageTS['overrideDomainLocal']);
		$this->remoteRepository->setUseHttps($pageTS['useHttpsRemote']);
		$this->remoteRepository->setOverrideDomain($pageTS['overrideDomainRemote']);
		
		$this->initializeDepth();
		$this->initializeIgnoreTables();
		$this->initializeReview();

		$hookFilename = 'contentstage/Classes/Controller/' . $this->request->getControllerName() . 'Controller.php';
		if (is_array($GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][$hookFilename][$this->request->getControllerActionName()])) {
			foreach ($GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][$hookFilename][$this->request->getControllerActionName()] as $classRef) {
				$this->hookObjectsArray[] = &t3lib_div::getUserObj($classRef);
			}
		}
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
		
		$this->applyTableIndexToIndex($this->ignoreSyncTables, $pageTS['doNotSync']);
		
		$this->applyTableIndexToIndex($this->ignoreSnapshotTables, $pageTS['doNotSnapshot']);
		
		$this->snapshotRepository->setIgnoreSnapshotTables($this->ignoreSnapshotTables);
		
		$this->tca->initializeIgnoreFields($pageTS['doNotDisplay']);
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
			is_numeric($pageTS['maximumDepth']) ? (intval($pageTS['maximumDepth']) === -1 ? PHP_INT_MAX : intval($pageTS['maximumDepth'])) : PHP_INT_MAX
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
	
	/**
	 * Initializes the logging class.
	 *
	 * @return void
	 */
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
	
	/**
	 * Initializes the review system.
	 *
	 * @return void
	 */
	protected function initializeReview() {
		$pageTS = $this->getPageTS();
		
		if (empty($pageTS['review'])) {
			return;
		}
		
		$this->reviewConfiguration = is_array($pageTS['review']) ? $pageTS['review'] : array();
		if (!isset($this->reviewConfiguration['required'])) {
			$this->reviewConfiguration['required'] = 2;
		}
		$this->reviewConfiguration['required'] = max(1, intval($this->reviewConfiguration['required']));
		
		$this->review = $this->reviewRepository->findActive($this->page);
		if ($this->review === null) {
			$this->review = $this->objectManager->create('Tx_Contentstage_Domain_Model_Review');
			
			$this->review->setPage($this->page);
			$this->review->setLevels($this->localRepository->getDepth());
			$this->review->setRequired($this->reviewConfiguration['required']);
			$this->review->setAutoPush(!empty($this->reviewConfiguration['defaultAutoPush']));
		}
	}
	
	/**
	 * Returns the page TypoScript configuration.
	 *
	 * @return array The pageTS
	 */
	protected function getPageTS() {
		if ($this->pageTS === null) {
			$id = $this->page;
			if ($id <= 0) {
				return array();
			}
			
			$rootline = array();
			foreach ($this->localRepository->getRootline($id) as $uid => $page) {
				array_unshift($rootline, $page);
			}
			
			$pageTS = t3lib_befunc::getPagesTSconfig($id, $rootline);
			$this->pageTS = Tx_Extbase_Utility_TypoScript::convertTypoScriptArrayToPlainArray($pageTS['tx_contentstage.']);
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
	 * @deprecated Mysql login credentials gets used at other locations also so this method was moved to be a utility method. Use the utility method directly.
	 */
	public function getLocalDbInfo() {
		return Tx_Contentstage_Utility_Shell::getLocalDbInfo();
	}
	
	/**
	 * Assign the review records to the view. Finds all if no reviews given.
	 *
	 * @param mixed $reviews The reviews to assign. Must be array or array like object.
	 * @return void
	 */
	protected function assignReviews($reviews = null) {
		if (!is_array($reviews) || !($reviews instanceof Traversable)) {
			if ($this->activeBackendUser->isAdmin()) {
				$reviews = $this->reviewRepository->findAllActive();
			} else {
				$mountpoints = $this->activeBackendUser->getDbMountpointsRecursive();
				$pageUids = array();
				
				foreach ($mountpoints as $mountpoint) {
					$pageUids = array_merge($pageUids, $this->localRepository->getPageTreeUids($mountpoint, -1));
				}
				$reviews = $this->reviewRepository->findActiveInPages($pageUids);
			}
		}
		
		$maximumReviewers = 0;
		foreach ($reviews as $review) {
			$maximumReviewers = max(max($maximumReviewers, $review->getRequired()), $review->getReviewed()->count());
		}
		$this->view->assign('reviewerIndices', range(1, $maximumReviewers));
		$this->view->assign('maximumReviewers', $maximumReviewers);
		$this->view->assign('reviews', $reviews);
		$this->view->assign('activeReview', $this->review);
		$this->view->assign('reviewConfiguration', $this->reviewConfiguration);
	}
	
	/**
	 * Assign the possible review backend users to the view.
	 *
	 * @return void
	 */
	protected function assignReviewers() {
		$pageTS = $this->getPageTS();
		$backendUsers = array();
		
		$rootlineIndex = array();
		foreach ($this->localRepository->getRootline($this->page) as $rootlinePage) {
			$rootlineIndex[$rootlinePage['uid']] = $rootlinePage;
		}
		
		$groupIndex = array();
		foreach (t3lib_div::intExplode(',', $this->reviewConfiguration['groups'], true) as $groupUid) {
			$group = $this->backendUserGroupRepository->findByUid($groupUid);
			if ($group !== null) {
				$groupIndex[$group->getUid()] = $group;
			}
		}
		
		foreach ($this->backendUserRepository->findAll() as $backendUser) {
			$mountpointMatches = false;
			foreach ($backendUser->getDbMountpointsRecursive() as $mountpoint) {
				if (isset($rootlineIndex[$mountpoint])) {
					$mountpointMatches = true;
					break;
				}
			}
			
			$groupMatches = true;
			if (count($groupIndex) > 0) {
				$groupMatches = false;
				foreach ($backendUser->getGroups() as $group) {
					if (isset($groupIndex[$group->getUid()])) {
						$groupMatches = true;
						break;
					}
				}
			}
			
			if ($mountpointMatches && $groupMatches) {
				$backendUsers[] = $backendUser;
			}
		}
		
		usort($backendUsers, function($a, $b) {
			return strcasecmp($a->getName(), $b->getName());
		});
		$this->view->assign('backendUsers', $backendUsers);
		$this->view->assign('activeBackendUser', $this->activeBackendUser);
	}
	
	/**
	 * Assigns the depth values as an array to the view.
	 *
	 * @return void
	 */
	protected function assignDepth() {
		$pageTS = $this->getPageTS();
		$depthOptions = $pageTS['depthOptions'];
		if (empty($depthOptions)) {
			$depthOptions = '0,1,2,3,4,5,6,7,8,9,-1';
		}
		$result = array();
		
		foreach (t3lib_div::intExplode(',', $depthOptions) as $depth) {
			if ($depth >= $this->minimumDepth && $depth <= $this->maximumDepth || ($depth == -1 && $this->maximumDepth === PHP_INT_MAX)) {
				$result[$depth] = $depth;
			}
		}
		
		if (isset($result[0])) {
			$result[0] = $this->translate('depth.this');
		}
		if (isset($result['-1'])) {
			$result['-1'] = $this->translate('depth.infinite');
		}
		
		$this->view->assign('depthOptions', $result);
		$this->view->assign('minimumDepth', $this->minimumDepth);
		$this->view->assign('maximumDepth', $this->maximumDepth);
	}
	
	/**
	 * Sends a mail.
	 *
	 * @param string $key Typoscript key under where to find the configuration.
	 * @param mixed $recipients Either array with email => name pairs or commaseparated list of emails.
	 * @param string $template Path to the template file.
	 * @param array $assign Key => value pairs to be assigned.
	 * @param array $attachements An array of file paths with attachments.
	 *
	 * @return boolean Whether or not the mail was sent.
	 */
	protected function sendMail($key, $recipients, array $assign = array(), array $attachments = array()) {
		$pageTS = $this->getPageTS();
		$configuration = t3lib_div::array_merge_recursive_overrule($pageTS['mails']['default'], $pageTS['mails'][$key]);
		
		if (
			empty($configuration)
			|| empty($configuration['templateFile'])
			|| empty($configuration['from'])
			|| empty($configuration['fromName'])
		) {
			return false;
		}
		
		$mail = $this->objectManager->create(
			'Tx_CabagExtbase_Utility_Mail',
			$this->controllerContext,
			$configuration['templateFile'],
			$pageTS
		)->setUseSwiftmailer(true);
		
		foreach ($assign as $k => $v) {
			$mail->assign($k, $v);
		}
		$mail->assign('activeBackendUser', $this->activeBackendUser);
		$mail->assign('pageUid', $this->page);
		$mail->assign('activeReview', $this->review);
		$mail->assign('localRootline', $this->localRepository->getRootline($this->page));
		$mail->assign('remoteRootline', $this->remoteRepository->getRootline($this->page));
		$mail->assign('localDomain', $this->localRepository->getDomain($this->page));
		$mail->assign('remoteDomain', $this->remoteRepository->getDomain($this->page));
		$mail->assign('comment', $this->request->hasArgument('comment') ? $this->request->getArgument('comment') : '');
		
		foreach ($attachments as $attachment) {
			$mail->addAttachment($attachment);
		}
		
		if (is_array($recipients) && isset($recipients[''])) {
			unset($recipients['']);
		}
		
		$to = array();
		if (is_array($configuration['to'])) {
			foreach ($configuration['to'] as $toConfig) {
				if (!empty($toConfig['mail'])) {
					$to[$toConfig['mail']] = empty($toConfig['name']) ? $toConfig['mail'] : $toConfig['name'];
				}
			}
		}
		if (is_array($recipients)) {
			$recipients = array_merge($to, $recipients);
		} else if (is_string($recipients)) {
			if (!empty($recipients)) {
				$to[$recipients] = 1;
			}
			$recipients = implode(',', array_keys($to));
		}
		
		$ok = $mail->sendMail(
			$recipients,
			$this->translate('mails.' . $key . '.subject'),
			$configuration['from'],
			$configuration['fromName'],
			$configuration['html']
		);
		
		if (!$ok) {
			$this->log->log($mail->getLastMessage(), Tx_CabagExtbase_Utility_Logging::WARNING, array('key' => $key, 'recipients' => $recipients, 'configuration' => $configuration));
		}
		
		return $ok;
	}
	
	/**
	 * Sends a mail concerning a review and logs directly if the mail was sent.
	 *
	 * @param string $key Typoscript key under where to find the configuration.
	 * @param Tx_Contentstage_Domain_Model_Review $review The review to send the mail for.
	 *
	 * @return boolean Whether or not the mail was sent.
	 */
	protected function sendReviewMailAndLog($key, Tx_Contentstage_Domain_Model_Review $review = null) {
		$pageTS = $this->getPageTS();
		$tsKey = 'review' . ucfirst($key);
		if (empty($key) || !isset($pageTS['mails'][$tsKey]) || $review === null) {
			$this->log->log($this->translate('info.review.mail.error'), Tx_CabagExtbase_Utility_Logging::WARNING);
			return false;
		}
		
		$configuration = t3lib_div::array_merge_recursive_overrule($pageTS['mails']['default'], $pageTS['mails'][$tsKey]);
		$recipients = array();
		if (!empty($configuration['sendToReviewers'])) {
			$recipients = $review->getRecipients(!empty($this->reviewConfiguration['sendMailToCurrentUser']));
		}
		
		$ok = $this->sendMail($tsKey, $recipients, array('review' => $review));
		if ($ok) {
			$this->log->log($this->translate('info.review.mail.success'), Tx_CabagExtbase_Utility_Logging::OK);
		} else {
			$this->log->log($this->translate('info.review.mail.error'), Tx_CabagExtbase_Utility_Logging::WARNING);
		}
		return $ok;
	}
}
?>