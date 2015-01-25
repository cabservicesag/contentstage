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
class Tx_Contentstage_Controller_ContentController extends Tx_Contentstage_Controller_BaseController {
	
	/**
	 * @var array The differences by table and uid.
	 */
	protected $differences = array();
	
	/**
	 * @var array The differences by pid => table => uid => field.
	 */
	protected $pidIndex = array();
	
	/**
	 * @var array The page tree.
	 */
	protected $pageTree = array();
	
	/**
	 * action compare
	 *
	 * @return void
	 */
	public function compareAction() {
		if ($this->page <= 0) {
			$this->log->log($this->translate('info.compare.noId'), Tx_CabagExtbase_Utility_Logging::INFORMATION);
			$this->log->write();
			return $this->translate('info.compare.noId');
		}
		
		$this->doComparison();

		$this->view->assign('localRootline', $this->localRepository->getRootline($this->page));
		$this->view->assign('remoteRootline', $this->remoteRepository->getRootline($this->page));
		$this->view->assign('depth', $this->localRepository->getDepth());
		
		$this->view->assign('pageTree', $this->localRepository->reducePageTree($this->pageTree[$this->page], $this->localRepository->getDepth()));
		
		$this->view->assign('pidIndex', $this->pidIndex);
		
		$this->view->assign('tca', $this->tca->getProcessedTca());
		
		$fileMessages = $this->localRepository->getFileMessages();
		$this->addFilesToSecureFiles($fileMessages);
		$this->view->assign('fileMessages', $fileMessages);
		
		if ($this->review !== null && $this->review->getUid() > 0 && $this->review->getLevels() === $this->localRepository->getDepth()) {
			$changed = $this->review->calculateState(null, false, $this->diff->getMaximumSourceTstamp());
			if ($changed) {
				$this->log->log($this->translate('info.review.deprecated'), Tx_CabagExtbase_Utility_Logging::WARNING, $this->diff->getMaximumSourceTstamp());
			}
		}
		
		$this->assignReviews();
		$this->assignReviewers();
		$this->assignDepth();
		$this->view->assign('currentPage', $this->page);
		$this->view->assign('isPushable', $this->isPushable());
		
		$this->log->log($this->translate('info.compare.startFluid'), Tx_CabagExtbase_Utility_Logging::INFORMATION, $this->pageTree[$this->page]);
		
		$content = $this->view->render();
		
		$this->log->log($this->translate('info.compare.endFluid'), Tx_CabagExtbase_Utility_Logging::INFORMATION, $this->pageTree[$this->page]);
		
		$this->log->write();
		
		return $content;
	}
	
	/**
	 * action view
	 *
	 * @return void
	 */
	public function viewAction() {
		if ($this->page <= 0) {
			$this->log->log($this->translate('info.compare.noId'), Tx_CabagExtbase_Utility_Logging::INFORMATION);
			$this->log->write();
			return $this->translate('info.compare.noId');
		}
		
		$pageTS = $this->getPageTS();
		
		$localDomain = $this->localRepository->getDomain($this->page);
		if ($localDomain === null) {
			$this->log->log($this->translate('warning.view.noDomain', array('local', $this->page)), Tx_CabagExtbase_Utility_Logging::WARNING);
		}
		
		$remoteDomain = $this->remoteRepository->getDomain($this->page);
		
		if ($remoteDomain === null) {
			$this->log->log($this->translate('warning.view.noDomain', array('remote', $this->page)), Tx_CabagExtbase_Utility_Logging::WARNING);
		}
		
		$this->view->assign('localUrl', $localDomain . 'index.php?id=' . $this->page);
		$this->view->assign('remoteUrl', $remoteDomain . 'index.php?id=' . $this->page);
		
		$this->log->write();
	}

