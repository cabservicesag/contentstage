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
class Tx_Contentstage_Domain_Repository_ContentRepository {
	/**
	 * @const string The cache table to write to. Might have to be changed for future versions.
	 * @see Tx_Contentstage_Eid_ClearCache::CACHE_TABLE
	 */
	const CACHE_TABLES = 'cachingframework_cache_hash,cf_cache_hash';
	
	/**
	 * @const string Type local.
	 */
	const TYPE_LOCAL = 'local';
	
	/**
	 * @const string Type remote.
	 */
	const TYPE_REMOTE = 'remote';
	
	/**
	 * @const int Insert error code.
	 */
	const ERROR_GET = 1355144402;
	
	/**
	 * @const int Insert error code.
	 */
	const ERROR_INSERT = 1354799704;
	
	/**
	 * The fields to collect for the page tree.
	 *
	 * @var array
	 */
	protected $fieldNames = array('uid', 'pid', 'title', 'deleted', 'hidden', 'tstamp', 'TSconfig');
	
	/**
	 * The associated folder.
	 *
	 * @var string
	 */
	protected $folder = '';
	
	/**
	 * The current page.
	 *
	 * @var int
	 */
	protected $currentPage = 0;
	
	/**
	 * Whether or not to use https for the domain.
	 *
	 * @var boolean
	 */
	protected $useHttps = false;
	
	/**
	 * The override domain.
	 *
	 * @var string
	 */
	protected $overrideDomain = '';
	
	/**
	 * An array of table => array of uid => true pairs for all the resolved relations.
	 *
	 * @var array
	 */
	protected $resolvedRelations = array();
	
	/**
	 * An array of table => array of uid => true pairs for all the unresolved relations.
	 *
	 * @var array
	 */
	protected $unresolvedRelations = array();
	
	/**
	 * The internal t3lib_db.
	 *
	 * @var t3lib_db
	 */
	protected $db = null;
	
	/**
	 * The recursion depth to search.
	 *
	 * @var int
	 */
	protected $depth = -1;
	
	/**
	 * The associated tag (for possible cachings).
	 *
	 * @var string
	 */
	protected $tag = '';
	
	/**
	 * The selected database.
	 *
	 * @var string
	 */
	protected $database = '';
	
	/**
	 * The cache.
	 *
	 * @var t3lib_cache_frontend_AbstractFrontend The cache.
	 */
	protected $cache = null;
	
	/**
	 * The domain cache (per call).
	 *
	 * @var array The domain cache.
	 */
	protected $domainCache = array();
	
	/**
	 * The cached full page tree.
	 *
	 * @var array
	 */
	protected $fullPageTree = null;
	
	/**
	 * The logging object.
	 *
	 * @var Tx_CabagExtbase_Utility_Logging The logging object.
	 */
	protected $log = null;
	
	/**
	 * @var Tx_Extbase_Object_ObjectManagerInterface
	 */
	protected $objectManager;
	
	/**
	 * The TCA utility object.
	 *
	 * @var Tx_Contentstage_Utility_Tca The TCA utility object.
	 */
	protected $tca = null;
	
	/**
	 * The file messages found.
	 *
	 * @var array
	 */
	protected $fileMessages = array();

	/**
	 * A per call cache of field names.
	 *
	 * @var array
	 */
	protected $fieldNameCache = array();
	
	/**
	 * A per call cache of sys_file_storage.
	 *
	 * @var array
	 */
	protected $fileStorageCache = array();
	
	/**
	 * The parent controller.
	 *
	 * @var Tx_Contentstage_Controller_BaseController
	 */
	protected $parent = null;

	/**
	 * Injects the object manager
	 *
	 * @param Tx_Extbase_Object_ObjectManagerInterface $objectManager
	 * @return void
	 */
	public function injectObjectManager(Tx_Extbase_Object_ObjectManagerInterface $objectManager) {
		$this->objectManager = $objectManager;
	}
	
	/**
	 * Injects the TCA utility object.
	 *
	 * @param Tx_Contentstage_Utility_Tca $diff The TCA utility object.
	 */
	public function injectTca(Tx_Contentstage_Utility_Tca $tcaObject = null) {
		$this->tcaObject = $tcaObject;
	}
	
	/**
	 * Constructor.
	 *
	 * @param t3lib_db $db The t3lib_db connection.
	 * @param Tx_CabagExtbase_Utility_Logging $log The logging object.
	 * @param t3lib_cache_frontend_AbstractFrontend $cache The cache to use.
	 * @param string $tag The local tag to use while caching.
	 */
	public function __construct(t3lib_db $db, Tx_CabagExtbase_Utility_Logging $log, $cache, $tag = 'ContentRepository') {
		if (!($cache instanceof t3lib_cache_frontend_AbstractFrontend) && !($cache instanceof \TYPO3\CMS\Core\Cache\Frontend\VariableFrontend)) {
			throw new Exception('Cache must be of type t3lib_cache_frontend_AbstractFrontend or \\TYPO3\\CMS\\Core\\Cache\\Frontend\\VariableFrontend, ' . get_class($cache) . ' given!', 1407143798);
		}
		$this->db = $db;
		$this->log = $log;
		$this->cache = $cache;
		$this->tag = $tag;
		$res = $db->sql_query('SELECT DATABASE();');
		if (($row = $db->sql_fetch_row($res)) !== false) {
			$this->database = current($row);
		}
	}
	
