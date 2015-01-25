<?php

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2014 Lavinia Negru <ln@cabag.ch>, cab services ag
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
class Tx_Contentstage_Controller_FileController extends Tx_Contentstage_Controller_BaseController {
	/**
	 * The File utility object.
	 *
	 * @var Tx_Contentstage_Utility_File The File utility object.
	 */
	protected $fileUtility = null;
	
	/**
	 * Injects the File utility object.
	 *
	 * @param Tx_Contentstage_Utility_File $fileUtility The File utility object.
	 */
	public function injectFile(Tx_Contentstage_Utility_File $fileUtility = null) {
		$this->fileUtility = $fileUtility;
	}
	
	/**
	 * action compare
	 *
	 * @return void
	 */
	public function compareAction() {
		if ($this->page <= 0) {
			//$this->log->log($this->translate('info.compare.noId'), Tx_CabagExtbase_Utility_Logging::INFORMATION);
			//$this->log->write();
			return $this->translate('info.compare.noId');
		}
		
		$this->doComparison();
		
		$this->view->assign('localDomain', $this->localRepository->getDomain(0));
		$this->view->assign('remoteDomain', $this->remoteRepository->getDomain(0));
		//$this->view->assign('localRootline', $this->localRepository->getRootline($this->page, true));
		//$this->view->assign('remoteRootline', $this->remoteRepository->getRootline($this->page, true));
		
		
		$this->view->assign('depth', $this->localRepository->getDepth());
		
		//$this->view->assign('pageTree', $this->localRepository->reducePageTree($this->pageTree[$this->page], $this->localRepository->getDepth()));
		$this->view->assign('fileTree', '');
		
		$this->view->assign('pidIndex', $this->pidIndex);
		
		$this->view->assign('tca', $this->tca->getProcessedTca());
		
		$fileMessages = $this->localRepository->getFileMessages();
		//$this->addFilesToSecureFiles($fileMessages);
		$this->view->assign('fileMessages', $fileMessages);
		
		if ($this->review !== null && $this->review->getUid() > 0 && $this->review->getLevels() === $this->localRepository->getDepth()) {
			$changed = $this->review->calculateState(null, false, $this->diff->getMaximumSourceTstamp());
			if ($changed) {
				//$this->sendReviewMailAndLog('changed', $this->review);
				//$this->log->log($this->translate('info.review.deprecated'), Tx_CabagExtbase_Utility_Logging::WARNING, $this->diff->getMaximumSourceTstamp());
			}
		}
		
		//$this->assignReviews();
		//$this->assignReviewers();
		//$this->assignDepth();
		$this->view->assign('currentPage', $this->page);
		$this->view->assign('isPushable', $this->isPushable());
		
		//$this->log->log($this->translate('info.compare.startFluid'), Tx_CabagExtbase_Utility_Logging::INFORMATION, $this->pageTree[$this->page]);
		
		$content = $this->view->render();
		
		//$this->log->log($this->translate('info.compare.endFluid'), Tx_CabagExtbase_Utility_Logging::INFORMATION, $this->pageTree[$this->page]);
		
		//$this->log->write();
		
		return $content;
	}
	
	/**
	 * Returns the page TypoScript configuration.
	 *
	 * @return array The pageTS
	 */
	protected function getPageTS() {
		if ($this->pageTS === null) {
			
			$pageTS = t3lib_befunc::getPagesTSconfig(0, array());
			$this->pageTS = $this->convertTypoScriptArrayToPlainArray($pageTS['tx_contentstage.']);
		}
		debug($this->pageTS,'$this->pageTS');
		return $this->pageTS;
	}
	
	/**
	 * Do the comparison.
	 *
	 * @return void
	 */
	protected function doComparison() {
		try {
			/*$localTree = $this->localRepository->getFullPageTree();
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
			
			$this->log->log($this->translate('info.compare.done'), Tx_CabagExtbase_Utility_Logging::INFORMATION, $localTree[$this->page]);*/
		} catch (Exception $e) {
			$this->log->log($this->translate('error.' . $e->getCode(), array($e->getMessage())) ?: $e->getMessage(), Tx_CabagExtbase_Utility_Logging::ERROR, $e->getTraceAsString());
		}
	}
	
	/**
	 * Returns whether or not the current page may be pushed.
	 *
	 * @return boolean Whether or not the current page may be pushed.
	 */
	protected function isPushable() {
		return $this->reviewConfiguration === null || empty($this->reviewConfiguration['_typoScriptNodeValue']) || !empty($this->reviewConfiguration['mayPush']) || ($this->review !== null && $this->review->isPushable());
	}

}