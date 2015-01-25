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
	const CACHE_TABLE = 'cachingframework_cache_hash';
	
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
	 * The associated tag (for possible cachings).
	 *
	 * @var string
	 */
	protected $tag = '';
	
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
	 * @var Tx_Extbase_Object_ObjectManagerInterface
	 */
	protected $objectManager;

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
	 * Constructor.
	 *
	 * @param t3lib_db $db The t3lib_db connection.
	 * @param Tx_CabagExtbase_Utility_Logging $log The logging object.
	 * @param t3lib_cache_frontend_AbstractFrontend $cache The cache to use.
	 * @param string $tag The local tag to use while caching.
	 */
	public function __construct(t3lib_db $db, Tx_CabagExtbase_Utility_Logging $log, t3lib_cache_frontend_AbstractFrontend $cache, $tag = 'ContentRepository') {
		$this->db = $db;
		$this->log = $log;
		$this->cache = $cache;
		$this->tag = $tag;
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
	 * Returns a handle for a given file.
	 * Possibility to change to some sort of rsync://path or similar.
	 *
	 * @param string $file File relative to the $this->folder.
	 * @return string The absolute file handle.
	 */
	public function getFileHandle($file) {
		return $this->folder . $file;
	}
	
	/**
	 * Copy given file to destination.
	 * Possibility to change to some sort of rsync://path or similar.
	 *
	 * @param string $fromFile File handle to copy from.
	 * @param string $toFile File handle to copy to.
	 * @return void
	 */
	public function copy($fromFile, $toFile) {
		if (!file_exists($fromFile)) {
			$this->log->log($this->translate('copy.fileMissing', array($fromFile)), Tx_CabagExtbase_Utility_Logging::WARNING);
			return false;
		}
		if (!is_dir(dirname($toFile))) {
			$ok = mkdir(dirname($toFile), octdec($GLOBALS['TYPO3_CONF_VARS']['BE']['folderCreateMask']), true);
			if (!$ok) {
				$this->log->log($this->translate('copy.targetFolder', array(dirname($toFile))), Tx_CabagExtbase_Utility_Logging::WARNING);
				return $false;
			}
		}
		return copy($fromFile, $toFile);
	}
	
	/**
	 * Returns the full page tree.
	 *
	 * @return array An array of the root pages with all subpages given with the key '_children' recursively.
	 */
	public function &getFullPageTree() {
		$identifier = 'tx_contentstage_ContentRepository_getFullPageTree_' . $this->tag;
		if (TX_CONTENTSTAGE_USECACHE && $this->cache->has($identifier)) {
			return $this->cache->get($identifier);
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
		$this->log->log($this->translate('pageTree.rebuilt', array($this->tag)), Tx_CabagExtbase_Utility_Logging::INFORMATION);
		return $index;
	}
	
	/**
	 * Returns an array with all the page uids for the given root (includes the root if it exists). 
	 *
	 * @param int $root The page id to start from.
	 * @return array The array with the pids.
	 */
	public function getPageTreeUids($root = 0) {
		$root = intval($root);
		$tree = &$this->getFullPageTree();
		
		if (!isset($tree[$root])) {
			return array();
		}
		
		return $this->_getPageTreeUids($root, $tree[$root]);
	}
	
	/**
	 * Returns an array with all the page uids for the given root (includes the root). 
	 *
	 * @param int $root The page id to start from.
	 * @param array $tree The recursive tree to work on.
	 * @param array $result The optional array to store the pids in the recursion.
	 * @return array The array with the pids.
	 */
	protected function _getPageTreeUids($root = 0, array &$tree = null, array &$result = array()) {
		$identifier = 'tx_contentstage_ContentRepository_getPageTreeUids_' . $this->tag . '_' . $root;
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
		
		if (isset($tree['_children'])) {
			foreach ($tree['_children'] as &$child) {
				$this->_getPageTreeUids(intval($child['uid']), $child, $result);
			}
		}
		
		if (TX_CONTENTSTAGE_USECACHE) {
			$this->cache->set($identifier, $result, array(), TX_CONTENTSTAGE_CACHETIME);
		}
		return $result;
	}
	
	/**
	 * Returns the rootline to a given page.
	 *
	 * @param int $page The page uid to get the rootline for.
	 * @return array The rootline, where the first item is the page and the last item is the root. Null if page not found or broken rootline!
	 */
	public function getRootline($page) {
		$index = &$this->getFullPageTree();
		$rootline = array();
		$page = intval($page);
		
		while (isset($index[$page]) && $page > 0) {
			$local = $index[$page];
			unset($local['_children']);
			$rootline[$page] = $local;
			$page = intval($local['pid']);
		}
		
		if (empty($rootline) || $page > 0) {
			// not found or broken rootline respectively
			return array();
		}
		return $rootline;
	}
	
	/**
	 * Returns the domain to a given page.
	 *
	 * @param int $page The page uid to get the domain for.
	 * @return string The domain
	 */
	public function getDomain($page) {
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
		
		return count($rows) > 0 ? $rows[0]['domainName'] : null;
	}
	
	/**
	 * Returns the list of tables from the current db.
	 *
	 * @return array An array of tableName => SHOW TABLE STATUS row pairs.
	 */
	public function getTables() {
		return $this->db->admin_get_tables();
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
				return null;
			}
			
			$whereParts = array('pid IN (' . implode(',', $pids) . ')');
		}
		if (!empty($where)) {
			$whereParts[] = '(' . $where . ')';
		}
		
		$resource = $this->db->exec_SELECTquery($fields, $table, implode(' AND ', $whereParts), $groupBy, $orderBy, $limit);
		
		// this slows the process down imensely!
		//$query = $this->db->SELECTquery($fields, $table, implode(' AND ', $whereParts), $groupBy, $orderBy, $limit);
		//$this->log->log($query, Tx_CabagExtbase_Utility_Logging::INFORMATION);
		
		if (!$resource || $this->db->sql_error()) {
			throw new Exception($this->db->sql_error() . ' [Query: ' . $this->db->SELECTquery($fields, $table, implode(' AND ', $whereParts), $groupBy, $orderBy, $limit) . ']', self::ERROR_GET);
		}
		
		$result->setResource($resource);
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
	 * Creates a where condition with the resolved relations to find the associated sys_log entries.
	 *
	 * @return string The generated where.
	 */
	protected function _getSysLogWhere() {
		$whereParts = array();
		
		foreach ($this->resolvedRelations as $table => &$tableData) {
			if (!empty($tableData)) {
				$whereParts[] = '(sys_log.tablename = ' . $this->db->fullQuoteStr($table, 'sys_log') . ' AND sys_log.recuid IN (' . implode(',', array_keys($tableData)) . '))';
			}
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
		
		$this->db->sql_free_result($this->db->sql_query($query));
		
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
	 * @return void
	 * @throws Exception
	 */
	public function clearCache($root) {
		$pids = $this->getPageTreeUids($root);
		$domain = $this->getDomain($root);
		
		if (empty($pids) || $domain === null) {
			// do nothing
			return;
		}
		
		$hash = t3lib_div::getRandomHexString(32);
		$fields = array(
			'identifier' => $hash,
			'crdate' => time(),
			'content' => serialize(implode(',', $pids)),
			'lifetime' => 10
		);
		
		$this->db->exec_INSERTquery(self::CACHE_TABLE, $fields);
		
		$url = 'http://' . $domain . '/index.php?eID=tx_contentstage&hash=' . $hash;
		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE); 
		$response = curl_exec($ch);
		
		if ($response === false) {
        	throw new Exception('Could not reach ' . $url, 1356616554);
		}
		if (($error = curl_error($ch)) !== '') {
        	throw new Exception($error . ' [' . $url . ']', 1356616553);
		}
		$headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $result = substr($response, $headerSize);
        $data = json_decode($result);
        
        if (empty($data['success'])) {
        	$errors = array_map(function ($value) { return $value['message']; }, $data['errors']);
        	throw new Exception('[' . $url . ']' . implode(PHP_EOL, $errors), 1356616552);
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
	 * @param boolean $noResult Ignore the result. Default false.
	 * @return array The assoc return array.
	 */
	public function _sql($query, $noResult = false) {
		$resource = $this->db->sql_query($query);
		
		if ($noResult) {
			$this->db->sql_free_result($resource);
		}
		
		$result = array();
		while (($row = $this->db->sql_fetch_assoc($resource)) !== false) {
			$result[] = $row;
		}
		$this->db->sql_free_result($resource);
		return $result;
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
}
?>