	/**
	 * Set the folder.
	 *
	 * @param string $folder The associated folder.
	 * @return void
	 */
	public function setFolder($folder) {
		$folder = preg_replace(
			array(
				'/\.{2,}/',
				'#/+#',
				'#/$#'
			),
			array(
				'',
				'/',
				''
			),
			$folder
		);
		if (!is_dir($folder)) {
			throw new Exception(sprintf($this->translate('fatal.remoteFolder'), $folder), 1355153307);
		}
		$this->folder = $folder .  '/';
	}
	
	/**
	 * Get the folder.
	 *
	 * @return string The associated folder.
	 */
	public function getFolder() {
		return $this->folder;
	}
	
	/**
	 * Set the current page.
	 *
	 * @param int $currentPage The current page.
	 * @return void
	 */
	public function setCurrentPage($currentPage) {
		$this->currentPage = intval($currentPage);
	}
	
	/**
	 * Get the current page.
	 *
	 * @return int The current page.
	 */
	public function getCurrentPage() {
		return $this->currentPage;
	}
	
	/**
	 * Set the parent.
	 *
	 * @param Tx_Contentstage_Controller_BaseController $parent The parent.
	 * @return void
	 */
	public function setParent($parent) {
		$this->parent = $parent;
	}
	
	/**
	 * Get the parent.
	 *
	 * @return Tx_Contentstage_Controller_BaseController The parent.
	 */
	public function getParent() {
		return $this->parent;
	}
	
	/**
	 * Set whether or not to use https.
	 *
	 * @param boolean $useHttps Whether or not to use https.
	 * @return void
	 */
	public function setUseHttps($useHttps) {
		$this->domainCache = array();
		$this->useHttps = !empty($useHttps);
	}
	
	/**
	 * Get whether or not to use https.
	 *
	 * @return boolean Whether or not to use https.
	 */
	public function getUseHttps() {
		return $this->useHttps;
	}
	
	/**
	 * Set the override domain.
	 *
	 * @param string $overrideDomain The override domain.
	 * @return void
	 */
	public function setOverrideDomain($overrideDomain) {
		$this->overrideDomain = $overrideDomain;
	}
	
	/**
	 * Get the override domain.
	 *
	 * @return string The override domain.
	 */
	public function getOverrideDomain() {
		return $this->overrideDomain;
	}
	
	/**
	 * Returns a handle for a given file.
	 * Possibility to change to some sort of rsync://path or similar.
	 *
	 * @param string $file File relative to the $this->folder.
	 * @param array $original An original handle to copy from.
	 * @return string The absolute file handle.
	 */
	public function getFileHandle($file, $original = null) {
		if ($original === null) {
			$root = $this->folder;
			$domain = $this->getDomain($this->getCurrentPage());
		} else {
			$root = $original['root'];
			$domain = $original['domain'];
		}
		$result = array(
			'rel' => $file,
			'abs' => $root . $file,
			'relDir' => dirname($file),
			'absDir' => dirname($root . $file),
			'domain' => $domain,
			'root' => $root,
		);
		
		return $result;
	}
	
	/**
	 * Copy given file to destination.
	 * Possibility to change to some sort of rsync://path or similar in the future.
	 *
	 * @param array $fromFile File handle to copy from. Result of self::getFileHandle().
	 * @param array $toFile File handle to copy to. Result of self::getFileHandle().
	 * @return void
	 */
	public function copy($fromFile, $toFile) {
		if (!file_exists($fromFile['abs'])) {
			$this->log->log($this->translate('copy.fileMissing', $fromFile), Tx_CabagExtbase_Utility_Logging::WARNING);
			return false;
		}
		if (!is_dir(dirname($toFile['abs']))) {
			$ok = mkdir(dirname($toFile['abs']), octdec($GLOBALS['TYPO3_CONF_VARS']['BE']['folderCreateMask']), true);
			if (!$ok) {
				$this->log->log($this->translate('copy.targetFolder', array(dirname($toFile['relativePath']))), Tx_CabagExtbase_Utility_Logging::WARNING);
				return $false;
			}
		}
		return copy($fromFile['abs'], $toFile['abs']);
	}
	