	/**
	 * action push
	 *
	 * @return void
	 */
	public function pushAction() {
		if ($this->review !== null && $this->review->getUid() > 0) {
			$this->localRepository->setDepth($this->review->getLevels());
			$this->remoteRepository->setDepth($this->review->getLevels());
			
			$this->doComparison();
		
			$changed = $this->review->calculateState(null, false, $this->diff->getMaximumSourceTstamp());
			if ($changed) {
				$this->log->log($this->translate('info.review.deprecated'), Tx_CabagExtbase_Utility_Logging::WARNING, $this->diff->getMaximumSourceTstamp());
			}
		}
		if (!$this->isPushable()) {
			$this->log->log($this->translate('info.review.noPermission'), Tx_CabagExtbase_Utility_Logging::WARNING);
			$this->redirect('compare');
		}
		try {
			$tables = $this->filterTables(array_keys($this->remoteRepository->getTables()), $this->ignoreSnapshotTables);
			$info = $this->snapshotRepository->create(
				$tables,
				$this->extensionConfiguration['remote.']['db.'],
				Tx_Contentstage_Domain_Repository_ContentRepository::TYPE_REMOTE
			);
			$this->log->log($this->translate('info.push.snapshot', array($info['file'])), Tx_CabagExtbase_Utility_Logging::OK);
			
			$this->pushTables($this->page);
			$this->log->log($this->translate('info.push.done'), Tx_CabagExtbase_Utility_Logging::OK);
			
			$this->remoteRepository->clearCache($this->page, !!$this->extensionConfiguration['clearAllCaches']);
			$this->log->log($this->translate('info.push.clearCache'), Tx_CabagExtbase_Utility_Logging::OK);
			
			if ($this->review !== null && $this->review->getUid() > 0 && $this->review->getLevels() <= $this->localRepository->getDepth()) {
				$this->review->addChangeString($this->activeBackendUser, Tx_Contentstage_Domain_Model_State::PUSHED);
			}
		} catch (Exception $e) {
			$this->log->log($this->translate('error.' . $e->getCode(), array($e->getMessage())) ?: $e->getMessage(), Tx_CabagExtbase_Utility_Logging::ERROR);
		}
		
		$this->log->write();
		$this->redirect('compare');
	}
	
	/**
	 * Returns whether or not the current page may be pushed.
	 *
	 * @return boolean Whether or not the current page may be pushed.
	 */
	protected function isPushable() {
		return $this->reviewConfiguration === null || empty($this->reviewConfiguration['_typoScriptNodeValue']) || !empty($this->reviewConfiguration['mayPush']) || ($this->review !== null && $this->review->isPushable());
	}
	
	/**
	 * Do the comparison.
	 *
	 * @return void
	 */
	protected function doComparison() {
		try {
			$localTree = $this->localRepository->getFullPageTree();
			$remoteTree = $this->remoteRepository->getFullPageTree();
			if (!is_array($remoteTree[$this->page])) {
				$remoteTree[$this->page] = array();
			}
			if (!is_array($localTree[$this->page])) {
				$localTree[$this->page] = array();
			}
			$this->pageTree = &$localTree;
			$this->diff->rows($remoteTree[$this->page], $localTree[$this->page]);
			$this->compareTables($this->page);
			$this->generatePidIndex();
			
			$this->log->log($this->translate('info.compare.done'), Tx_CabagExtbase_Utility_Logging::INFORMATION, $localTree[$this->page]);
		} catch (Exception $e) {
			$this->log->log($this->translate('error.' . $e->getCode(), array($e->getMessage())) ?: $e->getMessage(), Tx_CabagExtbase_Utility_Logging::ERROR, $e->getTraceAsString());
		}
	}

	/**
	 * Compares all the tables.
	 *
	 * @param int $root The root id.
	 * @return void
	 */
	protected function compareTables($root = 0) {
		$this->_compareTables($this->localRepository, $this->remoteRepository, $root);
	}

