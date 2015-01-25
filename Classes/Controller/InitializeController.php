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
class Tx_Contentstage_Controller_InitializeController extends Tx_Contentstage_Controller_BaseController {
	/**
	 * @var array The db info to write to.
	 */
	protected $toInfo;
	
	/**
	 * @var Tx_Contentstage_Domain_Repository_ContentRepository The repository to write to.
	 */
	protected $toRepository;
	
	/**
	 * @var array The type to write to.
	 */
	protected $toType;
	
	/**
	 * @var array The db info to read from.
	 */
	protected $fromInfo;
	
	/**
	 * @var Tx_Contentstage_Domain_Repository_ContentRepository The repository to read from.
	 */
	protected $fromRepository;
	
	/**
	 * @var array The type to read from.
	 */
	protected $fromType;

	/**
	 * action initalize
	 *
	 * @return void
	 */
	public function showAction() {
		
	}

	/**
	 * action doInitialize
	 *
	 * @param string $direction Either 'down' which overwrites the local installation or 'up' which overwrites the remote installation!
	 * @return void
	 */
	public function doInitializeAction($direction = 'down') {
		$this->initializeDirection($direction);
		
		$step = $this->extensionConfiguration['autoIncrementStep'];
		$threshold = $this->extensionConfiguration['autoIncrementThreshold'];
		
		try {
			$tables = $this->filterTables(array_keys($this->toRepository->getTables()), $this->ignoreSnapshotTables);
			$backupInfo = $this->snapshotRepository->create(
				$tables,
				$this->toInfo,
				$this->toType
			);
			
			$this->log->log($this->translate('info.init.backup', array($backupInfo['file'])), Tx_CabagExtbase_Utility_Logging::OK);
			
			// do not log the active users out
			$tables = $this->filterTables(array_keys($this->fromRepository->getTables()), $this->ignoreSnapshotTables);
			$snapshotInfo = $this->snapshotRepository->create(
				array_keys($tables),
				$this->fromInfo,
				$this->fromType
			);
			
			$this->log->log($this->translate('info.init.dumped', array($snapshotInfo['file'])), Tx_CabagExtbase_Utility_Logging::OK);
			
			$revertInfo = $this->snapshotRepository->revert(
				$snapshotInfo['file'],
				$this->toInfo
			);
			
			$this->log->log($this->translate('info.init.reverted', array($revertInfo['file'])), Tx_CabagExtbase_Utility_Logging::OK);
			
			$raiseInfo = $this->snapshotRepository->raiseAutoIncrement($this->toRepository, $step, $threshold);
			
			$this->log->log($this->translate('info.init.raised', array($raiseInfo['newAutoIncrement'])), Tx_CabagExtbase_Utility_Logging::OK);
		} catch (Exception $e) {
			$this->log->log($this->translate('error.' . $e->getCode(), array($e->getMessage())), Tx_CabagExtbase_Utility_Logging::ERROR);
		}
		
		$this->log->write();
		$this->redirect('show');
	}

	/**
	 * Initialize the directional fields.
	 *
	 * @param string $direction Either 'down' which overwrites the local installation or 'up' which overwrites the remote installation!
	 */
	protected function initializeDirection($direction = 'down') {
		if ($direction === 'up') {
			$this->toInfo = $this->extensionConfiguration['remote.']['db.'];
			$this->toRepository = $this->remoteRepository;
			$this->toType = Tx_Contentstage_Domain_Repository_ContentRepository::TYPE_REMOTE;
			$this->fromInfo = $this->getLocalDbInfo();
			$this->fromRepository = $this->localRepository;
			$this->fromType = Tx_Contentstage_Domain_Repository_ContentRepository::TYPE_LOCAL;
		} else {
			$this->toInfo = $this->getLocalDbInfo();
			$this->toRepository = $this->localRepository;
			$this->toType = Tx_Contentstage_Domain_Repository_ContentRepository::TYPE_LOCAL;
			$this->fromInfo = $this->extensionConfiguration['remote.']['db.'];
			$this->fromRepository = $this->remoteRepository;
			$this->fromType = Tx_Contentstage_Domain_Repository_ContentRepository::TYPE_REMOTE;
		}
	}
}
?>