	/**
	 * Compares two files and returns a message if they differ. Otherwise false.
	 *
	 * @param array $fromFile File handle to compare from. Result of self::getFileHandle().
	 * @param array $toFile File handle to compare to. Result of self::getFileHandle().
	 * @return mixed The message or false.
	 */
	public function compareFiles($fromFile, $toFile) {
		$message = false;
		$severity = Tx_CabagExtbase_Utility_Logging::INFORMATION;
		if (!file_exists($fromFile['abs'])) {
			$message = $this->translate('compare.fileSourceMissing', $fromFile);
		} else if (!file_exists($toFile['abs'])) {
			$message = $this->translate('compare.fileTargetMissing', $toFile);
		} else {
			if (filemtime($fromFile['abs']) !== filemtime($toFile['abs']) && md5_file($fromFile['abs']) !== md5_file($toFile['abs'])) {
				$message = $this->translate('compare.filesDiffer', array_merge(array_values($fromFile), array_values($toFile)));
			}
		}
		
		if ($message !== false) {
			$this->addFileMessage($fromFile, $message);
			$this->log->log($message, $severity);
			return $message;
		}
		return false;
	}
	
	/**
	 * Compares two folders and returns a message if they differ. Otherwise false.
	 *
	 * @param array $fromFile Folder handle to compare from. Result of self::getFileHandle().
	 * @param array $toFile Folder handle to compare to. Result of self::getFileHandle().
	 * @return mixed The message or false.
	 */
	public function compareFolders($fromFolder, $toFolder) {
		$message = false;
		$severity = Tx_CabagExtbase_Utility_Logging::INFORMATION;
		if (!file_exists($fromFolder['abs']) || !is_dir($fromFolder['abs'])) {
			$message = $this->translate('compare.folderSourceMissing', $fromFolder);
			$severity = Tx_CabagExtbase_Utility_Logging::WARNING;
			$this->addFileMessage($fromFolder, $message);
		} else if (!file_exists($toFolder['abs']) || !is_dir($toFolder['abs'])) {
			$message = $this->translate('compare.folderTargetMissing', $toFolder);
			$severity = Tx_CabagExtbase_Utility_Logging::WARNING;
			$this->addFileMessage($toFolder, $message);
		} else {
			$contents = self::dir_diff(
				self::scandir($fromFolder['abs']),
				self::scandir($toFolder['abs'])
			);
			$messages = array();
			
			foreach (array('source' => $fromFolder, 'target' => $toFolder) as $type => $folder) {
				$label = 'compare.file' . ucfirst($type) . 'Missing';
				foreach ($contents[$type] as $file) {
					$handle = $this->getFileHandle($folder['rel'] . '/' . $file, $folder);
					$messages[] = $m = $this->translate($label, $handle);
					$this->addFileMessage($handle, $m);
				}
			}
			foreach ($contents['both'] as $file) {
				$fromHandle = $this->getFileHandle($fromFolder['rel'] . '/' . $file, $fromFolder);
				$toHandle = $this->getFileHandle($toFolder['rel'] . '/' . $file, $toFolder);
				$m = false;
				if (is_file($path)) {
					$m = $this->compareFiles($fromHandle, $toHandle);
				} else if (is_dir($path)) {
					$m = $this->compareFolders($fromHandle, $toHandle);
				}
				if ($m !== false) {
					$messages[] = $m;
				}
			}
			
			if (count($messages) > 0) {
				$message = implode("<br />\n", $messages);
			}
		}
		
		if ($message !== false) {
			$this->log->log($message, $severity);
			return $message;
		}
		return false;
	}
	
	/**
	 * scandir() alternative, faster because it does not sort.
	 */
	protected static function scandir($dir) {
		$result = array();
		if ($dh = opendir($dir)) {
			while (($file = readdir($dh)) !== false) {
				$result[] = $file;
			}
			closedir($dh);
		}
		return $result;
	}
	
	/**
	 * Directory diff
	 *
	 * @param array $a The source array.
	 * @param array $b The target array.
	 * @return array The elements, that are only in array $a or $b.
	 */
	protected static function dir_diff($a, $b) {
		$onlyA = $onlyB = $both = array();
		foreach($a as $val) $onlyA[$val] = 1;
		foreach($b as $val) {
			if (isset($onlyA[$val])) {
				unset($onlyA[$val]);
				$both[$val] = 1;
			} else {
				$onlyB[$val] = 1;
			}
		}
		unset($both['.']);
		unset($both['..']);
		return array(
			'source' => array_keys($onlyA),
			'target' => array_keys($onlyB),
			'both' => array_keys($both)
		);
	}
	
	/**
	 * Returns the file storage for a given uid.
	 *
	 * @param int $uid The storage uid.
	 * @return array The sys_file_storage record.
	 */
	public function getFileStorage($uid) {
		$uid = intval($uid);
		if (!isset($this->fileStorageCache[$uid])) {
			$results = $this->_sql('SELECT * FROM sys_file_storage WHERE deleted = 0 AND uid = ' . $uid, true);
			$this->fileStorageCache[$uid] = array();
			if (count($results) > 0) {
				
				$results[0]['configuration_parsed'] = t3lib_div::xml2array($results[0]['configuration']);
				if ($results[0]['configuration_parsed']['data']['sDEF']['lDEF']['pathType']['vDEF'] === 'relative') {
					$results[0]['relativeBasePath'] = $results[0]['configuration_parsed']['data']['sDEF']['lDEF']['basePath']['vDEF'];
				}
				
				$this->fileStorageCache[$uid] = $results[0];
			}
		}
		return $this->fileStorageCache[$uid];
	}
	