	/**
	 * Compares all the tables.
	 *
	 * @param Tx_Contentstage_Domain_Repository_ContentRepository $fromRepository Repository to compare from.
	 * @param Tx_Contentstage_Domain_Repository_ContentRepository $toRepository Repository to compare to.
	 * @param int $root The root id.
	 * @return void
	 */
	protected function _compareTables(Tx_Contentstage_Domain_Repository_ContentRepository $fromRepository, Tx_Contentstage_Domain_Repository_ContentRepository $toRepository, $root = 0) {
		$tables = $fromRepository->getTables();
		
		foreach ($tables as $table => &$data) {
			if (substr($table, -3) === '_mm' || $this->ignoreSyncTables[$table]) {
				continue;
			}
			$this->differences[$table] = array();
			$resource1 = $fromRepository->findInPageTree($root, $table);
			$resource2 = $toRepository->findInPageTree($root, $table);
			
			$this->diff->resources($resource2, $resource1, $this->differences[$table], 'uid', $table === 'pages' ? 'uid' : 'pid');
			
			$resource1->free();
			$resource2->free();
		}
	}
	
	/**
	 * Push all tables in the given page tree.
	 *
	 * @param int $root The root id of the page tree.
	 * @return void
	 */
	protected function pushTables($root = 0) {
		$this->_pushTables($this->localRepository, $this->remoteRepository, $root);
	}
	
	/**
	 * Push all tables in the given page tree.
	 *
	 * @param Tx_Contentstage_Domain_Repository_ContentRepository $fromRepository Repository to push from.
	 * @param Tx_Contentstage_Domain_Repository_ContentRepository $toRepository Repository to push to.
	 * @param int $root The root id of the page tree.
	 * @return void
	 */
	protected function _pushTables(Tx_Contentstage_Domain_Repository_ContentRepository $fromRepository, Tx_Contentstage_Domain_Repository_ContentRepository $toRepository, $root = 0) {
		$tables = $fromRepository->getTables();
		foreach ($tables as $table => &$data) {
			if (substr($table, -3) === '_mm' || $this->ignoreSyncTables[$table]) {
				$this->log->log('ignored: ' . $table, Tx_CabagExtbase_Utility_Logging::INFORMATION);
				continue;
			}
			
			$resource = $fromRepository->findInPageTree($root, $table);
			$this->pushTable($resource, $toRepository);
		}
		
		$this->pushDependencies($fromRepository, $toRepository);
	}
	
	/**
	 * Resolves and pushes dependencies.
	 *
	 * @param Tx_Contentstage_Domain_Repository_ContentRepository $fromRepository Repository to push from.
	 * @param Tx_Contentstage_Domain_Repository_ContentRepository $toRepository Repository to push to.
	 * @return void
	 */
	protected function pushDependencies(Tx_Contentstage_Domain_Repository_ContentRepository $fromRepository, Tx_Contentstage_Domain_Repository_ContentRepository $toRepository) {
		$this->log->log($this->translate('info.push.dependencies'), Tx_CabagExtbase_Utility_Logging::OK);
		
		do {
			$relations = $fromRepository->getUnresolvedRelations();
			$unSynced = $fromRepository->setRelationsSynced();
			
			$this->pushFiles($fromRepository, $toRepository, $relations['__FILE'] ?: array());
			$this->pushFiles($fromRepository, $toRepository, $relations['__FOLDER'] ?: array());
			
			foreach ($relations as $table => &$data) {
				if (is_array($data) && isset($data[0])) {
					unset($data[0]);
				}
				if (substr($table, 0, 2) === '__' || empty($data)) {
					continue;
				}
				
				if (substr($table, -3) === '_mm') {
					$localUids = implode(',', array_keys($data));
					$toRepository->_sql('DELETE FROM ' . $table . ' WHERE uid_local IN (' . $localUids . ')', false);
					$where = 'uid_local IN (' . $localUids . ')';
				} else {
					$where = 'uid IN (' . implode(',', array_keys($data)) . ')';
				}
				
				$resource = $fromRepository->findInPageTree(0, $table, '*', $where, '', '');
				$this->pushTable($resource, $toRepository);
			}
		} while ($unSynced > 0);
		
		$sysLogResource = $fromRepository->findResolvedSysLog();
		$this->pushTable($sysLogResource, $toRepository);
		
		$sysHistoryResource = $fromRepository->findResolvedSysHistory();
		$sysHistoryResource->setTable('sys_history');
		$this->pushTable($sysHistoryResource, $toRepository);
	}
	