	/**
	 * Returns the full page tree.
	 *
	 * @return array An array of the root pages with all subpages given with the key '_children' recursively.
	 */
	public function &getFullPageTree() {
		if ($this->fullPageTree !== null) {
			return $this->fullPageTree;
		}
		
		$identifier = 'tx_contentstage_ContentRepository_getFullPageTree_' . $this->tag;
		if (TX_CONTENTSTAGE_USECACHE && $this->cache->has($identifier)) {
			$this->fullPageTree = $this->cache->get($identifier);
			return $this->fullPageTree;
		}
		$this->log->log($this->translate('pageTree.rebuild', array($this->tag)), Tx_CabagExtbase_Utility_Logging::INFORMATION);
		
		$res = $this->db->exec_SELECT_queryArray(array(
			'SELECT' => implode(', ', $this->fieldNames),
			'FROM' => 'pages',
			'WHERE' => '',
			'GROUPBY' => '',
			'ORDERBY' => 'sorting ASC',
			'LIMIT' => ''
		));
		$tree = array();
		$index = array();
		$index[0]['_children'] = &$tree;
		
		while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
			$uid = intval($row['uid']);
			
			if (isset($index[$uid]) && isset($index[$uid]['_children'])) {
				$row['_children'] = &$index[$uid]['_children'];
			} else {
				$row['_children'] = array();
			}
			
			$index[$uid] = &$row;
			$index[intval($row['pid'])]['_children'][intval($row['uid'])] = &$row;
			unset($row);
		}
		