	/**
	 * Pushes a single table.
	 *
	 * @param Tx_Contentstage_Domain_Repository_Result $resource The db resource to get the data from.
	 * @param Tx_Contentstage_Domain_Repository_ContentRepository $toRepository Repository to push to.
	 */
	protected function pushTable(Tx_Contentstage_Domain_Repository_Result $resource, Tx_Contentstage_Domain_Repository_ContentRepository $toRepository) {
		$toRepository->insert($resource);
		
		$table = $resource->getTable();
		$tcaName = $this->tca->getTableName($table);
		
		$this->log->log(
			$this->translate(
				'info.push.table',
				array(
					(empty($tcaName) || $table === $tcaName ? $table : '"' . $tcaName . '" (' . $table . ')'),
					$resource->count()
				)
			),
			$resource->count() > 0 ? Tx_CabagExtbase_Utility_Logging::OK : Tx_CabagExtbase_Utility_Logging::INFORMATION
		);
		
		$resource->free();
	}
	
	/**
	 * Pushes files.
	 *
	 * @param Tx_Contentstage_Domain_Repository_ContentRepository $fromRepository Repository to push from.
	 * @param Tx_Contentstage_Domain_Repository_ContentRepository $toRepository Repository to push to.
	 * @param array $files The files to push.
	 * @return void
	 */
	protected function pushFiles(Tx_Contentstage_Domain_Repository_ContentRepository $fromRepository, Tx_Contentstage_Domain_Repository_ContentRepository $toRepository, array $files) {
		$filesOK = 0;
		$errors = 0;
		foreach ($files as $file => &$ignored) {
			$this->log->log(
				$this->translate(
					'info.push.file',
					array($fromHandle, $toHandle)
				),
				Tx_CabagExtbase_Utility_Logging::INFORMATION
			);
			
			$fromHandle = $fromRepository->getFileHandle($file);
			$toHandle = $toRepository->getFileHandle($file);
			if ($toRepository->copy($fromHandle, $toHandle)) {
				$filesOK++;
			} else {
				$errors++;
			}
		}
		
		$this->log->log(
			$this->translate('info.push.files', array($filesOK + $errors, $filesOK, $errors)),
			$errors > 0 ? Tx_CabagExtbase_Utility_Logging::WARNING : ($filesOK > 0 ? Tx_CabagExtbase_Utility_Logging::OK : Tx_CabagExtbase_Utility_Logging::INFORMATION),
			$files
		);
	}
	
	/**
	 * Generates the pid index from the local differences.
	 */
	protected function generatePidIndex() {
		foreach ($this->differences as $table => &$data) {
			if (isset($data['byPid'])) {
				foreach ($data['byPid'] as $pid => &$changes) {
					$this->pidIndex[$pid][$table] = &$changes;
					$this->pageTree[$pid]['_differences'][$table] = &$changes;
				}
			}
		}
	}
	
	/**
	 * Checks if secure_files is installed and adds (local) files to secure_files.
	 *
	 * @param array $messages Array of path => array(handle, messages) tuples.
	 * @return void
	 */
	protected function addFilesToSecureFiles($messages) {
		if (t3lib_extMgm::isLoaded('secure_files')) {
			$parser = $this->objectManager->get('tx_SecureFiles_Utility_Parser');
			foreach ($messages as $message) {
				if ($message['handle']['root'] === PATH_site) {
					$parser->allowDownload($message['handle']['rel']);
				}
			}
		}
	}
}
?>