		if (TX_CONTENTSTAGE_USECACHE) {
			$this->cache->set($identifier, $index, array(), TX_CONTENTSTAGE_CACHETIME);
		}
		$this->fullPageTree = &$index;
		$this->log->log($this->translate('pageTree.rebuilt', array($this->tag)), Tx_CabagExtbase_Utility_Logging::INFORMATION);
		return $index;
	}
	
	/**
	 * Returns an array with all the page uids for the given root (includes the root if it exists). 
	 *
	 * @param int $root The page id to start from.
	 * @return array The array with the pids.
	 */
	public function getPageTreeUids($root = 0, $depth = false) {
		$root = intval($root);
		$tree = &$this->getFullPageTree();
		
		if (!isset($tree[$root])) {
			return array();
		}
		
		$result = array();
		return $this->_getPageTreeUids($root, $tree[$root], $result, $depth === false ? $this->getDepth() : $depth);
	}
	
	/**
	 * Reduce the page tree to a specified depth.
	 *
	 * @param array @tree The tree to work on.
	 * @param int $depth The depth to reduce to (negative to just leave it = infinite depth).
	 * @return array The new reduced tree.
	 */
	public function reducePageTree($tree, $depth = -1) {
		$reducedTree = array();
		
		foreach ($tree as $key => $value) {
			if ($key === '_children') {
				if ($depth !== 0 && count($value) > 0) {
					foreach ($value as $child) {
						$reducedTree[$key][] = $this->reducePageTree($child, $depth - 1);
					}
				}
			} else {
				$reducedTree[$key] = $value;
			}
		}
		
		return $reducedTree;
	}
	
	/**
	 * Returns an array with all the page uids for the given root (includes the root). 
	 *
	 * @param int $root The page id to start from.
	 * @param array $tree The recursive tree to work on.
	 * @param array $result The optional array to store the pids in the recursion.
	 * @param int $depth The optional depth to search - -1: infinite, 0: only this page, 1: only this page and its direct children etc.
	 * @return array The array with the pids.
	 */
	protected function _getPageTreeUids($root = 0, array &$tree = null, array &$result = array(), $depth = -1) {
		$identifier = 'tx_contentstage_ContentRepository_getPageTreeUids_' . $this->tag . '_' . $root . '_' . $depth;
		if (TX_CONTENTSTAGE_USECACHE && $this->cache->has($identifier)) {
			return $this->cache->get($identifier);
		}
		
		if (isset($result[$root])) {
			// RECURSIVE ARRAY DETECTED!!
			return $result;
		}
		
		if ($root > 0) {
			$result[$root] = $root;
		}
		
		if (isset($tree['_children']) && $depth !== 0) {
			foreach ($tree['_children'] as &$child) {
				$this->_getPageTreeUids(intval($child['uid']), $child, $result, $depth - 1);
			}
		}
		
		if (TX_CONTENTSTAGE_USECACHE) {
			$this->cache->set($identifier, $result, array(), TX_CONTENTSTAGE_CACHETIME);
		}
		return $result;
	}
	
	/**
	 * Returns the rootline to a given pageUid.
	 *
	 * @param int $pageUid The pageUid to get the rootline for.
	 * @param boolean $withDomain Pages in rootline get an url
	 * @return array The rootline, where the first item is the pageUid and the last item is the root. Null if pageUid not found or broken rootline!
	 */
	public function getRootline($pageUid, $withDomain = FALSE) {
		$index = &$this->getFullPageTree();
		$rootline = array();
		$pageUid = intval($pageUid);
		
		while (isset($index[$pageUid]) && $pageUid > 0) {
			$local = $index[$pageUid];
			if ($withDomain) {
				$local['url'] = $this->getPageUrl($pageUid);
			}
			unset($local['_children']);
			$rootline[$pageUid] = $local;
			$pageUid = intval($local['pid']);
		}
		
		if (empty($rootline) || $pageUid > 0) {
			// not found or broken rootline respectively
			return array();
		}
		return $rootline;
	}

	/**
	 * Returns full url for given pageUid
	 * 
	 * @param integer $pageUid
	 * @return string
	 */
	public function getPageUrl($pageUid) {
		$domain = $this->getDomain($pageUid);
		return $domain ? sprintf('%sindex.php?id=%d', $domain, $pageUid) : NULL;
	}

	/**
	 * Returns the domain to a given page.
	 *
	 * @param int $page The page uid to get the domain for.
	 * @return string The domain
	 */
	public function getDomain($page) {
		$protocol = 'http' . ($this->getUseHttps() ? 's' : '') . '://';

		if (!empty($this->overrideDomain)) {
			return $protocol . $this->overrideDomain . '/';
			
		}

		if (isset($this->domainCache[$page]) && ($this->domainCache[$page]) !== null) {
			return $this->domainCache[$page];
		}
		
		$rootline = $this->getRootline($page);
		
		if (empty($rootline)) {
			return null;
		}
		$pids = array();
		
		$orderParts = array();
		foreach ($rootline as $p) {
			$pid = intval($p['uid']);
			if ($pid <= 0) {
				break;
			}
			$pids[] = $pid;
			$orderParts[] = '(pid = ' . $pid . ') DESC';
		}
		
		$orderParts[] = 'sorting ASC';
		$rows = $this->db->exec_SELECTgetRows(
			'domainName',
			'sys_domain',
			'hidden = 0 AND !redirectTo AND pid IN (' . implode(',', $pids) . ')',
			'',
			implode(', ', $orderParts),
			1
		);
		
		$this->domainCache[$page] = count($rows) > 0 ? $protocol . $rows[0]['domainName'] . '/' : null;
				
		return $this->domainCache[$page];
	}
	
	/**
	 * Returns the list of tables from the current db.
	 *
	 * @return array An array of tableName => SHOW TABLE STATUS row pairs.
	 */
	public function getTables() {
		return $this->_sql('SHOW TABLE STATUS FROM `' . $this->database . '`', 'Name');
	}
	
	/**
	 * Returns a repository result object for the given query, but only records from within the given pagetree.
	 *
	 * @param int $root The root page to start from.
	 * @param string $table The table to query (CAN ONLY BE A SINGLE TABLE!).
	 * @param string $fields The fields to get (* by default).
	 * @param string $where The where condition (empty by default).
	 * @param string $groupBy Group the query (empty by default).
	 * @param string $orderBy Order to use on the query (uid ASC by default).
	 * @param string $limit Limit the query.
	 * @return Tx_Contentstage_Domain_Repository_Result The result object.
	 */
	public function findInPageTree($root = 0, $table, $fields = '*', $where = '', $groupBy = '', $orderBy = 'uid ASC', $limit = '') {
		$result = $this->objectManager->create('Tx_Contentstage_Domain_Repository_Result');
		$result->setRepository($this);
		$result->setTable($table);
		$whereParts = array();
		
		if ($root !== 0) {
			$pids = $this->getPageTreeUids($root);
			
			if (empty($pids)) {
				$whereParts = array('1 <> 1');
			} else {
				$whereParts = array(($table === 'pages' ? 'uid' : 'pid') . ' IN (' . implode(',', $pids) . ')');
			}
		}
		if (!empty($where)) {
			$whereParts[] = '(' . $where . ')';
		}
		
		$query = $this->db->SELECTquery($fields, $table, implode(' AND ', $whereParts), $groupBy, $orderBy, $limit);
		
		// this slows the process down imensely!
		//$this->log->log($query, Tx_CabagExtbase_Utility_Logging::INFORMATION);
		
		$resource = $this->db->sql_query($query);
		
		if (!$resource || $this->db->sql_error()) {
			throw new Exception($this->db->sql_error() . ' [Query: ' . $this->db->SELECTquery($fields, $table, implode(' AND ', $whereParts), $groupBy, $orderBy, $limit) . ']', self::ERROR_GET);
		}
		
		$result->setResource($resource);
		$result->setQuery($query);
		return $result;
	}
	
	/**
	 * Returns a repository result object for the given query, but only records from within the given pagetree. Actually only returns uid field and a hash for the rest of the fields.
	 *
	 * @param int $root The root page to start from.
	 * @param string $table The table to query (CAN ONLY BE A SINGLE TABLE!).
	 * @param string $fields The fields to get (* by default).
	 * @param string $where The where condition (empty by default).
	 * @param string $groupBy Group the query IMPORTANT: not used in this version.
	 * @param string $orderBy Order to use on the query (uid ASC by default).
	 * @param string $limit Limit the query.
	 * @param string $idFields The ID fields of the table.
	 * @return Tx_Contentstage_Domain_Repository_Result The result object.
	 */
	public function findInPageTreeHashed($root = 0, $table, $fields = '*', $where = '', $groupBy = '', $orderBy = 'uid ASC', $limit = '', $idFields = 'uid') {
		$fieldNames = array();
		if ($fields === '*' || $fields === $table . '.*') {
			if (!isset($this->fieldNameCache[$table])) {
				$this->fieldNameCache[$table] = array();
				$columns = $this->_sql('SHOW COLUMNS FROM ' . $table);
				foreach ($columns as $column) {
					$this->fieldNameCache[$table][$column['Field']] = $column['Field'];
				}
			}
			$fieldNames = $this->fieldNameCache[$table];
		} else {
			$fields = t3lib_div::trimExplode(',', $fields, true);
			$fieldNames = array_combine($fields, $fields);
		}
		
		$idFieldsArray = t3lib_div::trimExplode(',', $idFields, true);

		foreach ($idFieldsArray as $idField) {
			unset($fieldNames[$idField]);
		}
		
		$idFieldsArray = $this->mapTableToFields($table, $idFieldsArray);
		$idFields = implode(',', $idFieldsArray);

		$fields = $idFields . ', CONCAT_WS(\'///\', GROUP_CONCAT(sys_refindex.ref_table, \'///\', sys_refindex.ref_uid, \'///\', sys_refindex.ref_string SEPARATOR \'///\')' . (count($fieldNames) > 0 ? ',' : '') . implode(',', $this->mapTableToFields($table, $fieldNames)) . ') AS hash';

		$uidField = current($idFieldsArray);
		
		$result = $this->findInPageTree(
			$root,
			$table . ' LEFT JOIN sys_refindex ON (sys_refindex.recuid = ' . $uidField . ' AND sys_refindex.tablename = ' . $this->db->fullQuoteStr($table, 'sys_refindex') . ')',
			$fields,
			$where,
			$uidField,
			$orderBy,
			$limit
		);

		$result->setTable($table);
		$result->setLateBindingFields($fieldNames);

		return $result;
	}
	
	/**
	 * Maps the table name to the fields for use in SQL.
	 *
	 * @param string $table The table to map to the fields.
	 * @param array $fields The field array.
	 * @return array The fields with $table. before the name.
	 */
	protected function mapTableToFields($table, array $fields = array()) {
		$result = array();
		
		foreach ($fields as $key =>  $field) {
			$result[$key] = $table . '.' . $field;
		}
		
		return $result;
	}
	
	/**
	 * Takes the resolved relations and finds the associated sys_log entries.
	 *
	 * @return Tx_Contentstage_Domain_Repository_Result The result object.
	 */
	public function findResolvedSysLog() {
		return $this->findInPageTree(0, 'sys_log', 'sys_log.*', $this->_getSysLogWhere());
	}
	
	/**
	 * Takes the resolved relations and finds the associated sys_history entries.
	 *
	 * @return Tx_Contentstage_Domain_Repository_Result The result object.
	 */
	public function findResolvedSysHistory() {
		return $this->findInPageTree(0, 'sys_history LEFT JOIN sys_log ON (sys_log.uid = sys_history.sys_log_uid)', 'sys_history.*', $this->_getSysLogWhere());
	}
	
	/**
	 * Returns a single record. If TCA exists for a given table, then enable delete columns are taken into account.
	 *
	 * @param string $table The table to look in.
	 * @param int $uid The uid to get.
	 * @param string $fields The fields to get, all by default.
	 * @param boolean $ignoreEnable Wether or not to ignore enable field delete.
	 * @return mixed The resulting row array or false.
	 */
	public function getRecord($table, $uid, $fields = '*', $ignoreEnable = false) {
		$whereParts = array(
			$table . '.uid = ' . intval($uid)
		);
		
		$ctrl = $GLOBALS['TCA'][$table]['ctrl'];
		if (is_array($ctrl)) {

				// Delete field check:
			if ($ctrl['delete']) {
				$whereParts[] = $table . '.' . $ctrl['delete'] . '=0';
			}
		}
		
		return $this->db->exec_SELECTgetSingleRow(
			$fields,
			$table,
			implode(' AND ', $whereParts)
		);
	}
	
	/**
	 * Creates a where condition with the resolved relations to find the associated sys_log entries.
	 *
	 * @return string The generated where.
	 */
	protected function _getSysLogWhere() {
		$whereParts = array();
		
		foreach ($this->resolvedRelations as $table => &$tableData) {
			if (substr($table, 0, 2) !== '__' && !empty($tableData)) {
				$uids = array_filter(array_keys($tableData), 'is_numeric');
				$uids = empty($uids) ? array(0) : $uids;
				$whereParts[] = '(sys_log.tablename = ' . $this->db->fullQuoteStr($table, 'sys_log') . ' AND sys_log.recuid IN (' . implode(',', $uids) . '))';
			}
		}
		
		if (count($whereParts) === 0) {
			return '1 = 0';
		}
		
		return implode(' OR ', $whereParts);
	}
	
	/**
	 * Insert/update a given resource in the table. The resource must have the same fields, e.g. should be from the same table in another db.
	 *
	 * @param Tx_Contentstage_Domain_Repository_Result $resource The db resource to get the data from.
	 * @return void.
	 * @throws Exception
	 */
	public function insert(Tx_Contentstage_Domain_Repository_Result $resource) {
		$buffer = array();
		$c = 0;
		$updateTerm = false;
		$fields = array();
		$table = $resource->getTable();
		
		while (($row = $resource->nextWithRelations()) !== false) {
			$buffer[] = $row;
			//$this->log->log($table, Tx_CabagExtbase_Utility_Logging::INFORMATION, $row);
			
			if ($updateTerm === false) {
				$fields = array_keys($row);
				$update = array();
				foreach ($fields as &$field) {
					$update[] = $field . ' = VALUES(' . $field . ')';
				}
				$updateTerm = implode(',', $update);
			}
			
			if ($c % 100) {
				$this->_insert($table, $buffer, $fields, $updateTerm);
				$buffer = array();
			}
			$c++;
		}
		// if no row was looped, fields/updateTerm are not set, but that does not matter
		$this->_insert($table, $buffer, $fields, $updateTerm);
	}
	
	/**
	 * Takes a set of rows and inserts/updates them in the table.
	 *
	 * @param string $table The table to insert into.
	 * @param array $rows The db rows to insert/update.
	 * @param array $fields The fields.
	 * @param string $updateTerm The term to add for the on duplicate statement.
	 * @return void.
	 * @throws Exception
	 */
	protected function _insert($table, $rows, $fields, $updateTerm) {
		if (empty($rows)) {
			return;
		}
		$query = $this->db->INSERTmultipleRows($table, $fields, $rows) . ' ON DUPLICATE KEY UPDATE ' . $updateTerm;
		
		$this->db->sql_query($query);
		
		if ($error = $this->db->sql_error()) {
			throw new Exception($error . ' [' . $query . ']', self::ERROR_INSERT);
		}
	}
	
	/**
	 * Adds relation dependencies.
	 *
	 * @param array $relations An array of table => array of uids.
	 * @param boolean $force Force the relations already synced.
	 * @return void
	 */
	public function addRelations(array &$relations, $force = false) {
		foreach ($relations as $table => &$ignored) {
			if (substr($table, 0, 2) === '__') {
				if ($table !== '__FILE' && $table !== '__FOLDER') {
					unset($relations[$table]);
				}
			}
		}
		foreach ($relations as $table => &$uidArray) {
			foreach ($uidArray as $uid => &$ignored) {
				if ($force) {
					$this->resolvedRelations[$table][$uid] = true;
					if (isset($this->unresolvedRelations[$table][$uid])) {
						unset($this->unresolvedRelations[$table][$uid]);
					}
				} else if (!$this->resolvedRelations[$table][$uid]) {
					$this->unresolvedRelations[$table][$uid] = true;
				}
			}
		}
	}
	
	/**
	 * Returns the unresolved dependency relations.
	 *
	 * @return array An array of table => array of uid => true.
	 */
	public function getUnresolvedRelations() {
		return $this->unresolvedRelations;
	}
	
	/**
	 * Returns the resolved dependency relations.
	 *
	 * @return array An array of table => array of uid => true.
	 */
	public function getResolvedRelations() {
		return $this->resolvedRelations;
	}
	
	/**
	 * Sets all relations to synced and returns the amount of relations that were set.
	 *
	 * @return int The amount of relations changed.
	 */
	public function setRelationsSynced() {
		$count = 0;
		
		foreach ($this->unresolvedRelations as $table => &$tableData) {
			foreach ($tableData as $uid => $ignored) {
				$this->resolvedRelations[$table][$uid] = true;
				
				$count++;
			}
		}
		
		unset($this->unresolvedRelations);
		$this->unresolvedRelations = array();
		return $count;
	}
	
	/**
	 * Sets the relation to the table/uid synced (this is a performance optimization and technically not needed).
	 *
	 * @param string $table The table.
	 * @param string $uid The uid (could be a file).
	 * @return void
	 */
	public function setRelationSynced($table, $uid) {
		$this->resolvedRelations[$table][$uid] = true;
		if (isset($this->unresolvedRelations[$table][$uid])) {
			unset($this->unresolvedRelations[$table][$uid]);
		}
	}
	
	/**
	 * This should only be used for a remote system!
	 * Clears the cache for the given root.
	 * Note: There must be a sys_domain record in the given rootline! (This does not work for root = 0!).
	 *
	 * @param int $root The root to clear from.
	 * @param boolean $clearAll Whether to clear all caches.
	 * @return void
	 * @throws Exception
	 */
	public function clearCache($root, $clearAll = false) {
		$domain = $this->getDomain($root);
		if ($clearAll) {
			$content = 'ALL';
		} else {
			$pids = $this->getPageTreeUids($root);
			$content = is_array($pids) ? implode(',', $pids) : '';
		}
		
		if (empty($content) || $domain === null) {
			// do nothing
			return;
		}
		
		$hash = t3lib_div::getRandomHexString(32);
		$fields = array(
			'identifier' => $hash,
			'crdate' => time(),
			'content' => serialize($content),
			'lifetime' => 120,
			'expires' => time() + 120
		);
		
		foreach (t3lib_div::trimExplode(',', self::CACHE_TABLES, true) as $table) {
			$res = $this->db->sql_query('SHOW TABLES LIKE \'' . $table . '\'');
			if ($this->db->sql_num_rows($res) > 0) {
				break;
			}
		}
		
		if ($table === 'cf_cache_hash') {
			unset($fields['crdate']);
			unset($fields['lifetime']);
		} else {
			unset($fields['expires']);
		}
		$this->db->exec_INSERTquery($table, $fields);
        
		$sqlError = $this->db->sql_error();
        if (!empty($sqlError)) {
        	throw new Exception($sqlError, 1356616552);
        }
		
		$url = $domain . 'index.php?eID=tx_contentstage&hash=' . $hash;
		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);                                                                     
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
		
		$extensionConfiguration = $this->getParent()->getExtensionConfiguration();
		$sslVersion = intval($extensionConfiguration['remote.']['sslVersion']);
		if ($sslVersion === 2 || $sslVersion === 3) {
			curl_setopt($ch, CURLOPT_SSLVERSION, $sslVersion);
		}

		$result = curl_exec($ch);
        $data = json_decode($result);

        if (empty($data->success)) {
        	$errors = array_map(function ($value) { return $value->message; }, $data->errors);
        	throw new Exception('[' . $url . '] ' . implode(', ', $errors), 1356616552);
        }
	}
	
	/**
	 * Reset the caches of this repository. Needed when pushing to reset the remote page tree etc.
	 *
	 * @return void
	 */
	public function clearApplicationCaches() {
		$this->fullPageTree = null;
		$this->domainCache = array();
		
		$identifier = 'tx_contentstage_ContentRepository_getFullPageTree_' . $this->tag;
		if (TX_CONTENTSTAGE_USECACHE && $this->cache->has($identifier)) {
			$this->cache->remove($identifier);
		}
	}
	
	/**
	 * Returns the internal t3lib_db object. Only for internal use!
	 *
	 * @return t3lib_db The db.
	 * @internal
	 */
	public function _getDb() {
		return $this->db;
	}
	
	/**
	 * Directly execute a query on the db and return the result.
	 *
	 * @param string $query The query.
	 * @param mixed $result If set to false, no result is returned. If set to true, an array with the rows is returned. If set to a string, an associated array is returned, where the $row[$result] is used as the key.
	 * @return array The assoc return array.
	 * @internal
	 */
	public function _sql($query, $result = true) {
		$resource = $this->db->sql_query($query);
		
		if ($resource === null) {
			// mysqli 6.2
			return;
		}
		
		if ($result === false) {
			if ($resource !== null) {
				$this->db->sql_free_result($resource);
			}
			return;
		}
		
		if ($resource === null) {
			return array();
		}
		
		$output = array();
		if ($result === true) {
			while (($row = $this->db->sql_fetch_assoc($resource)) !== false) {
				$output[] = $row;
			}
		} else {
			$result = (string)$result;
			while (($row = $this->db->sql_fetch_assoc($resource)) !== false) {
				$output[$row[$result]] = $row;
			}
		}
		$this->db->sql_free_result($resource);
		return $output;
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
	 * Set the maximum recursion depth to search for.
	 *
	 * @param int $depth The recursion depth.
	 * @return void
	 */
	public function setDepth($depth) {
		$this->depth = max(-1, intval($depth));
	}
	
	/**
	 * Get the maximum recursion depth to search for.
	 *
	 * @return int The recursion depth.
	 */
	public function getDepth() {
		return $this->depth;
	}
	
	/**
	 * Set the file messages.
	 *
	 * @param array $fileMessages The file messages.
	 * @return void
	 */
	public function setFileMessages($fileMessages) {
		$this->fileMessages = $fileMessages;
	}
	
	/**
	 * Get the file messages.
	 *
	 * @return int The file messages.
	 */
	public function getFileMessages() {
		return $this->fileMessages;
	}
	
	/**
	 * Add a file message.
	 *
	 * @param array $handle The file handle.
	 * @param string $message The file message.
	 * @return void
	 */
	public function addFileMessage($handle, $fileMessage) {
		$this->fileMessages[$handle['abs']] = array(
			'handle' => $handle,
			'message' => $fileMessage,
		);
	}
	
	/**
	 * Get the tag.
	 *
	 * @return string The tag.
	 */
	public function getTag() {
		return $this->tag;
	}
}